<?php

namespace App\Http\Controllers;

use App\Services\DashBoard\DashBoardService;

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

    public function index(DashBoardService $dashBoardService)
    {
        $data = $dashBoardService->index($this->configDatabase);

        return view('admin.dashboard',
            [
                'countDiscountData' => count($data['discountData']),
                'countCouponData' => count($data['couponData']),
                'countDiscountUsed' => $data['countDiscountUsed'],
                'countCouponUsed' => $data['countCouponUsed'],
                'apps' => $data['apps'],
                'dashboardApps' => $data['dashboardApps'],
            ]);
    }

}
