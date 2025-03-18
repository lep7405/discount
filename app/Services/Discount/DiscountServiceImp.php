<?php

namespace App\Services\Discount;

use App\Exceptions\DiscountException;
use App\Exceptions\NotFoundException;
use App\Repositories\Coupon\CouponRepository;
use App\Repositories\Discount\DiscountRepository;
use App\Validator\UpdateDiscountValidator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class DiscountServiceImp implements DiscountService
{
    public function __construct(protected DiscountRepository $discountRepository, protected CouponRepository $couponRepository) {}

    // cho show

    /**
     * @throws DiscountException
     */
    public function index(string $databaseName, array $filters): array
    {
        $countAll = $this->discountRepository->countDiscount($databaseName);
        $filters = $this->handleFilters($countAll, $filters);
        $discountData = $this->discountRepository->getAll( $databaseName ,$filters);

        return [
            'discountData' => $discountData,
            'totalPagesDiscount' => $discountData->lastPage(),
            'totalItemsDiscount' => $discountData->total(),
            'currentPagesDiscount' => $discountData->currentPage(),
            'totalDiscounts' => $countAll,
        ];
    }
    public function handleFilters(int $countAll, array $filters){
        $perPage = Arr::get($filters, 'perPageDiscount', config('constant.default_per_page'));
        $perPage = $perPage == -1 ? $countAll : $perPage;
        Arr::set($filters, 'perPageDiscount', $perPage);
        $startedAt = Arr::get($filters, 'startedAt');
        if ($startedAt && ! in_array($startedAt, ['desc', 'asc'])) {
            Arr::set($filters, 'startedAt', null);
        }
        return $filters;
    }

    public function store(string $databaseName, array $attributes)
    {
        return $this->discountRepository->createDiscount($databaseName ,$attributes);
    }
    public function update(int $id, string $databaseName, array $attributes)
    {
        $discount = $this->getDiscountWithCoupon($id, $databaseName);
        $hasCouponUsed = $this->hasCouponUsed($discount);
        $validateAttributes = UpdateDiscountValidator::validateUpdate($attributes, $hasCouponUsed, $databaseName);
        if (in_array($databaseName, config('constant.SPECIAL_DATABASE_NAMES'))) {
            if (Arr::get($attributes, 'discount_for_x_month') === '0') {
                $validateAttributes['discount_month'] = null;
            }
        }

        return $this->discountRepository->updateDiscount($id, $databaseName,$validateAttributes);
    }

    /**
     * @throws DiscountException
     * @throws NotFoundException
     */
    public function delete(int $id, string $databaseName): void
    {
        $discount = $this->getDiscountWithCoupon($id, $databaseName);
        $hasCouponUsed = $this->hasCouponUsed($discount);
        if ($hasCouponUsed) {
            throw DiscountException::canNotDelete();
        }
        $this->couponRepository->deleteByDiscountId($discount->id, $databaseName);
        $this->discountRepository->deleteDiscount($discount->id, $databaseName);
    }

    public function getAllDiscountIdAndName(string $databaseName)
    {
        return $this->discountRepository->getAllDiscountIdAndName($databaseName);
    }

    /**
     * @throws NotFoundException
     */
    public function getDiscountWithCoupon(int $id,string $databaseName)
    {
        $discount = $this->discountRepository->findByIdWithCoupon($id, $databaseName);
        if (! $discount) {
            throw NotFoundException::Notfound('Discount not found');
        }

        return $discount;
    }

    public function hasCouponUsed($discount)
    {
        return $discount->coupon->contains(function ($item) {
            return $item->times_used > 0;
        });
    }

    /**
     * @throws NotFoundException
     */
    public function getDiscountInfo(int $id, string $databaseName)
    {
        $discount = $this->discountRepository->findById($id, $databaseName);
        if (! $discount) {
            throw NotFoundException::Notfound('Discount not found');
        }

        return (object) Arr::only($discount->toArray(), [
            'id', 'name', 'started_at', 'expired_at', 'type', 'value', 'usage_limit', 'trial_days'
        ]);
    }
}
