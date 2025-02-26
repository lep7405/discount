<?php

namespace App\Http\Controllers;

use App\Http\Requests\CouponByDiscountRequest;
use App\Http\Requests\CouponRequest;
use App\Http\Requests\DecrementRequest;
use App\Http\Requests\UpdateCouponRequest;
use App\Models\Coupon;
use App\Services\Coupon\CouponService;
use App\Services\Discount\DiscountService;
use Exception;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    protected $appName;

    protected $databaseName;

    protected $apps;

    public function __construct()
    {
        $this->routeName = Request()->route()->getPrefix();
        $arr = explode('/', $this->routeName);
        $this->databaseName = $arr[1];
        $this->appName = config('database.connections.'.$this->databaseName.'.app_name');
        Coupon::changeLogName($this->databaseName);
    }
    public function index(CouponService $couponService, Request $request)
    {
        $data = $couponService->index($request->query(), $this->databaseName);

        return view('admin.coupons.index', [
            'appName' => $this->appName,
            'couponData' => $data['couponData'],
            'databaseName' => $this->databaseName,
            'apps' => $this->apps,
            'total_pages_coupon' => $data['total_pages_coupon'],
            'total_items_coupon' => $data['total_items_coupon'],
            'current_pages_coupon' => $data['current_pages_coupon'] ?? 1,
            'per_page_coupon' => $request->query('per_page_coupon') ?? 5,
            'search_coupon' => $request->query('search_coupon') ?? null,
            'status' => $request->query('status') ?? null,
            'time_used' => $request->query('time_used') ?? null,
        ]);
    }
    public function create(DiscountService $discountService)
    {
        $appName = $this->appName;
        $databaseName = $this->databaseName;
        $discountData = $discountService->getAllDiscountForCreateOrUpdateCoupon($databaseName);

        return view('admin.coupons.create', compact(['discountData', 'databaseName', 'appName']));
    }
    public function store(CouponService $couponService, CouponRequest $couponRequest)
    {
        $couponService->create($couponRequest->validationData(), $this->databaseName);

        return redirect()->route('admin.'.$this->databaseName.'.coupons')->with('success', 'Discount created successfully!');
    }
    public function edit(CouponService $couponService, DiscountService $discountService, $id)
    {
        $appName = $this->appName;
        $databaseName = $this->databaseName;
        $couponData = $couponService->getCoupon($id, $databaseName);
        if (! $couponData) {
            return redirect()->back()->with('error','Coupon not found');
        }
        $discountData = $discountService->getAllDiscountForCreateOrUpdateCoupon($databaseName);
        $currentDiscount = $couponData->discount;

        return view('admin.coupons.update', compact(['couponData', 'discountData', 'currentDiscount', 'databaseName', 'appName']));
    }

    public function update(CouponService $couponService, UpdateCouponRequest $couponRequest, $id)
    {

        $couponService->update($couponRequest->validated(), $id, $this->databaseName);

        return redirect()->route('admin.'.$this->databaseName.'.coupons')->with('success', 'Discount updated successfully!');
    }

    public function destroy(CouponService $couponService, $id)
    {
        $couponService->delete($id, $this->databaseName);

        return redirect()->route('admin.'.$this->databaseName.'.coupons')->with('success', 'Discount deleted successfully!');
    }

    public function decrementTimesUsed(CouponService $couponService, DecrementRequest $request, $id)
    {
            $couponService->decrementCoupon($id, $request->validated()['numDecrement'], $this->databaseName);
            return redirect()->back()->with('success', 'Success Decrement Times Used!');
    }

    public function getCreatedByDiscount($discount_id, DiscountService $discountService)
    {
        $appName = $this->appName;
        $databaseName = $this->databaseName;
        $apps = $this->apps;
        $discount = $discountService->getDiscountInfo((int) $discount_id, $databaseName);
        $discount = json_decode(json_encode($discount));
        return view('admin.coupons.discounts.createCoupon', compact(['appName', 'databaseName', 'apps', 'discount']));
    }

    public function createByDiscount(CouponService $couponService, CouponByDiscountRequest $couponRequest, $discount_id)
    {
        $couponNew = $couponService->createByDiscount($couponRequest->validationData(), $discount_id, $this->databaseName);
        return redirect()->route('admin.'.$this->databaseName.'.edit_coupon', $couponNew->id)->with('success', 'Discount created successfully!');
    }
    public function getAllCouponsByDiscount($discount_id, Request $request, CouponService $couponService)
    {
        $data = $couponService->allCouponsByDiscount($discount_id, $this->databaseName, $request->query());
        return view('admin.coupons.discounts.listCoupon', [
            'appName' => $this->appName,
            'couponData' => $data['couponData'],
            'discountData' => $data['discountData'],
            'databaseName' => $this->databaseName,
            'total_pages_coupon' => $data['total_pages_coupon'],
            'total_items_coupon' => $data['total_items_coupon'],
            'current_pages_coupon' => $data['current_pages_coupon'] ?? 1,
            'per_page_coupon' => $request->query('per_page_coupon') ?? 5,
            'search_coupon' => $request->query('search_coupon') ?? null,
            'status' => $request->query('status') ?? null,
            'time_used' => $request->query('time_used') ?? null,
        ]);
    }
}
