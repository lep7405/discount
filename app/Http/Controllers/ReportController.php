<?php

namespace App\Http\Controllers;

use App\Services\Report\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ReportController extends Controller
{
    protected $appName;

    protected $databaseName;

    public function __construct()
    {
        $this->routeName = Request()->route()->getName();

        $arr = explode('.', $this->routeName);

        $this->databaseName = $arr[1];

        $this->appName = config('database.connections.' . $this->databaseName . '.app_name');
    }

    public function index(ReportService $reportService, Request $request)
    {
        $appName = $this->appName;

        $databaseName = $this->databaseName;

        $config = config('database.connections');

        $apps = [];

        $databases = ['mysql', 'mariadb', 'pgsql', 'sqlsrv', 'sqlite'];

        foreach ($config as $key => $db) {

            if ($key && ! in_array($key, $databases) && isset($db['app_name'])) {

                $apps[$key] = $db['app_name'];
            }
        }
        $data = $reportService->index($request->query(), $databaseName);

        return view('admin.reports.index', [
            'appName' => $appName,
            'countDiscount' => $data['countDiscount'],
            'countCoupon' => $data['countCoupon'],
            'countCouponUsed' => $data['countCouponUsed'],
            'countDiscountUsed' => $data['countDiscountUsed'],

            'discountData' => Arr::get($data, 'discountData'),
            'databaseName' => $databaseName,
            'totalPagesDiscount' => $data['totalPagesDiscount'],
            'totalItemsDiscount' => $data['totalItemsDiscount'],
            'totalDiscounts' => $data['totalDiscounts'],
            'currentPagesDiscount' => $data['currentPagesDiscount'] ?? 1,
            'perPageDiscount' => $request->query('perPageDiscount') ?? 5,
            'searchDiscount' => $request->query('searchDiscount') ?? null,
            'startedAt' => $request->query('startedAt'),

            'couponData' => $data['couponData'],
            'totalPagesCoupon' => $data['totalPagesCoupon'],
            'totalItemsCoupon' => $data['totalItemsCoupon'],
            'totalCoupons' => $data['totalCoupons'],
            'currentPagesCoupon' => $data['currentPagesCoupon'] ?? 1,
            'perPageCoupon' => $request->query('perPageCoupon') ?? 5,
            'searchCoupon' => $request->query('searchCoupon') ?? null,
            'status' => $request->query('status') ?? null,
            'timeUsed' => $request->query('time_used') ?? null,
        ]);

    }
}
