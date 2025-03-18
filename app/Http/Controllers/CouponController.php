<?php

namespace App\Http\Controllers;

use App\Http\Requests\CouponByDiscountRequest;
use App\Http\Requests\CreateCouponRequest;
use App\Http\Requests\DecrementRequest;
use App\Http\Requests\UpdateCouponRequest;
use App\Models\Coupon;
use App\Services\Coupon\CouponService;
use App\Services\Discount\DiscountService;
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
        $this->appName = config('database.connections.' . $this->databaseName . '.app_name');
        Coupon::changeLogName($this->databaseName);
    }

    public function index(CouponService $couponService, Request $request)
    {
        $data = $couponService->index($this->databaseName, $request->query());

        return view('admin.coupons.index', [
            'appName' => $this->appName,
            'couponData' => $data['couponData'],
            'databaseName' => $this->databaseName,
            'apps' => $this->apps,
            'totalPagesCoupon' => $data['totalPagesCoupon'],
            'totalItemsCoupon' => $data['totalItemsCoupon'],
            'totalCoupons' => $data['totalCoupons'],
            'currentPagesCoupon' => $data['currentPagesCoupon'] ?? 1,
            'perPageCoupon' => $request->query('perPageCoupon') ?? 5,
            'searchCoupon' => $request->query('searchCoupon') ?? null,
            'status' => $request->query('status') ?? null,
            'timeUsed' => $request->query('timeUsed') ?? null,
        ]);
    }

    public function create(DiscountService $discountService)
    {
        $appName = $this->appName;
        $databaseName = $this->databaseName;
        $discountData = $discountService->getAllDiscountIdAndName($databaseName);

        return view('admin.coupons.create', compact(['discountData', 'databaseName', 'appName']));
    }

    public function store(CouponService $couponService, CreateCouponRequest $couponRequest)
    {
        $couponService->store($this->databaseName,$couponRequest->validationData());

        return redirect()->route('admin.' . $this->databaseName . '.coupons')->with('success', 'Discount created successfully!');
    }

    public function edit(CouponService $couponService, DiscountService $discountService, int $id)
    {
        $appName = $this->appName;
        $databaseName = $this->databaseName;
        $couponData = $couponService->getCouponById($id, $databaseName);
        $discountData = $discountService->getAllDiscountIdAndName($databaseName);
        $currentDiscount = $couponData->discount;

        return view('admin.coupons.update', compact(['couponData', 'discountData', 'currentDiscount', 'databaseName', 'appName']));
    }

    public function update(CouponService $couponService, UpdateCouponRequest $couponRequest, $id)
    {

        $couponService->update($id, $this->databaseName , $couponRequest->validated());

        return redirect()->route('admin.' . $this->databaseName . '.coupons')->with('success', 'Discount updated successfully!');
    }

    public function destroy(CouponService $couponService, $id)
    {
        $couponService->delete($id, $this->databaseName);

        return redirect()->route('admin.' . $this->databaseName . '.coupons')->with('success', 'Discount deleted successfully!');
    }

    public function decrementTimesUsed(CouponService $couponService, DecrementRequest $request, $id)
    {
        $couponService->decrementTimesUsedCoupon($id,  $this->databaseName, $request->validationData()['numDecrement']);

        return redirect()->back()->with('success', 'Success Decrement Times Used!');
    }

    public function createByDiscount(int $discount_id, DiscountService $discountService)
    {

        $appName = $this->appName;
        $databaseName = $this->databaseName;
        $apps = $this->apps;
        $discount = $discountService->getDiscountInfo( $discount_id, $databaseName);
        return view('admin.coupons.discounts.createCoupon', compact(['appName', 'databaseName', 'apps', 'discount']));
    }

    public function storeByDiscount(int $discount_id, CouponService $couponService, CouponByDiscountRequest $couponRequest)
    {
        $couponNew = $couponService->createCouponByDiscount( $discount_id, $this->databaseName,$couponRequest->validationData());

        return redirect()->route('admin.' . $this->databaseName . '.editCoupon', $couponNew->id)->with('success', 'Discount created successfully!');
    }

    public function getAllCouponsByDiscount($discount_id, Request $request, CouponService $couponService)
    {
        $data = $couponService->getAllCouponsByDiscount($discount_id, $this->databaseName, $request->query());

        return view('admin.coupons.discounts.listCoupon', [
            'appName' => $this->appName,
            'couponData' => $data['couponData'],
            'discountData' => $data['discountData'],
            'databaseName' => $this->databaseName,
            'apps' => $this->apps,
            'totalPagesCoupon' => $data['totalPagesCoupon'],
            'totalItemsCoupon' => $data['totalItemsCoupon'],
            'currentPagesCoupon' => $data['currentPagesCoupon'] ?? 1,
            'perPageCoupon' => $request->query('perPageCoupon') ?? 5,
            'searchCoupon' => $request->query('searchCoupon') ?? null,
            'status' => $request->query('status') ?? null,
            'timeUsed' => $request->query('timeUsed') ?? null,
        ]);
    }
}
