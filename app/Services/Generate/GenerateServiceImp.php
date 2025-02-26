<?php

namespace App\Services\Generate;

use App\Exceptions\DiscountException;
use App\Exceptions\GenerateException;
use App\Models\Coupon;
use App\Repositories\Coupon\CouponRepository;
use App\Repositories\Discount\DiscountRepository;
use App\Repositories\Generate\GenerateRepository;
use Carbon\Carbon;
use Customerio\Client;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class GenerateServiceImp implements GenerateService
{
    public function __construct(protected GenerateRepository $generateRepository, protected DiscountRepository $discountRepository, protected CouponRepository $couponRepository) {}

    public function index(array $filters)
    {
        $count_all = $this->generateRepository->countGenerate();
        $perPage = Arr::get($filters, 'per_page', 5);
        $status = Arr::get($filters, 'status');
        $perPage = $perPage == -1 ? $count_all : $perPage;
        $status = $status !== null ? (int) $status : null;
        Arr::set($filters, 'per_page', $perPage);
        Arr::set($filters, 'status', $status);
        $generateData = $this->generateRepository->getAllGenerates($filters);
        $total_items = $generateData->total();
        $total_pages = $generateData->lastPage();
        $current_pages = $generateData->currentPage();
        $groupedGenerates = $generateData->groupBy('app_name');
        $discountMap = [];
        foreach ($groupedGenerates as $appName => $group) {
            $discountIds = $group->pluck('discount_id')->unique();
            $discounts = $this->discountRepository->findDiscountsByIdsAndApp($discountIds, $appName);
            $discountMap[$appName] = $discounts->keyBy('id');
        }

        $generateDatas = [];
        foreach ($generateData as $gen) {
            $discount = $discountMap[$gen['app_name']][$gen['discount_id']] ?? null;
            if (! $discount) {
                throw DiscountException::notFound();
            }
            $gen['db_name'] = $gen['app_name'];
            $gen['app_name'] = config('database.connections.'.$gen['app_name'].'.app_name');
            $gen['expired'] = $discount->expired_at && now()->timestamp > Carbon::parse($discount->expired_at)->timestamp;
            $gen['discount_name'] = $discount->name;
            $gen['discount_id'] = $discount->id;
            $generateDatas[] = $gen;
        }

        return [
            'generateData' => $generateDatas,
            'total_pages' => $total_pages,
            'total_items' => $total_items,
            'current_pages' => $current_pages,
        ];
    }

    public function showCreate(array $databaseName)
    {
        $discountData = [];
        foreach ($databaseName as $db) {
            $data = $this->discountRepository->getAllDiscountsNoCoupon($db);
            foreach ($data as $d) { // Lặp qua phần data thực sự
                $d['databaseName'] = $db;
                $d['appName'] = config('database.connections.'.$db.'.app_name');
                $discountData[] = $d;
            }

        }

        return $discountData;
    }

    public function create(array $data)
    {
        [$discount_id, $app_name] = explode('&', $data['discount_app']);
        $data['app_name'] = $app_name;
        $data['discount_id'] = $discount_id;
        $condition = Arr::get($data, 'condition_object');

        $discount = $this->discountRepository->findDiscountByIdNoCoupon($discount_id, $app_name);
        if (! $discount) {
            throw DiscountException::notFound(['error'=>['Discount not found']]);
        }

        if ($this->generateRepository->getGenerateByDiscountIdAndAppName($discount_id, $app_name)->count() > 0) {
            throw DiscountException::generateExist(['message'=>['Generate existed discount_id']]);
        }

        if (! empty($discount->expired_at) && now()->timestamp > Carbon::parse($discount->expired_at)->timestamp) {
            throw DiscountException::discountExpired(['error'=>['Discount expired']]);
        }

        if ($condition) {
            $decode_conditions = json_decode($condition, true); // Giải mã JSON thành mảng
            $condition_array = [];

            foreach ($decode_conditions as $cd) {
                $text = [];

                // Kiểm tra nếu tồn tại key 'apps' và nó là một mảng
                if (isset($cd['apps']) && is_array($cd['apps'])) {
                    foreach ($cd['apps'] as $app) {
                        // Kiểm tra xem có tồn tại 'name' và 'status' không
                        if (isset($app['name']) && isset($app['status'])) {
                            $text[] = $app['name'].'&'.$app['status'];
                        }
                    }
                }

                if (count($text) > 0) {
                    $condition_array[] = implode('||', $text);
                }
            }
            $data['conditions'] = json_encode($condition_array);
        } else {
            $data['conditions'] = '';
        }
        $data['success_message'] = $this->handleMessage($data)['success_message'];
        $data['fail_message'] = $this->handleMessage($data)['fail_message'];

        return $this->generateRepository->createGenerate($data);
    }

    public function showUpdate($id)
    {
        $generate = $this->generateRepository->find($id);
        if (! $generate) {
            throw GenerateException::notFound();
        }
        $coupon = $this->couponRepository->getCouponByDiscountIdAndCode($id, $generate->app_name);
        $status_del = true;
        if (count($coupon) > 0) {
            $status_del = false;
        }
        $discount = $this->discountRepository->findDiscountByIdNoCoupon($generate->discount_id, $generate->app_name);

        return [
            'generate' => $generate,
            'discount' => $discount,
            'status_del' => $status_del,
        ];
    }

    public function update($id, array $data)
    {
        $generate = $this->generateRepository->find($id);
        if (! $generate) {
            throw GenerateException::notFound();
        }
        $discount_id_in_db = $generate->discount_id;
        $app_name_in_db = $generate->app_name;
        $coupon = $this->couponRepository->getCouponByDiscountIdAndCode($id, $generate->app_name);
        if (count($coupon) > 0) {
            $this->validateUpdate(true, $data);
        } else {
            $this->validateUpdate(false, $data);
        }
        [$discount_id, $app_name] = explode('&', $data['discount_app']);

        if ($discount_id != $discount_id_in_db || $app_name != $app_name_in_db) {
            if ($this->generateRepository->getGenerateByDiscountIdAndAppName($discount_id, $app_name)->count() > 0) {
                throw DiscountException::generateExist();
            }
        }
        $discount = $this->discountRepository->findDiscountByIdNoCoupon($discount_id, $app_name);
        if (! $discount) {
            throw DiscountException::notFound();
        }
        if (! empty($discount->expired_at) && now()->timestamp > Carbon::parse($discount->expired_at)->timestamp) {
            throw DiscountException::discountExpired();
        }

        $data['conditions'] = $this->handleCondition($data);
        $data['success_message'] = $this->handleMessage($data)['success_message'];
        $data['fail_message'] = $this->handleMessage($data)['fail_message'];

        $data['discount_id'] = $discount_id;
        $data['app_name'] = $app_name;
        return $this->generateRepository->updateGenerate($id, $data);
    }

    public function changeStatus($id)
    {
        $generate = $this->generateRepository->find($id);
        if (! $generate) {
            throw GenerateException::notFound();
        }
        $this->generateRepository->updateGenerateStatus($id, $generate->status);
    }

    public function destroy($id)
    {
        $generate = $this->generateRepository->find($id);
        if (! $generate) {
            throw GenerateException::notFound();
        }
        $this->generateRepository->destroyGenerate($id);
    }

    public function validateUpdate($status, $data)
    {
        $rules = [
            'expired_range' => 'required|integer',
            'app_url' => 'required',
        ];
        if ($status) {
            $rules['discount_app'] = 'required';
        }
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw GenerateException::validateEdit($validator->errors()->first());
        }
    }

    public function handleCondition($data)
    {
        // Kiểm tra nếu 'condition_object' có dữ liệu
        if (! empty($data['condition_object'])) {
            // Giải mã JSON thành mảng PHP
            $conditionArray = json_decode($data['condition_object'], true);

            // Nếu JSON không hợp lệ, trả về mảng rỗng
            if (! is_array($conditionArray)) {
                return [];
            }

            // Mảng chứa điều kiện đã xử lý
            $conditions = [];

            // Duyệt qua từng điều kiện trong mảng
            foreach ($conditionArray as $cd) {
                if (isset($cd['apps']) && is_array($cd['apps']) && count($cd['apps']) > 0) {
                    // Mảng lưu các điều kiện đã ghép của từng app
                    $apps = [];

                    // Lặp qua từng app và nối `name&status`
                    foreach ($cd['apps'] as $app) {
                        if (isset($app['name']) && isset($app['status'])) {
                            $apps[] = $app['name'].'&'.$app['status'];
                        }
                    }

                    // Nếu có app hợp lệ, nối các điều kiện bằng '||'
                    if (! empty($apps)) {
                        $conditions[] = implode('||', $apps);
                    }
                }
            }

            return $conditions; // Trả về mảng kết quả thay vì JSON encode
        }

        return [];
    }

    public function handleMessage($data)
    {
        $successMessage = array_filter([
            'message' => $data['success_message'] ?? null,
            'extend' => $data['extend_message'] ?? null,
        ]);
        $data['success_message'] =
        $failMessage = array_filter([
            'message' => $data['fail_message'] ?? null,
            'reason_expired' => $data['reason_expired'] ?? null,
            'reason_limit' => $data['reason_limit'] ?? null,
            'reason_condition' => $data['reason_condition'] ?? null,
        ]);

        return [
            'success_message' => ! empty($successMessage) ? json_encode($successMessage) : null,
            'fail_message' => ! empty($failMessage) ? json_encode($failMessage) : null,
        ];
    }

    public function privateGenerateCoupon($ip, $generateId, $shopName)
    {
        $ip_server = config('discount_manager.ip_server');
        $ip_server = str_replace(' ', '', trim($ip_server));
        $ip_server_array = explode(',', $ip_server);
        foreach ($ip_server_array as $ipValue) {
            if (! filter_var($ipValue, FILTER_VALIDATE_IP)) {
                unset($ip_server_array[array_search($ipValue, $ip_server_array)]);
            }
        }
        if (! in_array($ip, $ip_server_array)) {
            logger("not support ip {$ip}", compact('ip', 'ip_server'));

            return [
                'status' => false,
                'message' => 'Not support!',
            ];
        }
        $generate = $this->generateRepository->find($generateId);
        if (! $generate) {
            return [
                'status' => false,
                'message' => 'Generate not exist!',
            ];
        }
        $app = $generate->app_name;
        $app_url = $generate->app_url;
        $discount_id = $generate->discount_id;

        $discount = $this->discountRepository->findDiscountByIdNoCoupon($discount_id, $app);
        $discount_expired = $discount->expired_at ? Carbon::parse($discount->expired_at)->timestamp : null;

        $existingCoupons = $this->couponRepository->getCouponByDiscountIdAndCode($discount_id, $app);
        $number_coupon = count($existingCoupons);

        $coupon = $this->getExistingCoupon($discount_id, "{$shopName}.myshopify.com", $app);

        if ($coupon) {
            // Tồn tại Coupon
            if ($coupon->times_used > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Coupon used!',
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'message' => 'Coupon created!',
                ]);
            }
        } else {
            $current_timestamp = now()->timestamp;
            if ($discount_expired != null && $current_timestamp > $discount_expired) {
                return [
                    'status' => false,
                    'message' => 'Discount Expired!',
                ];
            } elseif ($generate->limit && $generate->limit <= $number_coupon) {
                return [
                    'status' => false,
                    'message' => 'Limit Coupon',
                ];
            } else {
                $dataCoupon = [];
                $dataCoupon['discount_id'] = $discount_id;
                $dataCoupon['shop'] = $shopName.'.myshopify.com';
                $dataCoupon['times_used'] = 0;
                $dataCoupon['status'] = 1;
                $dataCoupon['code'] = $this->generateUniqueCouponCode($discount_id, $shopName, $app);

                if ($this->isAutomaticCoupon($app)) {
                    $dataCoupon['automatic'] = true;
                }
                $this->couponRepository->createCoupon($dataCoupon, $app);

                return [
                    'status' => true,
                    'message' => 'Success generate coupon!',
                ];
            }
        }
    }

    public function generateCoupon($generate_id, $timestamp, $shop_id)
    {
        $generate = $this->generateRepository->find($generate_id);
        if (! $generate || ! $generate->status) {
            return [
                'header_message' => config('constant', 'DEFAULT_HEADER_MESSAGE'),
                'content_message' => 'WHOOPS!',
                'reasons' => ! $generate ? 'This offer does not exist!' : 'This offer was disabled',
                'app_url' => null,
                'generate_id' => null,
                'custom_fail' => null,
                'extend_message' => null,
            ];
        }
        $app = $generate->app_name;
        $app_url = $generate->app_url;
        $discount_id = $generate->discount_id;
        $conditions = json_decode($generate->conditions, true) ?? [];

        $discount = $this->discountRepository->findDiscountByIdNoCoupon($discount_id, $app);
        $discount_expired = $discount->expired_at ? Carbon::parse($discount->expired_at)->timestamp : null;

        $existingCoupons = $this->couponRepository->getCouponByDiscountIdAndCode($discount_id, $app);
        $number_coupon = count($existingCoupons);

        // 5️⃣ Lấy message từ Generate hoặc dùng DEFAULT
        $messages = $this->getMessagesFromGenerate($generate);
        $header_message = $generate->header_message ?? config('constant', 'DEFAULT_HEADER_MESSAGE');
        $used_message = $generate->used_message ?? config('constant', 'DEFAULT_USED_MESSAGE');

        // 6️⃣ Lấy thông tin Shop từ API Customer.io
        try {
            $attributes = $this->getShopAttributes($shop_id, $app_url, $generate_id);
            $shop_name = $attributes->shop_name ?? null;
        } catch (GuzzleException $e) {
            return [
                'header_message' => 'Connection Error!',
                'content_message' => 'Oops! Can not find shop name!',
                'reasons' => 'The shop may be down or experiencing issues. Please try again!',
                'app_url' => $app_url,
                'generate_id' => $generate_id,
                'custom_fail' => null,
                'extend_message' => null,
            ];
        }

        // 7️⃣ Kiểm tra nếu shop đã có Coupon
        $coupon = $this->getExistingCoupon($discount_id, $shop_name, $app);
        if ($coupon) {
            if ($coupon->times_used > 0) {
                return [
                    'header_message' => $header_message,
                    'content_message' => $messages['fail_message'],
                    'reasons' => $used_message,
                    'app_url' => $app_url,
                    'generate_id' => $generate_id,
                    'custom_fail' => null,
                    'extend_message' => null,
                ];
            }

            return [
                'header_message' => $header_message,
                'content_message' => $messages['success_message'],
                'extend_message' => $messages['extend_message'],
                'app_url' => $app_url,
                'generate_id' => $generate_id,
                'custom_fail' => null,
                'reasons' => null,
            ];
        }

        // 8️⃣ Kiểm tra thời gian hết hạn
        if ($this->isCouponExpired($discount_expired, $timestamp, $generate->expired_range)) {
            return [
                'header_message' => $header_message,
                'content_message' => $messages['fail_message'],
                'reasons' => $messages['reason_expired'],
                'app_url' => $app_url,
                'generate_id' => $generate_id,
                'custom_fail' => null,
                'extend_message' => null,
            ];
        }

        // 9️⃣ Kiểm tra giới hạn sử dụng Coupon
        if ($generate->limit && $generate->limit <= $number_coupon) {
            return [
                'header_message' => $header_message,
                'content_message' => $messages['fail_message'],
                'reasons' => $messages['reason_limit'],
                'app_url' => $app_url,
                'generate_id' => $generate_id,
                'custom_fail' => null,
                'extend_message' => null,
            ];
        }

        // 🔟 Kiểm tra điều kiện (Conditions)
        if ($conditions) {
            $conditions = json_decode($conditions);
            $prefix_app = [
                'qv' => 'Quick View',
                'fg' => 'Free gift',
                'pp' => 'Promotion Popup',
                'sl' => 'Store Locator',
                'sp' => 'Store Pickup',
                'bn' => 'Banner Slider',
                'cs' => 'Currency Switcher',
                'pl' => 'Product Label',
                'ca' => 'Customer Attribute',
                'sw' => 'Spin To Win',
                'io' => 'Smart Image Optimizer',
            ];
            foreach ($conditions as $cd) {
                $arr_or = explode('||', $cd); // Tách trên 1 hàng các điều kiện OR
                $text_or = '';
                for ($i = 0; $i < count($arr_or); $i++) {
                    $arr_con = explode('&', $arr_or[$i]); // Dạng của điều kiện name&status
                    $name_status = $arr_con[0].'_status';
                    $status = $arr_con[1];
                    // Nếu không có app_status
                    // Hoặc app_status khác
                    // thì lưu text.
                    // Nếu không thì text = ""

                    try {
                        $customer_status = $attributes->$name_status;
                    } catch (Exception $e) {
                        $customer_status = 'notinstalledyet';
                    }

                    if ($customer_status == $status) {
                        $text_or = '';
                        break;
                    } else {
                        $text_or .= '<p>';
                        if (count($arr_or) > 1) {
                            $text_or .= "<strong class='or_status'>OR</strong>";
                        }
                        if ($status == 'notinstalledyet') {
                            $text_or .= "<span class='app_status'> ".$prefix_app[$arr_con[0]]."</span> must be <span class='app_status'>Not Installed yet</span></p>";
                        } else {
                            $text_or .= "<span class='app_status'> ".$prefix_app[$arr_con[0]]."</span> must be <span class='app_status'>".$status.'</span></p>';
                        }

                    }
                }

                // Nếu có $text_or thì chứng tỏ điều kiện này ko thoả mãn. Break luôn.
                if ($text_or) {
                    $text = $text_or;
                    break;
                }

            }
            if ($text_or) {
                return [
                    'header_message' => $header_message,
                    'content_message' => config('constant', 'DEFAULT_FAIL_MESSAGE'),
                    'custom_fail' => $text_or,
                    'app_url' => $app_url,
                    'reasons' => config('constant', 'DEFAULT_CONDITION_REASON'),
                    'generate_id' => $generate_id,
                    'extend_message' => null,
                ];
            }
        }
        // 🔥 Nếu tất cả điều kiện đạt -> Tạo mới Coupon
        $dataCoupon = [];
        $dataCoupon['discount_id'] = $discount_id;
        $dataCoupon['shop'] = $shop_name.'.myshopify.com';
        $dataCoupon['times_used'] = 0;
        $dataCoupon['status'] = 1;
        $dataCoupon['code'] = $this->generateUniqueCouponCode($discount_id, $shop_name, $app);

        if ($this->isAutomaticCoupon($app)) {
            $dataCoupon['automatic'] = true;
        }
        $this->couponRepository->createCoupon($dataCoupon, $app);

        return [
            'header_message' => config('constant', 'DEFAULT_HEADER_MESSAGE'),
            'content_message' => $messages['success_message'],
            'extend_message' => $messages['extend_message'],
            'app_url' => $app_url,
            'generate_id' => $generate_id,
            'custom_fail' => null,
            'reasons' => null,
        ];
    }

    private function getMessagesFromGenerate($generate)
    {
        return [
            'success_message' => json_decode($generate->success_message, true)['message'] ?? config('constant', 'DEFAULT_SUCCESS_MESSAGE'),
            'extend_message' => json_decode($generate->success_message, true)['extend'] ?? config('constant', 'DEFAULT_EXTEND_MESSAGE'),
            'fail_message' => json_decode($generate->fail_message, true)['message'] ?? config('constant', 'DEFAULT_FAIL_MESSAGE'),
            'reason_expired' => json_decode($generate->fail_message, true)['reason_expired'] ?? config('constant', 'DEFAULT_EXPIRED_REASON'),
            'reason_limit' => json_decode($generate->fail_message, true)['reason_limit'] ?? config('constant', 'DEFAULT_LIMIT_REASON'),
            'reason_condition' => json_decode($generate->fail_message, true)['reason_condition'] ?? config('constant', 'DEFAULT_CONDITION_REASON'),
        ];
    }

    private function getShopAttributes($shop_id, $app_url, $generate_id)
    {
        $client = new Client($this->apiKey, $this->siteId);
        $endpoint = "/customers/{$shop_id}/attributes";
        $client->setAppAPIKey($this->appKey);

        try {
            $get_customerio = $client->get($endpoint);

            return $get_customerio->customer->attributes;
        } catch (GuzzleException $e) {
            return [
                'header_message' => 'Connection Error!',
                'content_message' => 'Oops! Can not connect to shop!',
                'reasons' => 'The shop may be down or experiencing issues. Please try again!',
                'app_url' => $app_url,
                'generate_id' => $generate_id,
            ];
        }
    }

    private function getExistingCoupon($discount_id, $shop_name, $app)
    {
        return $this->couponRepository->getCouponByDiscountIdandShop($discount_id, "{$shop_name}.myshopify.com", $app);
    }

    private function isCouponExpired($discount_expired, $timestamp, $expired_range)
    {
        $current_timestamp = now()->timestamp;
        $expired_timestamp = $timestamp + ($expired_range * 24 * 60 * 60);

        return ($discount_expired && $current_timestamp > $discount_expired) ||
            $current_timestamp > $expired_timestamp ||
            ($discount_expired && $timestamp > $discount_expired);
    }

    private function generateUniqueCouponCode($discount_id, $shop_name, $app)
    {
        do {
            $newCode = strtoupper('GENAUTO'.random_int(1, 1000).substr(md5("{$app}_{$discount_id}_{$shop_name}"), 2, 4));
            $exists = $this->couponRepository->getCouponByCode($newCode, $app);
        } while ($exists);

        return $newCode;
    }

    private function isAutomaticCoupon($app)
    {
        $automatic_apps = [
            'bannerslider', 'banner', 'currency_switcher', 'productlabels',
            'pl', 'customer_attribute', 'spin_to_win', 'smart_image_optimizer',
            'seo_booster', 'affiliate', 'loyalty', 'freegifts',
            'freegifts_new', 'reviews_importer',
        ];

        return in_array($app, $automatic_apps);
    }
}
