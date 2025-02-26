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
        $this->appName = config('database.connections.'.$this->databaseName.'.app_name');
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

        return view('admin.reports.index',
            [
                'appName' => $appName,
                'count_discount' => $data['count_discount'],
                'count_coupon' => $data['count_coupon'],
                'count_coupon_used' => $data['count_coupon_used'],
                'count_discount_used' => $data['count_discount_used'],

                'discountData' => Arr::get($data, 'discountData'),
                'databaseName' => $databaseName,
                'total_pages_discount' => $data['total_pages_discount'],
                'total_items_discount' => $data['total_items_discount'],
                'current_pages_discount' => $data['current_pages_discount'] ?? 1,
                'per_page_discount' => $request->query('per_page_discount') ?? 5,
                'search_discount' => $request->query('search_discount') ?? null,
                'started_at' => $request->query('started_at'),

                'couponData' => $data['couponData'],
                'total_pages_coupon' => $data['total_pages_coupon'],
                'total_items_coupon' => $data['total_items_coupon'],
                'current_pages_coupon' => $data['current_pages_coupon'] ?? 1,
                'per_page_coupon' => $request->query('per_page_coupon') ?? 5,
                'search_coupon' => $request->query('search_coupon') ?? null,
                'status' => $request->query('status') ?? null,
                'time_used' => $request->query('time_used') ?? null,
            ]
        );

    }
}
