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
use Customerio\Client;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;

class GenerateServiceImp implements GenerateService
{
    public function __construct(protected GenerateRepository $generateRepository, protected DiscountRepository $discountRepository, protected CouponRepository $couponRepository) {}


    public function index(array $filters)
    {
        $countAll = $this->generateRepository->countGenerate();
        $filters = $this->handleFilters($countAll, $filters);
        $generateData = $this->generateRepository->getAll($filters);
        $generateDataItems = $this->handleGenerateData($generateData);

        return [
            'generateData' => $generateDataItems,
            'totalPages' => $generateData->lastPage(),
            'totalItems' => $generateData->total(),
            'totalGenerates' => $countAll,
            'currentPages' => $generateData->currentPage(),
        ];
    }
    public function handleFilters($countAll,$filters)
    {
        $perPage = Arr::get($filters, 'perPage', config('constant.DEFAULT_PER_PAGE'));
        $perPage = ($perPage == -1) ? $countAll : $perPage;
        Arr::set($filters, 'perPage', $perPage);
        $status = Arr::get($filters, 'status');
        if ($status && ! in_array($status, ['0', '1'])) {
            Arr::set($filters, 'status', null);
        }
        return $filters;
    }

    public function handleGenerateData($generateData)
    {

        $groupedGenerates = $generateData->groupBy('app_name');
        $discountMap = [];
        foreach ($groupedGenerates as $appName => $group) {
            $discountIds = $group->pluck('discount_id')->unique()->toArray();
            $discounts = $this->discountRepository->findByIdsAndApp($discountIds, $appName);
            $discountMap[$appName] = $discounts->keyBy('id');
        }

        return $generateData->map(function ($gen) use ($discountMap) {

            $discount = $discountMap[$gen['app_name']][$gen['discount_id']] ?? null;
            if (! $discount) {
                throw NotFoundException::notFound('Discount not found');
            }
            $gen['db_name'] = $gen['app_name'];
            $gen['app_name'] = config('database.connections.' . $gen['app_name'] . '.app_name');
            $gen['expired'] = $discount->expired_at && now()->greaterThan($discount->expired_at);
            $gen['discount_name'] = $discount->name;
            $gen['discount_id'] = $discount->id;

            return $gen;
        });
    }

    public function create(array $databaseName)
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

    //create
    public function store(array $attributes)
    {
        [$discountId, $appName] = explode('&', Arr::get($attributes, 'discount_app'));

        $this->checkDiscount($discountId, $appName);
        $this->checkUniqueGenerate($discountId, $appName);

        $attributes['app_name'] = $appName;
        $attributes['discount_id'] = $discountId;
        $this->prepareData($attributes);
        $attributes = array_intersect_key($attributes, array_flip((new Generate)->getFillable()));
        return $this->generateRepository->createGenerate($attributes);
    }

    public function checkDiscount($discountId, $appName)
    {
        $discount = $this->findDiscountById($discountId, $appName);

        if (! $discount->expired_at || (now()->greaterThan($discount->expired_at))) {
            throw DiscountException::discountExpired();
        }
    }

    public function findDiscountById($discountId, $appName)
    {
        $discount = $this->discountRepository->findById($discountId, $appName);
        if (! $discount) {
            throw NotFoundException::Notfound('Discount not found!');
        }

        return $discount;
    }

    public function checkUniqueGenerate(int $discountId,string $appName)
    {
        $generate = $this->generateRepository->findByDiscountIdAndAppName($discountId, $appName);
        if ($generate) {
            throw GenerateException::generateExist();
        }
    }

    public function prepareData(&$attributes)
    {
        $attributes['conditions'] = $this->handleCondition(Arr::get($attributes, 'condition_object'));
        $this->handleMessage($attributes);

        return $attributes;
    }

    public function handleCondition($data): array
    {
        if (empty($data)) {
            return [];
        }
        $conditionArray = json_decode($data, true);
        if (! is_array($conditionArray)) {
            return [];
        }
        $conditions = [];
        foreach ($conditionArray as $condition) {
            if (! is_array($condition['apps']) || empty($condition['apps'])) {
                continue;
            }

            $appConditions = [];
            foreach ($condition['apps'] as $app) {
                if (isset($app['name'], $app['status'])) {
                    $appConditions[] = $app['name'] . '&' . $app['status'];
                }
            }

            if (! empty($appConditions)) {
                $conditions[] = implode('||', $appConditions);
            }
        }

        return $conditions;
    }

    public function handleMessage(&$data): void
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

