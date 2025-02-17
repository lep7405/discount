<?php

namespace App\Services\Coupon;

use App\Exceptions\CouponException;
use App\Exceptions\DiscountException;
use App\Repositories\Coupon\CouponRepository;
use App\Repositories\Discount\DiscountRepository;
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
        \Illuminate\Log\log($filters);
        $couponData = $this->couponRepository->getAllCoupons($filters, $databaseName);
        $total_items = $couponData->total();
        $total_pages = $couponData->lastPage();
        $current_pages = $couponData->currentPage();

        return [
            'couponData' => $couponData,
            'total_pages_coupon' => $total_pages,
            'total_items_coupon' => $total_items,
            'current_pages_coupon' => $current_pages,
        ];
    }

    public function create(array $data, string $databaseName)
    {
        if ($databaseName == 'bannerslider') {
            $connectionName = 'banner';
        } elseif ($databaseName == 'productlabels') {
            $connectionName = 'pl';
        }
        $formData = Arr::only($data, ['code', 'shop', 'discount_id']);
        Arr::set($formData, 'times_used', 0);

        if (($databaseName == 'bannerslider' ||
                $databaseName == 'banner' ||
                $databaseName == 'currency_switcher' ||
                $databaseName == 'productlabels' ||
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
        ) && $formData['shop'] != null) {
            $formData['automatic'] = true;
        }

        return $this->couponRepository->createCoupon($formData, $connectionName);
    }

    public function getCoupon(int $id, string $databaseName)
    {
        return $this->couponRepository->getCouponById($id, $databaseName);
    }

    public function decrementCoupon(int $id, int $numDecrement, string $databaseName)
    {
        $coupon = $this->couponRepository->getCouponById($id, $databaseName);
        if (! $coupon) {
            throw CouponException::notFound();
        }
        if ($coupon->times_used < $numDecrement) {
            throw CouponException::timesUsedLessThanDecrement();
        }
        $this->couponRepository->decrementTimesUsed($id, $numDecrement, $databaseName);
    }

    public function update(array $data, int $id, string $databaseName)
    {
        $coupon = $this->couponRepository->getCouponById($id, $databaseName);
        if (! $coupon) {
            throw CouponException::notFound();
        }

        $couponByCode = $this->couponRepository->getCouponByCode($data['code'], $databaseName);
        if ($couponByCode) {
            if ($couponByCode->id != $id) {
                throw CouponException::codeAlreadyExist();
            }
        }
        $formData = Arr::only($data, ['code', 'shop', 'discount_id']);

        return $this->couponRepository->updateCoupon($formData, $id, $databaseName);
    }

    public function delete(int $id, string $databaseName)
    {
        $coupon = $this->couponRepository->getCouponById($id, $databaseName);
        if (! $coupon) {
            throw CouponException::notFound();
        }
        if ($coupon->times_used > 0) {
            throw CouponException::cannotDelete();
        }

        return $this->couponRepository->deleteCoupon($id, $databaseName);
    }

    public function createByDiscount(array $data, int $discount_id, string $databaseName)
    {
        $discount = $this->discountRepository->findDiscountById($discount_id, $databaseName);
        if (! $discount) {
            throw DiscountException::notFound();
        }
        Arr::set($data, 'discount_id', $discount->id);
        Arr::set($data, 'times_used', 1);

        return $this->couponRepository->createCoupon($data, $databaseName);
    }

    public function allCouponsByDiscount(string $discount_id, string $databaseName, array $filters = [])
    {
        $count_all = $this->couponRepository->countCoupons();
        $perPage = Arr::get($filters, 'per_page', 5);
        $status = Arr::get($filters, 'status');
        $perPage = $perPage == -1 ? $count_all : $perPage;
        $status = $status !== null ? (int) $status : null;
        Arr::set($filters, 'per_page', $perPage);
        Arr::set($filters, 'status', $status);
        $couponsData = $this->couponRepository->getAllCoupons($discount_id, $databaseName);
    }
}
