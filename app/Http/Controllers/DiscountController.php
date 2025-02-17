<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiscountRequest;
use App\Http\Resources\DiscountResource;
use App\Models\Discount;
use App\Services\Discount\DiscountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    protected $databaseName;
    protected $appName;
    protected $apps;

    public function __construct(protected DiscountService $discountService)
    {
        $this->routeName = Request()->route()->getPrefix();
        $arr = explode('/', $this->routeName);
        $this->databaseName = $arr[1];
        $this->appName = config('database.connections.' . $this->databaseName . '.app_name');
        Discount::changeLogName($this->databaseName);
    }

    public function create(DiscountRequest $request)
    {
        $discount = $this->discountService->store($request->validationData(), $this->databaseName);

        //        return new DiscountResource($discount);
        //dùng cho blade
        return redirect()->route('admin.' . $this->databaseName . '.discounts')->with('success', 'Discount created successfully!');
    }

    public function show(Request $request)
    {

        $appName = $this->appName;
        $databaseName = $this->databaseName;
        $data = $this->discountService->getAllDiscounts($request->query(), $databaseName);
        $discountData = $data['discountData'];
        $total_pages_discount = $data['total_pages_discount'];
        $total_items_discount = $data['total_items_discount'];
        $current_pages_discount = $data['current_pages_discount'] ?? 1;
        $per_page_discount = $request->query('per_page_discount') ?? 5;
        $search_discount = $request->query('search_discount') ?? null;
        $started_at = $request->query('started_at');

        return view('admin.discounts.index', compact(['appName', 'discountData', 'databaseName', 'total_pages_discount', 'total_items_discount', 'current_pages_discount', 'per_page_discount', 'search_discount', 'started_at']));
    }

    public function update(Request $request, $id)
    {
        $this->discountService->edit($id, $request->all(), $this->databaseName);

        //blade
        return redirect()->back()->with('success', 'Discount updated successfully!');
    }

    public function destroy($id)
    {
        $this->discountService->destroy($id, $this->databaseName);

        return responseWithSuccess();
        //blade
        //return redirect()->route('admin.'.$this->databaseName.'.discounts')->with('success', 'Discount deleted successfully!');
    }

    public function showCreate()
    {
        $appName = $this->appName;
        $databaseName = $this->databaseName;

        return view('admin.discounts.create', compact('appName', 'databaseName'));
    }

    public function showUpdate($id)
    {

        $appName = $this->appName;
        $databaseName = $this->databaseName;
        $data = $this->discountService->findDiscountById($id, $databaseName)['discount'];
        $discount_status = $this->discountService->findDiscountById($id, $databaseName)['discount_status'];

        return view('admin.discounts.update', compact('appName', 'databaseName', 'data', 'discount_status'));
    }

    public function getDiscount(DiscountService $discountService, $id): JsonResponse
    {
        $discount = $discountService->getDiscountNoCoupon($id, $this->databaseName);

        $filteredData = array_intersect_key((new DiscountResource($discount))->toArray(request()), array_flip([
            'id', 'name', 'started_at', 'expired_at', 'type', 'value', 'usage_limit', 'trial_days',
        ]));

        return response()->json($filteredData);
    }
}
