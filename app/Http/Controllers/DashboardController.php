<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Discount;
use Exception;

class DashboardController extends Controller
{
    protected $configDatabase;

    public function __construct()
    {

        $config_discount_manager = config('database');
        $databases = [];
        foreach ($config_discount_manager['connections'] as $key => $config) {
            if ($key && $key != 'mysql' && $key != 'sqlite' && $key != 'mariadb' && $key != 'pgsql' && $key != 'sqlsrv') {
                $databases[] = $key;
            }
        }
        $this->configDatabase = $databases;
    }

    public function index()
    {
        $databases = $this->configDatabase;
        $discountData = [];
        $couponData = [];
        $count_discount_used = 0;
        $count_coupon_used = 0;
        $dashboard_apps = [];

        foreach ($databases as $key => $db) {
            // Khởi tạo giá trị mặc định
            $dashboard_apps[$key] = [
                'db' => $db,
                'app_name' => config('database.connections.' . $db . '.app_name'),
                'count_discount' => 0,
                'count_coupon' => 0,
                'used_coupons' => 0,
                'count_coupon_used' => 0,
            ];

            // Lấy discount và coupon liên quan trong một lần
            $discounts = $this->getDiscountsWithCoupons($db);

            // Nếu có dữ liệu discount
            if (! empty($discounts)) {
                foreach ($discounts as $d) {
                    $discountData[] = $d;
                    $dashboard_apps[$key]['count_discount'] = count($discounts);

                    // Tính tổng times_used từ coupons
                    $totalUsed = collect($d->coupon)->sum('times_used');
                    $count_discount_used += ($totalUsed > 0) ? 1 : 0;

                    foreach ($d->coupon as $c) {
                        $couponData[] = $c;
                        $count_coupon_used += $c->times_used;

                        // Cập nhật vào dashboard_apps
                        $dashboard_apps[$key]['used_coupons'] += $c->times_used;
                        $dashboard_apps[$key]['count_coupon'] += 1;
                        $dashboard_apps[$key]['count_coupon_used'] = $count_coupon_used;
                    }
                }
            }
        }

        $apps = $this->getAppNames();

        return view('admin.dashboard', compact(['discountData', 'couponData', 'count_discount_used', 'count_coupon_used', 'apps', 'dashboard_apps']));
    }

    // Lấy discounts với coupons
    private function getDiscountsWithCoupons($db)
    {
        try {
            return Discount::on($db)->with('coupon')->get();
        } catch (Exception $e) {
            logger()->error("Can't access to app {$db} {$e->getMessage()}");

            return collect([]);
        }
    }

    // Lấy tên các ứng dụng
    private function getAppNames()
    {
        $apps = [];
        $config = config('database.connections');
        foreach ($config as $key => $db) {
            if ($key && $key != 'mysql' && isset($db['app_name'])) {
                $apps[$key] = $db['app_name'];
            }
        }

        return $apps;
    }
}
