<?php

namespace App\Services\Generate;

use App\Exceptions\DiscountException;
use App\Exceptions\GenerateException;
use App\Exceptions\NotFoundException;
use App\Models\Coupon;
use App\Models\Generate;
use App\Repositories\Coupon\CouponRepository;
use App\Repositories\Discount\DiscountRepository;
use App\Repositories\Generate\GenerateRepository;
use App\Validator\GenerateUpdateValidator;
use Carbon\Carbon;
use Customerio\Client;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;

class GenerateServiceImp implements GenerateService
{
    public function __construct(protected GenerateRepository $generateRepository, protected DiscountRepository $discountRepository, protected CouponRepository $couponRepository) {}

    public function index(array $filters)
    {
        // Lấy số bản ghi tổng và cấu hình cho pagination
        $count_all = $this->generateRepository->countGenerate();
        $perPage = Arr::get($filters, 'per_page', 5);
        $perPage = ($perPage == -1) ? $count_all : $perPage;
        $status = Arr::get($filters, 'status');
        Arr::set($filters, 'per_page', $perPage);
        Arr::set($filters, 'status', $status !== null ? (int) $status : null);

        // Lấy dữ liệu generate
        $generateData = $this->generateRepository->getAllGenerates($filters);
        $groupedGenerates = $generateData->groupBy('app_name');

        // Mảng để lưu thông tin discounts theo app
        $discountMap = [];

        // Lấy discounts một lần cho từng app_name
        foreach ($groupedGenerates as $appName => $group) {
            $discountIds = $group->pluck('discount_id')->unique();
            $discounts = $this->discountRepository->findDiscountsByIdsAndApp($discountIds, $appName);
            $discountMap[$appName] = $discounts->keyBy('id');
        }

        // Xử lý dữ liệu generate
        $generateDatas = $generateData->map(function ($gen) use ($discountMap) {
            $discount = $discountMap[$gen['app_name']][$gen['discount_id']] ?? null;

            if (! $discount) {
                throw DiscountException::notFound(['error' => ['Discount not found']]);
            }

            $gen['db_name'] = $gen['app_name'];
            $gen['app_name'] = config('database.connections.' . $gen['app_name'] . '.app_name');
            $gen['expired'] = $discount->expired_at && now()->timestamp > Carbon::parse($discount->expired_at)->timestamp;
            $gen['discount_name'] = $discount->name;
            $gen['discount_id'] = $discount->id;

            return $gen;
        });

        // Trả về kết quả
        return [
            'generateData' => $generateDatas,
            'total_pages' => $generateData->lastPage(),
            'total_items' => $generateData->total(),
            'current_pages' => $generateData->currentPage(),
        ];
    }

