<?php

namespace App\Services\Coupon;

use App\Exceptions\CouponException;
use App\Exceptions\DiscountException;
use App\Exceptions\NotFoundException;
use App\Repositories\Coupon\CouponRepository;
use App\Repositories\Discount\DiscountRepository;
use App\Validator\CouponUpdateValidator;
use Illuminate\Support\Arr;

class CouponServiceImp implements CouponService
{
    public function __construct(protected CouponRepository $couponRepository, protected DiscountRepository $discountRepository) {}

    public function index(array $filters, $databaseName)
    {
        $count_all = $this->couponRepository->countCoupons($databaseName);
        $perPage = Arr::get($filters, 'per_page_coupon', 5);
        $status = Arr::get($filters, 'status');
        $perPage = $perPage == -1 ? $count_all : $perPage;
        $status = $status !== null ? (int) $status : null;
        $arrange_times_used = Arr::get($filters, 'time_used');
        if ($arrange_times_used && ! in_array($arrange_times_used, ['desc', 'asc'])) {
            throw CouponException::inValidArrangeTime();
        }
        Arr::set($filters, 'per_page_coupon', $perPage);
        Arr::set($filters, 'status', $status);
        $couponData = $this->couponRepository->getAllCoupons($filters, $databaseName);

        return [
            'couponData' => $couponData,
            'total_pages_coupon' => $couponData->lastPage(),
            'total_items_coupon' => $couponData->total(),
            'current_pages_coupon' => $couponData->currentPage(),
            'total_items' => $count_all,
        ];
    }

    public function create(array $data, string $databaseName)
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
        ) && $data['shop'] != null) {
            $data['automatic'] = true;
        }

        return $this->couponRepository->createCoupon($data, $databaseName);
    }

    public function update(array $data, int $id, string $databaseName)
    {
        $coupon = $this->couponRepository->getCouponById($id, $databaseName);
        if (! $coupon) {
            throw NotFoundException::Notfound('Coupon not found');
        }

        if ($coupon->times_used && $coupon->times_used > 0) {
            throw CouponException::cannotUpdate(['error' => ['Coupon can not update']]);
        }

        //        $data = CouponUpdateValidator::validateEdit($data, $databaseName);

        $couponByCode = $this->couponRepository->getCouponByCode($data['code'], $databaseName);
        if ($couponByCode) {
            if ($couponByCode->id != $id) {
                throw CouponException::codeAlreadyExist(['code' => ['Code existed']]);
            }
        }
        $formData = Arr::only($data, ['code', 'shop', 'discount_id']);

        return $this->couponRepository->updateCoupon($formData, $id, $databaseName);
    }

    public function getCoupon(int $id, string $databaseName)
    {
        return $this->couponRepository->getCouponById($id, $databaseName);
    }

    public function decrementCoupon(int $id, int $numDecrement, string $databaseName)
    {
        $coupon = $this->couponRepository->getCouponById($id, $databaseName);
        if (! $coupon) {
            throw CouponException::notFound(['error' => ['Coupon not found']]);
        }
        if ($coupon->times_used < $numDecrement) {
            throw CouponException::timesUsedLessThanDecrement(['decrement' => ['Invalid numDecrement']]);
        }
        $this->couponRepository->decrementTimesUsed($id, $numDecrement, $databaseName);
    }

    public function delete(int $id, string $databaseName)
    {
        $coupon = $this->couponRepository->getCouponById($id, $databaseName);
        if (! $coupon) {
            throw CouponException::notFound();
        }
        if ($coupon->times_used && $coupon->times_used > 0) {
            throw CouponException::cannotDelete(['message' => ['Can not delete coupon']]);
        }

        return $this->couponRepository->deleteCoupon($id, $databaseName);
    }

    public function createByDiscount(array $data, int $discount_id, string $databaseName)
    {
        $discount = $this->discountRepository->findDiscountByIdNoCoupon($discount_id, $databaseName);
        if (! $discount) {
            throw DiscountException::notFound(['error' => ['Discount not found']]);
        }

        Arr::set($data, 'discount_id', $discount->id);
        Arr::set($data, 'times_used', 0);

        return $this->couponRepository->createCoupon($data, $databaseName);
    }

    public function allCouponsByDiscount($discount_id, string $databaseName, array $filters)
    {
        $discount = $this->discountRepository->findDiscountByIdWithCoupon($discount_id, $databaseName);
        $count_all = count($discount->coupon);
        $perPage = Arr::get($filters, 'per_page_coupon', 5);
        $status = Arr::get($filters, 'status');
        $perPage = $perPage == -1 ? $count_all : $perPage;
        $status = $status !== null ? (int) $status : null;
        $arrange_times_used = Arr::get($filters, 'time_used');
        if ($arrange_times_used && ! in_array($arrange_times_used, ['desc', 'asc'])) {
            throw CouponException::inValidArrangeTime();
        }
        Arr::set($filters, 'per_page_coupon', $perPage);
        Arr::set($filters, 'status', $status);
        $couponData = $this->couponRepository->getAllCouponsByDiscount($discount_id, $filters, $databaseName);

        return [
            'couponData' => $couponData,
            'discountData' => $discount,
            'total_pages_coupon' => $couponData->lastPage(),
            'total_items_coupon' => $couponData->total(),
            'current_pages_coupon' => $couponData->currentPage(),
        ];
    }
}
