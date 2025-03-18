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
        $discountStats = $this->processDiscountData($filters, $databaseName);

        $couponStats = $this->processCouponData($filters, $databaseName);

        $summaryStats = $this->calculate($databaseName);

        return array_merge($discountStats, $couponStats, $summaryStats);
    }

    private function processDiscountData(array &$filters, string $databaseName): array
    { $countAll = $this->discountRepository->countDiscount($databaseName);
        $filters = $this->handleFiltersDiscount($countAll, $filters);
        $discountData = $this->discountRepository->getAll( $databaseName ,$filters);

        return [
            'discountData' => $discountData,
            'totalPagesDiscount' => $discountData->lastPage(),
            'totalItemsDiscount' => $discountData->total(),
            'currentPagesDiscount' => $discountData->currentPage(),
            'totalDiscounts' => $countAll,
        ];
    }
    public function handleFiltersDiscount(int $countAll, array $filters){
        $perPageDiscount = Arr::get($filters, 'perPageDiscount', config('constant.default_per_page'));
        $perPageDiscount = $perPageDiscount == -1 ? $countAll : $perPageDiscount;
        Arr::set($filters, 'perPageDiscount', $perPageDiscount);
        $startedAt = Arr::get($filters, 'startedAt');
        if ($startedAt && ! in_array($startedAt, ['desc', 'asc'])) {
            Arr::set($filters, 'startedAt', null);
        }
        return $filters;
    }
    private function processCouponData(array &$filters, string $databaseName): array
    {
        $countAll = $this->couponRepository->countCoupons($databaseName);
        $filters = $this->handleFiltersCoupon($countAll, $filters);
        $couponData = $this->couponRepository->getAll(null, $databaseName, $filters);

        return [
            'couponData' => $couponData,
            'totalPagesCoupon' => $couponData->lastPage(),
            'totalItemsCoupon' => $couponData->total(),
            'currentPagesCoupon' => $couponData->currentPage(),
            'totalCoupons' => $countAll,
        ];
    }
    public function handleFiltersCoupon(int $countAll, array $filters)
    {
        $perPageCoupon = Arr::get($filters, 'perPageCoupon', 5);
        $perPageCoupon = $perPageCoupon == -1 ? $countAll : $perPageCoupon;
        Arr::set($filters, 'perPageCoupon', $perPageCoupon);

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

    private function calculate(string $databaseName): array
    {
        $discounts = $this->discountRepository->getAllWithCoupon($databaseName);
        $countDiscount = $discounts->count();
        $countDiscountUsed = 0;
        $countCoupon = 0;
        $countCouponUsed = 0;
        foreach ($discounts as $discount) {
            $total = 0;
            foreach ($discount->coupon as $coupon) {
                $countCoupon++;
                $total += $coupon->times_used;
            }

            $countCouponUsed += $total;

            if ($total > 0) {
                $countDiscountUsed++;
            }
        }
        return [
            'countDiscount' => $countDiscount,
            'countDiscountUsed' => $countDiscountUsed,
            'countCoupon' => $countCoupon,
            'countCouponUsed' => $countCouponUsed,
        ];
    }
}
