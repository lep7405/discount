<?php

namespace App\Services\Coupon;

use App\Exceptions\CouponException;
use App\Exceptions\DiscountException;
use App\Exceptions\NotFoundException;
use App\Repositories\Coupon\CouponRepository;
use App\Repositories\Discount\DiscountRepository;
use Illuminate\Support\Arr;

class CouponServiceImp implements CouponService
{
    public function __construct(protected CouponRepository $couponRepository, protected DiscountRepository $discountRepository) {}

    public function index(string $databaseName, array $filters)
    {
        $countAll = $this->couponRepository->countCoupons($databaseName);
        $filters = $this->handleFilters($countAll, $filters);
        $couponData = $this->couponRepository->getAll(null, $databaseName, $filters);

        return [
            'couponData' => $couponData,
            'totalPagesCoupon' => $couponData->lastPage(),
            'totalItemsCoupon' => $couponData->total(),
            'currentPagesCoupon' => $couponData->currentPage(),
            'totalCoupons' => $countAll,
        ];
    }

    public function handleFilters(int $countAll, array $filters)
    {
        $perPage = Arr::get($filters, 'perPageCoupon', 5);
        $perPage = $perPage == -1 ? $countAll : $perPage;
        Arr::set($filters, 'perPageCoupon', $perPage);

        $arrangeTimesUsed = Arr::get($filters, 'timeUsed');
        if ($arrangeTimesUsed && ! in_array($arrangeTimesUsed, ['desc', 'asc'])) {
            Arr::set($filters, 'timeUsed', null);
        }
        $status = Arr::get($filters, 'status');
        if ($status && ! in_array($status, ['0', '1'])) {
            Arr::set($filters, 'status', null);
        }

        return $filters;
    }

    public function store(string $databaseName, array $attributes)
    {
        if ((
            $databaseName == 'banner' ||
            $databaseName == 'cs' ||
            $databaseName == 'pl' ||
            $databaseName == 'customer_attribute' ||
            $databaseName == 'spin_to_win' ||
            $databaseName == 'smart_image_optimizer' ||
            $databaseName == 'smart_seo_json_ld' ||
            $databaseName == 'affiliate' ||
            $databaseName == 'loyalty' ||
            $databaseName == 'reviews_importer' ||
            $databaseName == 'freegifts' ||
            $databaseName == 'freegifts_new'
        ) && $attributes['shop'] != null) {
            $attributes['automatic'] = true;
        }
        $formData = Arr::only($attributes, ['code', 'shop', 'discountId', 'automatic']);

        return $this->couponRepository->createCoupon($databaseName, $formData);
    }

    public function update(int $id, string $databaseName, array $attributes)
    {
        $attributes = Arr::only($attributes, ['code', 'shop', 'discountId']);

        $coupon = $this->getCouponById($id, $databaseName);

        if ($coupon->timesUsed && $coupon->timesUsed > 0) {
            throw CouponException::cannotUpdate();
        }
        $couponByCode = $this->couponRepository->findByCode(Arr::get($attributes, 'code'), $databaseName);
        if ($couponByCode) {
            if ($couponByCode->id != $id) {
                throw CouponException::codeAlreadyExist();
            }
        }

        return $this->couponRepository->updateCoupon($id, $databaseName, $attributes);
    }

    public function getCouponById(int $id, string $databaseName)
    {
        $coupon = $this->couponRepository->findById($id, $databaseName);
        if (! $coupon) {
            throw NotFoundException::Notfound('Coupon not found');
        }

        return $coupon;
    }

    public function decrementTimesUsedCoupon(int $id, string $databaseName, int $numDecrement)
    {
        $coupon = $this->getCouponById($id, $databaseName);
        if ($coupon->timesUsed < $numDecrement) {
            throw CouponException::timesUsedLessThanDecrement();
        }
        $this->couponRepository->decrementTimesUsed($id, $databaseName, $numDecrement);
    }

    public function delete(int $id, string $databaseName)
    {
        $coupon = $this->getCouponById($id, $databaseName);
        if ($coupon->timesUsed && $coupon->timesUsed > 0) {
            throw CouponException::cannotDeleteCouponAlreadyUsed();
        }

        return $this->couponRepository->deleteCoupon($id, $databaseName);
    }

    public function createCouponByDiscount(int $discountId, string $databaseName, array $attributes)
    {
        $discount = $this->discountRepository->findById($discountId, $databaseName);
        if (! $discount) {
            throw NotFoundException::Notfound('Discount not found');
        }

        Arr::set($attributes, 'discountId', $discount->id);
        Arr::set($attributes, 'timesUsed', 0);
        $attributes = Arr::only($attributes, ['code', 'shop', 'discountId','timesUsed']);

        return $this->couponRepository->createCoupon($databaseName, $attributes);
    }

    public function getAllCouponsByDiscount($discountId, string $databaseName, array $filters)
    {
        $discount = $this->discountRepository->findByIdWithCoupon($discountId, $databaseName);
        $countAll = count($discount->coupon);
        $this->handleFilters($countAll, $filters);
        $couponData = $this->couponRepository->getAll($discountId, $databaseName, $filters);

        return [
            'couponData' => $couponData,
            'discountData' => $discount,
            'totalPagesCoupon' => $couponData->lastPage(),
            'totalItemsCoupon' => $couponData->total(),
            'currentPagesCoupon' => $couponData->currentPage(),
        ];
    }
}
