<?php

namespace App\Services\Report;

use App\Exceptions\CouponException;
use App\Exceptions\DiscountException;
use App\Repositories\Coupon\CouponRepository;
use App\Repositories\Discount\DiscountRepository;
use Illuminate\Support\Arr;

class ReportServiceImp implements ReportService
{
    public function __construct(protected DiscountRepository $discountRepository, protected CouponRepository $couponRepository) {}

    public function index(array $filters, string $databaseName)
    {
        $count_all = $this->discountRepository->countDiscount($databaseName);
        $perPage = Arr::get($filters, 'per_page_discount', 5);
        $perPage = $perPage == -1 ? $count_all : $perPage;
        $started_at = Arr::get($filters, 'started_at');
        if ($started_at && ! in_array($started_at, ['desc', 'asc'])) {
            throw DiscountException::inValidStartedAt();
        }
        Arr::set($filters, 'per_page_discount', $perPage);
        Arr::set($filters, 'started_at', $started_at);

        $discountData = $this->discountRepository->getAllDiscountsReports($filters, $databaseName);
        $total_items_discount = $discountData->total();
        $total_pages_discount = $discountData->lastPage();
        $current_pages_discount = $discountData->currentPage();

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

        $couponData = $this->couponRepository->getAllCouponsReport($filters, $databaseName);
        $total_items_coupon = $couponData->total();
        $total_pages_coupon = $couponData->lastPage();
        $current_pages_coupon = $couponData->currentPage();

        $discounts = $this->discountRepository->getAllNotFilterWithCoupon($databaseName);
        $count_discount = $discounts->count();
        $count_discount_used = 0;
        $count_coupon = 0;
        $count_coupon_used = 0;
        foreach ($discounts as $key => $discount) {
            $total = 0;
            foreach ($discount->coupon as $coupon) {
                $count_coupon++;
                $total += $coupon->times_used;
            }
            $count_coupon_used = $count_coupon_used + $total;
            if ($total > 0) {
                $count_discount_used += 1;
            }
        }

        return [
            'discountData' => $discountData,
            'total_pages_discount' => $total_pages_discount,
            'total_items_discount' => $total_items_discount,
            'current_pages_discount' => $current_pages_discount,

            'couponData' => $couponData,
            'total_pages_coupon' => $total_pages_coupon,
            'total_items_coupon' => $total_items_coupon,
            'current_pages_coupon' => $current_pages_coupon,

            'count_discount' => $count_discount,
            'count_discount_used' => $count_discount_used,
            'count_coupon' => $count_coupon,
            'count_coupon_used' => $count_coupon_used,
        ];
    }
}
