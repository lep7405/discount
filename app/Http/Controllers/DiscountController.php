<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiscountRequest;
use App\Models\Discount;
use App\Services\Discount\DiscountService;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    protected $databaseName;

    protected $appName;

    protected $apps;

    public function __construct(protected DiscountService $discountService)
    {
        $this->routeName = Request()->route()->getPrefix();
        $uri = Request()->route()->uri();
        $arr = explode('/', $uri);
        $this->databaseName = $arr[1];
        $this->appName = config('database.connections.' . $this->databaseName . '.app_name');
        Discount::changeLogName($this->databaseName);
    }

    public function index(Request $request)
    {
        $appName = $this->appName;
        $databaseName = $this->databaseName;
        $data = $this->discountService->index($request->query(), $databaseName);

        return view('admin.discounts.index', [
            'appName' => $appName,
            'discountData' => $data['discountData'],
            'databaseName' => $databaseName,
            'total_pages_discount' => $data['total_pages_discount'],
            'total_items_discount' => $data['total_items_discount'],
            'total_items' => $data['total_items'],
            'current_pages_discount' => $data['current_pages_discount'] ?? 1,
            'per_page_discount' => $request->query('per_page_discount') ?? 5,
            'search_discount' => $request->query('search_discount') ?? null,
            'started_at' => $request->query('started_at') ?? null,
        ]);
    }

    public function create()
    {
        $appName = $this->appName;
        $databaseName = $this->databaseName;

        return view('admin.discounts.create', compact('appName', 'databaseName'));
    }

    public function store(DiscountRequest $request)
    {
        $this->discountService->store($request->validationData(), $this->databaseName);

        return redirect()->route('admin.' . $this->databaseName . '.discounts')->with('success', 'Discount created successfully!');
    }

    public function edit($id)
    {
        $databaseName = $this->databaseName;
        $discount = $this->discountService->getDiscountWithCoupon($id, $databaseName);
        $status = $this->discountService->getStatusDiscount($discount);
        //        dd(json_encode($status, JSON_PRETTY_PRINT));

        return view('admin.discounts.update', [
            'appName' => $this->appName,
            'databaseName' => $this->databaseName,
            'discountStatus' => $status,
            'discountData' => $discount,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->discountService->update($id, $request->all(), $this->databaseName);

        return redirect()->back()->with('success', 'Discount updated successfully!');
    }

    public function destroy($id)
    {
        $this->discountService->delete($id, $this->databaseName);

        return redirect()->route('admin.' . $this->databaseName . '.discounts')->with('success', 'Discount deleted successfully!');
    }

    public function getDiscountInfo(Request $request, DiscountService $discountService, $id)
    {
        $data = $discountService->getDiscountInfo($id, $this->databaseName);

        return view('components.discountInfo', compact('data'))->render();
    }

    public function test4()
    {
        dd(1);
    }
}
