<?php

namespace App\Services\Discount;

use App\Exceptions\DiscountException;
use App\Repositories\Coupon\CouponRepository;
use App\Repositories\Discount\DiscountRepository;
use App\Validator\DiscountValidator;
use Illuminate\Support\Arr;

class DiscountServiceImp implements DiscountService
{
    public function __construct(protected DiscountRepository $discountRepository, protected CouponRepository $couponRepository) {}

    // cho show
    public function index(array $filters, $databaseName)
    {
        $count_all = $this->discountRepository->countDiscount($databaseName);

        $perPage = Arr::get($filters, 'per_page_discount', 5);
        $perPage = $perPage == -1 ? $count_all : $perPage;

        $started_at = Arr::get($filters, 'started_at');
        if ($started_at && ! in_array($started_at, ['desc', 'asc'])) {
            throw DiscountException::inValidStartedAt(['message'=>'Invalid started_at']);
        }
        Arr::set($filters, 'per_page_discount', $perPage);
        Arr::set($filters, 'started_at', $started_at);

        $discountData = $this->discountRepository->getAllDiscounts($filters, $databaseName);
        return [
            'discountData' => $discountData,
            'total_pages_discount' => $discountData->lastPage(),
            'total_items_discount' => $discountData->total(),
            'current_pages_discount' => $discountData->currentPage(),
            'total_items' => $count_all,
        ];
    }
    public function getAllDiscountForCreateOrUpdateCoupon($databaseName)
    {
        return $this->discountRepository->getAllDiscountsForCreateOrUpdateCoupon($databaseName);
    }
    public function store(array $attributes, $databaseName)
    {
        return $this->discountRepository->createDiscount($attributes, $databaseName);
    }
    public function update($id, array $attributes, $databaseName)
    {
        $discount = $this->getDiscountWithCoupon($id, $databaseName);
        $discount_status = $this->getStatusDiscount($discount);
        $validateAttributes = DiscountValidator::validateEdit($attributes, $discount_status, $databaseName);
        if(!Arr::get($attributes,'discount_for_x_month')){
            $validateAttributes['discount_month']=null;
        }
        return $this->discountRepository->updateDiscount($validateAttributes, $id, $databaseName);
    }
    public function delete($id, $databaseName): void
    {
        $discount = $this->getDiscountWithCoupon($id, $databaseName);
        $discount_status = $this->getStatusDiscount($discount);
        if ($discount_status) {
            throw DiscountException::canNotDelete(['message'=>['Can not delete discount']]);
        }
        $this->couponRepository->deleteCouponByDiscountId($discount->id, $databaseName);
        $this->discountRepository->deleteDiscount($discount->id, $databaseName);
    }
    /**
     * @throws DiscountException
     */
    public function getDiscountAndStatus($id, $databaseName): array
    {
        $discount = $this->getDiscountWithCoupon($id, $databaseName);
        $discountStatus = $this->getStatusDiscount($discount);
        return [
            'discount' => $discount,
            'discountStatus' => $discountStatus,
        ];
    }
    /**
     * @throws DiscountException
     */
    public function getDiscountWithCoupon($id, $databaseName)
    {
        $discount = $this->discountRepository->findDiscountByIdWithCoupon($id, $databaseName);
        if (! $discount) {
            throw DiscountException::notFound(['error'=>['Discount not found']]);
        }

        return $discount;
    }

    public function getStatusDiscount($discount)
    {
        return $discount->coupon->contains(function ($item) {
            return $item->times_used > 0;
        });
    }

    // get discount info
    public function getDiscountInfo(int $id, string $databaseName)
    {
        $discount= $this->discountRepository->findDiscountByIdNoCoupon($id, $databaseName);
        if(! $discount) {
            throw DiscountException::notFound(['message'=>['Discount not found1']]);
        }
        return Arr::only($discount->toArray(), [
            'id', 'name', 'started_at', 'expired_at', 'type', 'value', 'usage_limit', 'trial_days',
        ]);
    }


}