    //update
    public function edit(int $id,array $databaseName)
    {
        $generate = $this->findGenerateById($id);
        $coupon = $this->couponRepository->findByDiscountIdAndCode($generate->discount_id, $generate->app_name);
        $discountData = [];
        foreach ($databaseName as $db) {
            $data = $this->discountRepository->getAllDiscountIdAndName($db);
            foreach ($data as $d) {
                $d['databaseName'] = $db;
                $d['appName'] = config('database.connections.' . $db . '.app_name');
                $discountData[] = $d;
            }
        }
        return [
            'generate' => $generate,
            'discountData' => $discountData,
            'status_del' => ! $coupon,
        ];
    }

    public function update(int $id, array $attributes)
    {
        $generate = $this->findGenerateById($id);
        $currentDiscountId = $generate->discount_id;
        $currentAppName = $generate->app_name;

        $existingCoupon = $this->couponRepository->findByDiscountIdAndCode($currentDiscountId, $currentAppName);
        GenerateUpdateValidator::validateUpdate(! $existingCoupon, $attributes);

        if (! $existingCoupon) {
            [$newDiscountId, $newAppName] = explode('&', Arr::get($attributes, 'discount_app'));
            if ($currentDiscountId != $newDiscountId || $currentAppName != $newAppName) {
                $this->checkDiscount($newDiscountId, $newAppName);
                $this->checkUniqueGenerate($newDiscountId, $newAppName);
                $data['discount_id'] = $newDiscountId;
                $data['app_name'] = $newAppName;
            }
        }
        $this->prepareData($attributes);
        $attributes = array_intersect_key($attributes, array_flip((new Generate)->getFillable()));

        return $this->generateRepository->updateGenerate($id, $attributes);
    }

    public function destroy(int $id)
    {
        $this->findGenerateById($id);
        $this->generateRepository->destroyGenerate($id);
    }

    public function changeStatus(int $id)
    {
        $generate = $this->findGenerateById($id);
        $this->generateRepository->updateGenerateStatus($id, $generate->status);
    }

    public function findGenerateById(int $id)
    {
        $generate = $this->generateRepository->findById($id);
        if (! $generate) {
            throw NotFoundException::notFound('Generate not found');
        }

        return $generate;
    }

    //private generate coupon
    public function privateGenerateCoupon($ip, int $generateId,string $shopName)
    {
        if (! $this->validateIp($ip)) {
            return $this->privateGenerateResponse(false, 'Ip not valid!');
        }

        $generate = $this->generateRepository->findById($generateId);
        if (! $generate) {
            return $this->privateGenerateResponse(false, 'Generate not exist!');
        }

        $app = $generate->app_name;
        $discountId = $generate->discount_id;

        $coupon = $this->couponRepository->findByDiscountIdandShop($discountId, "{$shopName}.myshopify.com", $app);
        if ($coupon) {
            if ($coupon->times_used > 0) {
                return $this->privateGenerateResponse(false, 'Coupon used!');
            }

            return $this->privateGenerateResponse(true, 'Coupon created!');
        }

        $discount = $this->discountRepository->findById($discountId, $app);
        if (! $discount) {
            return $this->privateGenerateResponse(false, 'Discount not found!');
        }
        if ($discount->expired_at != null && now()->greaterThan($discount->expired_at)) {
            return $this->privateGenerateResponse(false, 'Discount expired!');
        }

        $numberCoupon = $this->couponRepository->countByDiscountIdAndCode($discountId, $app);
        if ($generate->limit && $generate->limit <= $numberCoupon) {
            return $this->privateGenerateResponse(false, 'Limit Coupon!');
        }

        $this->createCouponForShop($discountId, $shopName, $app);

        return $this->privateGenerateResponse(true, 'Success generate coupon!');
    }

    public function validateIp($ip)
    {
        $ipServer = config('Discount_manager.ip_server');
        $ipServer = str_replace(' ', '', trim($ipServer));
        $ipServerArray = explode(',', $ipServer);
        foreach ($ipServerArray as $ipValue) {
            if (! filter_var($ipValue, FILTER_VALIDATE_IP)) {
                unset($ipServerArray[array_search($ipValue, $ipServerArray)]);
            }
        }
        if (! in_array($ip, $ipServerArray)) {
            return false;
        }

        return true;
    }

    public function createCouponForShop(int $discountId,string $shopName,string $app)
    {
        $dataCoupon = [
            'discount_id' => $discountId,
            'shop' => $shopName . '.myshopify.com',
            'times_used' => 0,
            'status' => 1,
            'code' => $this->generateUniqueCouponCode($discountId, $shopName, $app),
        ];

        if ($this->isAutomaticCoupon($app)) {
            $dataCoupon['automatic'] = true;
        }
        $this->couponRepository->createCoupon($app,$dataCoupon);
    }

