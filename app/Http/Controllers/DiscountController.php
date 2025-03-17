<?php

namespace App\Http\Controllers;

use App\Exceptions\NotFoundException;
use App\Http\Requests\CreateDiscountRequest;
use App\Models\Discount;
use App\Services\Discount\DiscountService;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    protected string $databaseName;
    protected string $appName;

    public function __construct(protected DiscountService $discountService)
    {
        $uri = Request()->route()->uri();

        $segments = explode('/', $uri);

        $this->databaseName = $segments[1];

        $this->appName = config('database.connections.' . $this->databaseName . '.app_name');

        Discount::changeLogName($this->databaseName);
    }

    public function index(Request $request)
    {
        $appName = $this->appName;

        $databaseName = $this->databaseName;

        $data = $this->discountService->index($databaseName,$request->query());

        return view('admin.discounts.index', [
            'appName' => $appName,
            'discountData' => $data['discountData'],
            'databaseName' => $databaseName,
            'totalPagesDiscount' => $data['totalPagesDiscount'],
            'totalItemsDiscount' => $data['totalItemsDiscount'],
            'totalItems' => $data['totalItems'],
            'currentPagesDiscount' => $data['currentPagesDiscount'] ?? 1,
            'perPageDiscount' => $request->query('perPageDiscount') ?? 5,
            'searchDiscount' => $request->query('searchDiscount') ?? null,
            'startedAt' => $request->query('startedAt') ?? null,
        ]);
    }

    public function create()
    {
        $appName = $this->appName;

        $databaseName = $this->databaseName;

        return view('admin.discounts.create', compact('appName', 'databaseName'));
    }

    public function store(CreateDiscountRequest $request)
    {
        $this->discountService->store($this->databaseName,$request->validationData());

        return redirect()->route('admin.' . $this->databaseName . '.discounts')->with('success', 'Discount created successfully!');
    }

    public function edit($id)
    {
        $databaseName = $this->databaseName;

        $discount = $this->discountService->getDiscountWithCoupon($id, $databaseName);

        $hasCouponUsed = $this->discountService->hasCouponUsed($discount);

        return view('admin.discounts.update', [
            'appName' => $this->appName,
            'databaseName' => $this->databaseName,
            'discountStatus' => $hasCouponUsed,
            'discountData' => $discount,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->discountService->update($id,  $this->databaseName ,$request->all());

        return redirect()->back()->with('success', 'Discount updated successfully!');
    }

    public function destroy($id)
    {
        $this->discountService->delete($id, $this->databaseName);

        return redirect()->route('admin.' . $this->databaseName . '.discounts')->with('success', 'Discount deleted successfully!');
    }

    public function getDiscountInfo(Request $request, $id)
    {
        try {
            $data = $this->discountService->getDiscountInfo($id, $this->databaseName);
            return view('components.discountInfo', compact('data'))->render();
        } catch (\Exception $e) {
            \Log::error('Error in getDiscountInfo: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }
}
