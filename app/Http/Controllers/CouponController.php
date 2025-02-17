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

    /**
     * Create a new controller instance.
     * Get appNam and databaseName from Route Group Prefix
     *
     * @return void
     */
    public function __construct()
    {
        $this->routeName = Request()->route()->getPrefix();
        $arr = explode('/', $this->routeName);
        $this->databaseName = $arr[1];
        $this->appName = config('database.connections.' . $this->databaseName . '.app_name');
        Coupon::changeLogName($this->databaseName);
    }

    public function create(CouponService $couponService, CouponRequest $couponRequest)
    {
        $coupon = $couponService->create($couponRequest->validated(), $this->databaseName);

        return redirect()->route('admin.' . $this->databaseName . '.coupons')->with('success', 'Discount created successfully!');
    }

    public function show(CouponService $couponService, Request $request)
    {
        $appName = $this->appName;
        $databaseName = $this->databaseName;
        $apps = $this->apps;

        $data = $couponService->index($request->query(), $databaseName);

        $couponData = $data['couponData'];

        $total_pages_coupon = $data['total_pages_coupon'];
        $total_items_coupon = $data['total_items_coupon'];
        $current_pages_coupon = $data['current_pages_coupon'] ?? 1;
        $per_page_coupon = $request->query('per_page_coupon') ?? 5;
        $search_coupon = $request->query('search_coupon') ?? null;
        $status = $request->query('status') ?? null;
        $time_used = $request->query('time_used') ?? null;

        return view('admin.coupons.index', compact(['appName', 'couponData', 'databaseName', 'apps', 'total_pages_coupon', 'total_items_coupon', 'current_pages_coupon', 'per_page_coupon', 'search_coupon', 'status', 'time_used']));
    }

    public function update(CouponService $couponService, UpdateCouponRequest $couponRequest, $id)
    {
        $couponService->update($couponRequest->validated(), $id, $this->databaseName);

        return redirect()->route('admin.' . $this->databaseName . '.coupons')->with('success', 'Discount updated successfully!');
    }

    public function destroy(CouponService $couponService, $id)
    {
        $couponService->delete($id, $this->databaseName);

        return redirect()->route('admin.' . $this->databaseName . '.coupons')->with('success', 'Discount deleted successfully!');
    }

    public function showCreate(DiscountService $discountService)
    {
        $appName = $this->appName;
        $databaseName = $this->databaseName;
        $discountData = $discountService->getAllDiscounts(-1, null, $databaseName);

        return view('admin.coupons.create', compact(['discountData', 'databaseName', 'appName']));
    }

    public function showUpdate(CouponService $couponService, DiscountService $discountService, $id)
    {
        $appName = $this->appName;
        $databaseName = $this->databaseName;
        $couponData = $couponService->getCoupon($id, $databaseName);
        $discountData = $discountService->getAllDiscounts(-1, null, $databaseName);
        $currentDiscount = $couponData->discount;

        return view('admin.coupons.edit', compact(['couponData', 'discountData', 'currentDiscount', 'databaseName', 'appName']));
    }

    public function decrementTimesUsed(CouponService $couponService, DecrementRequest $request, $id)
    {
        try {
            $couponService->decrementCoupon($id, $request->validated()['numDecrement'], $this->databaseName);

            return redirect()->back()->with('message', 'Success Decrement Times Used!');
        } catch (Exception $e) {
            return redirect()->back()->with($e->getMessage());

        }
    }

    public function getCreatedByDiscount($discount_id, DiscountService $discountService)
    {
        $appName = $this->appName;
        $databaseName = $this->databaseName;
        $apps = $this->apps;
        $discount = $discountService->getDiscountNoCoupon((int) $discount_id, $databaseName);

        return view('admin.coupons.discounts.createCoupon', compact(['appName', 'databaseName', 'apps', 'discount']));
    }

    public function createByDiscount(CouponService $couponService, CouponByDiscountRequest $couponRequest, $discount_id)
    {
        $couponService->createByDiscount($couponRequest->validated(), $discount_id, $this->databaseName);

        return redirect()->route('admin.' . $this->databaseName . '.coupons')->with('success', 'Discount created successfully!');
    }

    public function getAllCouponsByDiscount($discount_id, Request $request, CouponService $couponService)
    {
        //        $databaseName = $this->databaseName;
        //        $couponService->allCouponsByDiscount($discount_id, $databaseName,$request->query());
        //        return response()->json([
        //            'data' => $couponData,
        //        ]);
    }
}