    private function generateUniqueCouponCode($discountId, $shopName, $app)
    {
        do {
            $newCode = strtoupper('GENAUTO' . random_int(1, 1000) . substr(md5("{$app}_{$discountId}_{$shopName}"), 2, 4));
            $exists = $this->couponRepository->findByCode($newCode, $app);
        } while ($exists);

        return $newCode;
    }
    private function isAutomaticCoupon($app)
    {
        $automaticApps = [
            'banner', 'cs',
            'pl', 'customer_attribute', 'spin_to_win', 'smart_image_optimizer',
            'seo_booster', 'affiliate', 'loyalty', 'freegifts',
            'freegifts_new', 'reviews_importer',
        ];

        return in_array($app, $automaticApps);
    }

    public function privateGenerateResponse($status, $message)
    {
        return [
            'status' => $status,
            'message' => $message,
        ];
    }

    //generate coupon
    /**
     * @throws NotFoundException
     */
    public function generateCoupon(int $generateId, $timestamp, $shopId)
    {
        $generate = $this->generateRepository->findById($generateId);
        $headerMessage = config('constant.DEFAULT_HEADER_MESSAGE');
        if (! $generate) {
            return $this->generateCouponResponse($headerMessage, contentMessage: 'WHOOPS!', reasons: 'This offer does not exist!');
        }
        $appUrl = $generate->app_url;
        if (! $generate->status) {
            return $this->generateCouponResponse($headerMessage, contentMessage: 'WHOOPS!', reasons: 'This offer was disabled!', appUrl: $appUrl, generateId: $generateId);
        }
        $app = $generate->app_name;
        $discountId = $generate->discount_id;

        $messages = $this->getMessagesFromGenerate($generate);
        $headerMessage = $generate->header_message ?? config('constant.DEFAULT_HEADER_MESSAGE');
        $usedMessage = $generate->used_message ?? config('constant.DEFAULT_USED_MESSAGE');

        // Lấy thông tin Shop từ API Customer.io
        $attributes = $this->getShopAttributes($shopId);
        if (! $attributes) {
            return $this->generateCouponResponse(headerMessage: 'Connection Error!', contentMessage: 'Oops! Can not connect to shop!', reasons: 'The shop may be down or experiencing issues. Please try again!', appUrl: $appUrl, generateId: $generateId);
        }
        $shopName = $attributes->shop_name ?? null;

        $coupon = $this->couponRepository->findByDiscountIdandShop($discountId, "{$shopName}.myshopify.com", $app);
        if ($coupon) {
            if ($coupon->times_used > 0) {
                return $this->generateCouponResponse(headerMessage: $headerMessage, contentMessage: $messages['fail_message'], reasons: $usedMessage, appUrl: $appUrl, generateId: $generateId);
            }

            return $this->generateCouponResponse(headerMessage: $headerMessage, contentMessage: $messages['success_message'], appUrl: $appUrl, generateId: $generateId, extendMessage: $messages['extend_message'], couponCode: $coupon->code);
        }

        $discount = $this->findDiscountById($discountId, $app);
        $expiredTimestamp = $timestamp + ($generate->expired_range * 24 * 60 * 60);
        if (($discount->expired_at && now()->greaterThan($discount->expired_at)) || now()->timestamp > $expiredTimestamp) {
            return $this->generateCouponResponse(headerMessage: $headerMessage, contentMessage: $messages['fail_message'], reasons: $messages['reason_expired'], appUrl: $appUrl, generateId: $generateId);
        }

        $numberCoupon = $this->couponRepository->countByDiscountIdAndCode($discountId, $app);
        if ($generate->limit && $generate->limit <= $numberCoupon) {
            return $this->generateCouponResponse(headerMessage: $headerMessage, contentMessage: $messages['fail_message'], reasons: $messages['reason_limit'], appUrl: $appUrl, generateId: $generateId);
        }

        $textOr = $this->checkConditions($generate->conditions, $attributes);
        if ($textOr) {
            return $this->generateCouponResponse(headerMessage: $headerMessage, contentMessage: $messages['fail_message'], reasons: $messages['reason_condition'], appUrl: $appUrl, generateId: $generateId, customFail: $textOr);
        }

        $this->createCouponForShop($discountId, $shopName, $app);

        return $this->generateCouponResponse(headerMessage: $headerMessage, contentMessage: $messages['success_message'], appUrl: $appUrl, generateId: $generateId, customFail: $messages['extend_message']);
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
    private function getShopAttributes($shopId)
    {
        $apiKey = config('Discount_manager.customerio.apiKey');

        $siteId = config('Discount_manager.customerio.siteId');

        $appKey = config('Discount_manager.customerio.appKey');

        $client = new Client($apiKey, $siteId);
        $endpoint = "/customers/{$shopId}/attributes";
        $client->setAppAPIKey($appKey);

        try {
            $getCustomerio = $client->get($endpoint);
            return $getCustomerio->customer->attributes;
        } catch (GuzzleException $e) {
            return false;
        }
    }
    public function checkConditions($conditions,array $attributes)
    {
        $prefixApp = [
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
            $arrOr = explode('||', $cd); // Tách trên 1 hàng các điều kiện OR
            $textOr = '';

            for ($i = 0; $i < count($arrOr); $i++) {
                $arrCon = explode('&', $arrOr[$i]); // Dạng của điều kiện name&status
                $nameStatus = $arrCon[0] . '_status';
                $status = $arrCon[1];

                try {
                    $customerStatus = $attributes[$nameStatus];
                } catch (Exception $e) {
                    $customerStatus = 'notinstalledyet';
                }

                if ($customerStatus == $status) {
                    $textOr = '';
                    break;
                } else {
                    $textOr .= '<p>';
                    if (count($arrOr) > 1) {
                        $textOr .= "<strong class='or_status'>OR</strong>";
                    }

                    if ($status == 'notinstalledyet') {
                        $textOr .= "<span class='app_status'> " . $prefixApp[$arrCon[0]] . "</span> must be <span class='app_status'>Not Installed yet</span></p>";
                    } else {
                        $textOr .= "<span class='app_status'> " . $prefixApp[$arrCon[0]] . "</span> must be <span class='app_status'>" . $status . '</span></p>';
                    }
                }
            }

            // Nếu có $textOr thì chứng tỏ điều kiện này ko thoả mãn. Break luôn.
            if ($textOr) {
                break;
            }
        }

        return $textOr;
    }
    private function generateCouponResponse(
        string $headerMessage,
        string $contentMessage,
        ?string $reasons = null,
        ?string $appUrl = null,
        ?int $generateId = null,
        ?string $customFail = null,
        ?string $extendMessage = null,
        ?string $couponCode = null
    ): array {
        $response = [
            'headerMessage' => $headerMessage,
            'contentMessage' => $contentMessage,
        ];

        if ($reasons !== null) {
            $response['reasons'] = $reasons;
        }
        if ($appUrl !== null) {
            $response['appUrl'] = $appUrl;
        }
        if ($generateId !== null) {
            $response['generateId'] = $generateId;
        }
        if ($customFail !== null) {
            $response['customFail'] = $customFail;
        }
        if ($extendMessage !== null) {
            $response['extendMessage'] = $extendMessage;
        }
        if ($couponCode !== null) {
            $response['couponCode'] = $couponCode;
        }

        return $response;
    }

    public function createCouponFromAffiliatePartner(array $formData, string $appCode, string $shopName)
    {
        $percentage = Arr::get($formData, 'percentage', 0);
        $trialDays = Arr::get($formData, 'trial_days', 0);

        $connectionMap = [
            'up_promote' => env('DB_CONNECTION_APP_13'),
            'bon' => env('DB_CONNECTION_APP_15'),
            'deco' => env('DB_CONNECTION_APP_3'),
            'bogos' => env('DB_CONNECTION_APP_16'),
            'search_pie' => env('DB_CONNECTION_APP_12'),
        ];

        $connection = $connectionMap[$appCode] ?? '';

        if (! $connection) {
            return [
                'message' => 'Not found connection',
            ];
        }

        $name = "affiliate_partner_{$appCode}_{$percentage}_{$trialDays}";
        $attributes= [
            'name' => $name,
            'type' => 'percentage',
            'value' => $percentage,
            'trial_days' => $trialDays,
        ];
        $discount = $this->discountRepository->UpdateOrCreateDiscountInAffiliatePartner($connection,$attributes);
        $shop = "{$shopName}.myshopify.com";

        $existingCoupon = $this->couponRepository->findByDiscountIdandShop($discount->id, $shop, $connection);
        if ($existingCoupon) {
            if ($existingCoupon->times_used == 0) {
                return [
                    'message' => 'Coupon already exists',
                ];
            }
        }

        $codeName = $this->generateCodeName($connection, $appCode, $discount->id, 'AF-');

        $attributes = [
            'code' => $codeName,
            'discount_id' => $discount->id,
            'shop' => $shop,
            'times_used' => 0,
            'status' => 1,
            'automatic' => true,
        ];

        $coupon = $this->couponRepository->createCoupon($connection,$attributes);

        return [
            'message' => 'Coupon created successfully',
            'coupon' => $coupon,
        ];
    }
    private function generateCodeName(
        string $connection,
        string $appCode,
        int $discountId,
        string $prefix = ''
    ) {
        $partialCode = $appCode . $discountId . random_int(1, 10000) . time();
        $code = $prefix . md5($partialCode);
        $exist = $this->couponRepository->findByCode($code, $connection);

        if ($exist) {
            return $this->generateCodeName($connection, $appCode, $discountId, $prefix);
        }

        return $code;
    }
}
