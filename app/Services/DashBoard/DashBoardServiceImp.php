<?php

namespace App\Services\DashBoard;

use App\Repositories\Discount\DiscountRepository;
use Exception;
use Illuminate\Support\Collection;

class DashBoardServiceImp implements DashBoardService
{
    public function __construct(protected DiscountRepository $discountRepository) {}

    public function index($databases)
    {
        $discountData = [];
        $couponData = [];
        $countDiscountUsed = 0;
        $countCouponUsed = 0;
        $dashboardApps = $this->initializeDashboardApps($databases);

        foreach ($databases as $key => $db) {
            $discounts = $this->getDiscountsWithCoupons($db);

            if ($discounts->isNotEmpty()) {
                $this->processDiscountsData(
                    $discounts,
                    $key,
                    $dashboardApps,
                    $discountData,
                    $couponData,
                    $countDiscountUsed,
                    $countCouponUsed
                );
            }
        }

        return [
            'discountData' => $discountData,
            'couponData' => $couponData,
            'countDiscountUsed' => $countDiscountUsed,
            'countCouponUsed' => $countCouponUsed,
            'apps' => $this->getAppNames(),
            'dashboardApps' => $dashboardApps,
        ];
    }

    private function initializeDashboardApps(array $databases): array
    {
        $dashboardApps = [];
        foreach ($databases as $key => $db) {
            $dashboardApps[$key] = [
                'db' => $db,
                'appName' => config("database.connections.{$db}.app_name"),
                'countDiscount' => 0,
                'countCoupon' => 0,
                'usedCoupons' => 0,
                'countCouponUsed' => 0,
            ];
        }
        return $dashboardApps;
    }

    private function processDiscountsData(
        Collection $discounts,
                   $key,
        array &$dashboardApps,
        array &$discountData,
        array &$couponData,
        int &$countDiscountUsed,
        int &$countCouponUsed
    ): void {
        $dashboardApps[$key]['countDiscount'] = $discounts->count();

        foreach ($discounts as $discount) {
            $discountData[] = $discount;

            $totalUsed = $discount->coupon->sum('times_used');
            $countDiscountUsed += ($totalUsed > 0) ? 1 : 0;

            $this->processCouponsData(
                $discount->coupon,
                $key,
                $dashboardApps,
                $couponData,
                $countCouponUsed
            );
        }

        $dashboardApps[$key]['countCouponUsed'] = $dashboardApps[$key]['usedCoupons'];
    }

    private function processCouponsData(
        Collection $coupons,
                   $key,
        array &$dashboardApps,
        array &$couponData,
        int &$countCouponUsed
    ): void {
        foreach ($coupons as $coupon) {
            $couponData[] = $coupon;
            $countCouponUsed += $coupon->times_used;

            $dashboardApps[$key]['usedCoupons'] += $coupon->times_used;
            $dashboardApps[$key]['countCoupon']++;
        }
    }

    private function getDiscountsWithCoupons($db): Collection
    {
        try {
            return $this->discountRepository->getAllWithCoupon($db);
        } catch (Exception $e) {
            logger()->error("Can't access to app {$db}: {$e->getMessage()}");
            return collect([]);
        }
    }

    private function getAppNames(): array
    {
        $apps = [];
        $connections = config('database.connections');

        foreach ($connections as $key => $db) {
            if ($key && $key !== 'mysql' && isset($db['app_name'])) {
                $apps[$key] = $db['app_name'];
            }
        }

        return $apps;
    }
}