    public function showCreate(array $databaseName)
    {
        $discountData = [];
        foreach ($databaseName as $db) {
            $data = $this->discountRepository->getAllDiscountIdAndName($db);
            foreach ($data as $d) {
                $d['databaseName'] = $db;
                $d['appName'] = config('database.connections.' . $db . '.app_name');
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
        $this->validationDiscount($discount_id,$app_name);
        $this->checkUniqueGenerate($discount_id, $app_name);
        $this->prepareData($data);
        return $this->generateRepository->createGenerate($data);
    }
    public function prepareData($data)
    {
        $data['conditions'] = $this->handleCondition(Arr::get($data, 'condition_object'));
        $this->handleMessage($data);
        return $data;
    }
    public function validationDiscount($discount_id,$app_name)
    {
        $discount = $this->discountRepository->findDiscountByIdNoCoupon($discount_id, $app_name);
        if (! $discount) {
            throw NotFoundException::Notfound('Discount not found');
        }
        if ($discount->expired_at && now()->greaterThan(Carbon::parse($discount->expired_at))) {
            throw DiscountException::discountExpired(['error' => ['Discount expired']]);
        }
    }
    public function checkUniqueGenerate($discount_id, $app_name)
    {
        $generate = $this->generateRepository->getGenerateByDiscountIdAndAppName($discount_id, $app_name);
        if ($generate) {
            throw GenerateException::generateExist(['error' => ['Generate existed discount_id']]);
        }
    }

    public function showUpdate($id)
    {
        $generate = $this->getGenerateById($id);
        $coupon = $this->couponRepository->getCouponByDiscountIdAndCode($generate->discount_id, $generate->app_name);
        $status_del = true;
        if ($coupon) {
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
        $generate = $this->getGenerateById($id);
        $currentDiscountId = $generate->discount_id;
        $currentAppName = $generate->app_name;

        $existingCoupon = $this->couponRepository->getCouponByDiscountIdAndCode($currentDiscountId, $currentAppName);
        GenerateUpdateValidator::validateUpdate(!$existingCoupon, $data);

        if (!$existingCoupon) {
            [$newDiscountId, $newAppName] = explode('&', $data['discount_app']);
            if ($currentDiscountId != $newDiscountId || $currentAppName != $newAppName) {
                $this->validationDiscount($newDiscountId, $newAppName);
                $this->checkUniqueGenerate($newDiscountId, $newAppName);
                $data['discount_id'] = $newDiscountId;
                $data['app_name'] = $newAppName;
            }
        }
        $this->prepareData($data);
        $data = array_intersect_key($data, array_flip((new Generate())->getFillable()));
        return $this->generateRepository->updateGenerate($id, $data);
    }

    public function destroy($id)
    {
        $generate = $this->getGenerateById($id);
        $this->generateRepository->destroyGenerate($id);
    }

    public function changeStatus($id)
    {
        $generate = $this->getGenerateById($id);
        $this->generateRepository->updateGenerateStatus($id, $generate->status);
    }

    public function getGenerateById($id)
    {
        $generate = $this->generateRepository->find($id);
        if (! $generate) {
            throw GenerateException::notFound(['error' => ['Generate not found']]);
        }
        return $generate;
    }

    public function handleCondition($data)
    {
        // Kiểm tra nếu 'condition_object' có dữ liệu
        if (! empty($data)) {
            // Giải mã JSON thành mảng PHP
            $conditionArray = json_decode($data, true);

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
                            $apps[] = $app['name'] . '&' . $app['status'];
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

    public function handleMessage(&$data)
    {
        $successMessage = array_filter([
            'message' => $data['success_message'] ?? null,
            'extend' => $data['extend_message'] ?? null,
        ]);
        $failMessage = array_filter([
            'message' => $data['fail_message'] ?? null,
            'reason_expired' => $data['reason_expired'] ?? null,
            'reason_limit' => $data['reason_limit'] ?? null,
            'reason_condition' => $data['reason_condition'] ?? null,
        ]);
        $data['success_message'] = ! empty($successMessage) ? $successMessage : null;
        $data['fail_message'] = ! empty($failMessage) ? $failMessage : null;
    }

    //private generate coupon

    public function privateGenerateCoupon($ip, $generateId, $shopName)
    {
        if (! $this->validateIp($ip)) {
            return $this->response(false,'Ip not valid!');
        }

        $generate = $this->generateRepository->getGenerateById($generateId);
        if (!$generate) {
            return $this->response(false,'Generate not exist!');
        }
        if (!$generate->status) {
            return $this->response(false,'Generate not active!');
        }

        $app = $generate->app_name;
        $discount_id = $generate->discount_id;

        $coupon = $this->couponRepository->getCouponByDiscountIdandShop($discount_id, "{$shopName}.myshopify.com", $app);
        if ($coupon) {
            if ($coupon->times_used > 0) {
                return $this->response(false,'Coupon used!');
            }
            return $this->response(true,'Coupon created!');
        }

        $discount = $this->discountRepository->findDiscountByIdNoCoupon($discount_id, $app);
        if ($discount === null) {
            return $this->response(false,'Discount not found!');
        }
        $discount_expired = $discount->expired_at ? Carbon::parse($discount->expired_at)->timestamp : null;
        if ($discount_expired != null && now()->timestamp > $discount_expired) {
            return $this->response(false,'Discount expired!');
        }

        $number_coupon = $this->couponRepository->countCouponByDiscountIdAndCode($discount_id, $app);
        if ($generate->limit && $generate->limit <= $number_coupon) {
            return $this->response(false,'Limit Coupon!');
        }

        $this->createCouponForShop($discount_id,$shopName,$app);

        return $this->response(true,'Success generate coupon!');
    }
    public function validateIp($ip){
        $ip_server = config('Discount_manager.ip_server');
        $ip_server = str_replace(' ', '', trim($ip_server));
        $ip_server_array = explode(',', $ip_server);
        foreach ($ip_server_array as $ipValue) {
            if (! filter_var($ipValue, FILTER_VALIDATE_IP)) {
                unset($ip_server_array[array_search($ipValue, $ip_server_array)]);
            }
        }
        if (! in_array($ip, $ip_server_array)) {
            return false;
        }
        return true;
    }
    public function createCouponForShop($discount_id,$shopName,$app){
        $dataCoupon = [
            'discount_id' => $discount_id,
            'shop' => $shopName . '.myshopify.com',
            'times_used' => 0,
            'status' => 1,
            'code' => $this->generateUniqueCouponCode($discount_id, $shopName, $app)
        ];

        if ($this->isAutomaticCoupon($app)) {
            $dataCoupon['automatic'] = true;
        }
        $this->couponRepository->createCoupon($dataCoupon, $app);
    }
    public function response($status,$message){
        return [
            'status' => $status,
            'message' => $message,
        ];
    }

    //end private generate coupon


    public function generateCouponResponse($header_message,$content_message,$reasons,$app_url,$generate_id,$custom_fail,$extend_message,$coupon_code){
        $data= [
            'header_message' => $header_message,
            'content_message' => $content_message,
        ];
        if ($reasons !== null) $data['reasons'] = $reasons;
        if ($app_url !== null) $data['app_url'] = $app_url;
        if ($generate_id !== null) $data['generate_id'] = $generate_id;
        if ($custom_fail !== null) $data['custom_fail'] = $custom_fail;
        if ($extend_message !== null) $data['extend_message'] = $extend_message;
        if ($coupon_code !== null) $data['coupon_code'] = $coupon_code;

        return $data;

    }
    public function generateCoupon($generate_id, $timestamp, $shop_id)
    {
        $generate = $this->generateRepository->getGenerateById($generate_id);
        $header_message = config('constant.DEFAULT_HEADER_MESSAGE');
        if (! $generate) {
            return $this->generateCouponResponse($header_message,'WHOOPS!','This offer does not exist!',null,null,null,null,null);
        }
        $app_url = $generate->app_url;
        if (! $generate->status) {
            return $this->generateCouponResponse($header_message,'WHOOPS!','This offer was disabled!',$app_url,$generate_id,null,null,null);
        }
        $app = $generate->app_name;
        $discount_id = $generate->discount_id;
        $conditions = $generate->conditions ?? [];

        $messages = $this->getMessagesFromGenerate($generate);
        $header_message = $generate->header_message ?? config('constant.DEFAULT_HEADER_MESSAGE');
        $used_message = $generate->used_message ?? config('constant.DEFAULT_USED_MESSAGE');

        // Lấy thông tin Shop từ API Customer.io

        //        $attributes = $this->getShopAttributes($shop_id, $app_url, $generate_id);
        //        $shop_name = $attributes->shop_name ?? null;

        //test
        $attributes = [
            'shop_name' => 'shop3',
            'name_status' => 'fg',
            'fg_status' => 'charged',
        ];
        $shop_name = $attributes['shop_name'];
        // Kiểm tra nếu shop đã có Coupon
        $coupon = $this->couponRepository->getCouponByDiscountIdandShop($discount_id, "{$shop_name}.myshopify.com", $app);
        if ($coupon) {
            if ($coupon->times_used > 0) {
                return $this->generateCouponResponse($header_message,$messages['fail_message'],$used_message,$app_url,$generate_id,null,null,null);
            }
            return $this->generateCouponResponse($header_message,$messages['success_message'],null,$app_url,$generate_id,null,$messages['extend_message'],$coupon->code);
        }
        // Kiểm tra thời gian hết hạn
        $discount = $this->discountRepository->findDiscountByIdNoCoupon($discount_id, $app);
        $discount_expired = $discount->expired_at ? Carbon::parse($discount->expired_at)->timestamp : null;

        $expired_timestamp = $timestamp + ($generate->expired_range * 24 * 60 * 60);
        if (($discount_expired && now()->timestamp > $discount_expired) || now()->timestamp > $expired_timestamp) {
            return $this->generateCouponResponse($header_message,$messages['fail_message'],$messages['reason_expired'],$app_url,$generate_id,null,null,null);
        }
        // 9️⃣ Kiểm tra giới hạn sử dụng Coupon
        $number_coupon = $this->couponRepository->countCouponByDiscountIdAndCode($discount_id, $app);
        if ($generate->limit && $generate->limit <= $number_coupon) {
            return $this->generateCouponResponse($header_message,$messages['fail_message'],$messages['reason_limit'],$app_url,$generate_id,null,null,null);
        }
        // 🔟 Kiểm tra điều kiện (Conditions)
        if ($conditions) {
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

                    $name_status = $arr_con[0] . '_status';
                    $status = $arr_con[1];
                    // Nếu không có app_status
                    // Hoặc app_status khác
                    // thì lưu text.
                    // Nếu không thì text = ""

                    try {
                        $customer_status = $attributes[$name_status];

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
                            $text_or .= "<span class='app_status'> " . $prefix_app[$arr_con[0]] . "</span> must be <span class='app_status'>Not Installed yet</span></p>";
                        } else {
                            $text_or .= "<span class='app_status'> " . $prefix_app[$arr_con[0]] . "</span> must be <span class='app_status'>" . $status . '</span></p>';
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
                return $this->generateCouponResponse($header_message,$messages['fail_message'],$messages['reason_condition'],$app_url,$generate_id,$text_or,null,null);
            }
        }
        // 🔥 Nếu tất cả điều kiện đạt -> Tạo mới Coupon

        $this->generateCouponn($discount_id,$shop_name,$app);
        return $this->generateCouponResponse($header_message,$messages['success_message'],null,$app_url,$generate_id,null,$messages['extend_message'],null);
    }
    public function generateCouponn($discount_id,$shop_name,$app){
        $dataCoupon = [];
        $dataCoupon['discount_id'] = $discount_id;
        $dataCoupon['shop'] = $shop_name . '.myshopify.com';
        $dataCoupon['times_used'] = 0;
        $dataCoupon['status'] = 1;
        $dataCoupon['code'] = $this->generateUniqueCouponCode($discount_id, $shop_name, $app);

        if ($this->isAutomaticCoupon($app)) {
            $dataCoupon['automatic'] = true;
        }
        $this->couponRepository->createCoupon($dataCoupon, $app);
    }





    public function createCouponFromAffiliatePartner(array $data, string $appCode, string $shopName)
    {
        $percentage = Arr::get($data, 'percentage', 0);
        $trialDays = Arr::get($data, 'trial_days', 0);

        $connectionMap = [
            'up_promote' => env('DB_CONNECTION_APP_13'),
            'bon' => env('DB_CONNECTION_APP_15'),
            'deco' => env('DB_CONNECTION_APP_3'),
            'bogos' => env('DB_CONNECTION_APP_16'),
            'search_pie' => env('DB_CONNECTION_APP_12'),
        ];

        $connection = $connectionMap[$appCode] ?? '';

        if (! $connection) {
            throw new Exception('Not found connection');
        }

        $name = "affiliate_partner_{$appCode}_{$percentage}_{$trialDays}";

        $discount = $this->discountRepository->UpdateOrCreateDiscountInAffiliatePartner($name, $percentage, $trialDays, $connection);
        $shop = "{$shopName}.myshopify.com";

        $existingCoupon = $this->couponRepository->getCouponByDiscountIdandShop($discount, $shop, $connection);
        if ($existingCoupon) {
            if ($existingCoupon->times_used == 0) {
                throw new Exception('Coupon already exists');
            }
        }
        $codeName = $this->generateCodeName($connection, $appCode, $discount->id, 'AF-');

        $data = [
            'code' => $codeName,
            'discount_id' => $discount->id,
            'shop' => $shop,
            'times_used' => 0,
            'status' => 1,
            'automatic' => true,
        ];

        return $this->couponRepository->createCoupon($data, $connection);
    }
    private function generateCodeName(
        string $connection,
        string $appCode,
        int $discountId,
        string $prefix = ''
    ) {
        $partialCode = $appCode . $discountId . random_int(1, 10000) . time();
        $code = $prefix . md5($partialCode);
        $exist = Coupon::on($connection)->where('code', $code)->first();
        if ($exist) {
            return $this->generateCodeName($connection, $appCode, $discountId, $prefix);
        }

        return $code;
    }

    private function getMessagesFromGenerate($generate)
    {
        return [
            'success_message' => $generate->success_message['message'] ?? config('constant.DEFAULT_SUCCESS_MESSAGE'),
            'extend_message' => $generate->success_message['extend'] ?? config('constant.DEFAULT_EXTEND_MESSAGE'),
            'fail_message' => $generate->fail_message['message'] ?? config('constant.DEFAULT_FAIL_MESSAGE'),
            'reason_expired' => $generate->fail_message['reason_expired'] ?? config('constant.DEFAULT_EXPIRED_REASON'),
            'reason_limit' => $generate->fail_message['reason_limit'] ?? config('constant.DEFAULT_LIMIT_REASON'),
            'reason_condition' => $generate->fail_message['reason_condition'] ?? config('constant.DEFAULT_CONDITION_REASON'),
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


    private function generateUniqueCouponCode($discount_id, $shop_name, $app)
    {
        do {
            $newCode = strtoupper('GENAUTO' . random_int(1, 1000) . substr(md5("{$app}_{$discount_id}_{$shop_name}"), 2, 4));
            $exists = $this->couponRepository->getCouponByCode($newCode, $app);
        } while ($exists);

        return $newCode;
    }

    private function isAutomaticCoupon($app)
    {
        $automatic_apps = [
            'banner', 'cs',
            'pl', 'customer_attribute', 'spin_to_win', 'smart_image_optimizer',
            'seo_booster', 'affiliate', 'loyalty', 'freegifts',
            'freegifts_new', 'reviews_importer',
        ];

        return in_array($app, $automatic_apps);
    }

}
