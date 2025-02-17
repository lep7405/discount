<?php

namespace App\Http\Controllers;

use App\Services\Report\ReportService;
use Illuminate\Http\Request;

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
        foreach ($config as $key => $db) {
            if ($key && $key != 'mysql' && isset($db['app_name'])) {
                $apps[$key] = $db['app_name'];
            }
        }

        $data = $reportService->index($request->query(), $databaseName);

        $discounts = $data['discountData'];
        $coupons = $data['couponData'];

        $count_discount = $data['couponData'];
        $count_discount_used = $data['count_discount_used'];
        $count_coupon = $data['count_coupon'];
        $count_coupon_used = $data['count_coupon_used'];

        $total_pages_discount = $data['total_pages_discount'];
        $total_items_discount = $data['total_items_discount'];
        $current_pages_discount = $data['current_pages_discount'] ?? 1;
        $per_page_discount = $data['per_page_discount'] ?? 5;
        $search_discount = $request->query('search_discount');
        $started_at = $request->query('started_at');

        $total_pages_coupon = $data['total_pages_coupon'];
        $total_items_coupon = $data['total_items_coupon'];
        $current_pages_coupon = $data['current_pages_coupon'] ?? 1;
        $per_page_coupon = $data['per_page_coupon'] ?? 5;
        $search_coupon = $request->query('search_coupon');
        $status = $request->query('status') ?? null;
        $time_used = $request->query('time_used') ?? null;

        return view('admin.reports.index', compact([
            'appName',
            'databaseName',
            'discounts',
            'coupons',
            'count_discount',
            'count_coupon',
            'count_discount_used',
            'count_coupon_used',
            'total_pages_discount',
            'total_items_discount',
            'current_pages_discount',
            'per_page_discount',
            'search_discount',
            'started_at',
            'total_pages_coupon',
            'total_items_coupon',
            'current_pages_coupon',
            'per_page_coupon',
            'search_coupon',
            'status',
            'time_used',
            'apps',
        ]));

    }
}
