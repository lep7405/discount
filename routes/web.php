<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\GenerateController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth()->check()) {
        return redirect()->route('admin.dashboard.index');
    }

    return redirect()->route('login');
});
Route::fallback(function(){
    return view('errors.404');
});
Route::group([], function () {
    Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});
Route::get('check-auth', function () {
    if (auth()->check()) {
        return 'Đã đăng nhập';
    } else {
        return 'Chưa đăng nhập, đáng lẽ phải redirect sang login';
    }
})->middleware('auth');
Route::get('coupon/{generateId}/{timeStamp}/{shopId}', [GenerateController::class, 'generateCoupon'])->name('generate.url.coupon');

Route::middleware('auth')->group(function () {
    Route::group(['prefix' => 'admin'], function () {

        Route::get('me', [AuthController::class, 'me'])->name('admin.user.current');
        Route::post('changePassWord', [AuthController::class, 'changePassword'])->name('admin.user.changePassword');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        //        Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard.index');
        //        Route::post('generate', [DashboardController::class, 'generate'])->name('admin.generate.index');

        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard.index');

        Route::get('generates', [GenerateController::class, 'index'])->name('admin.indexGenerate');
        Route::get('generates/create', [GenerateController::class, 'create'])->name('admin.createGenerate');
        Route::post('generates', [GenerateController::class, 'store'])->name('admin.storeGenerate');
        Route::get('generates/{id}', [GenerateController::class, 'edit'])->name('admin.editGenerate');
        Route::put('generates/{id}', [GenerateController::class, 'update'])->name('admin.updateGenerate');
        Route::delete('generates/{id}', [GenerateController::class, 'destroy'])->name('admin.destroyGenerate');
        Route::put('generates/changeStatus/{id}', [GenerateController::class, 'changeStatus'])->name('admin.changeStatusGenerate');

        foreach (config('database.connections') as $key => $db) {
            if ($key && ! in_array($key, ['mysql', 'mariadb', 'pgsql', 'sqlsrv', 'sqlite'])) {
                Route::group(['prefix' => $key], function () use ($key) {
                    Route::get('reports', [ReportController::class, 'index'])->name('admin.' . $key . '.reports');

                    Route::get('discounts', [DiscountController::class, 'index'])->name('admin.' . $key . '.discounts');
                    Route::get('discounts/create', [DiscountController::class, 'create'])->name('admin.' . $key . '.createDiscount');
                    Route::post('discounts', [DiscountController::class, 'store'])->name('admin.' . $key . '.storeDiscount');
                    Route::get('discounts/{id}/edit', [DiscountController::class, 'edit'])->name('admin.' . $key . '.editDiscount');
                    Route::put('discounts/{id}', [DiscountController::class, 'update'])->name('admin.' . $key . '.updateDiscount');
                    Route::delete('discounts/{id}', [DiscountController::class, 'destroy'])->name('admin.' . $key . '.destroyDiscount');
                    Route::post('discounts/{id}', [DiscountController::class, 'getDiscountInfo'])->name('admin.' . $key . '.discountInfo');

                    Route::get('all_discounts', [DiscountController::class, 'getAllDiscounts']);

                    Route::get('coupons', [CouponController::class, 'index'])->name('admin.' . $key . '.coupons');
                    Route::get('coupons/create', [CouponController::class, 'create'])->name('admin.' . $key . '.createCoupon');
                    Route::post('coupons', [CouponController::class, 'store'])->name('admin.' . $key . '.storeCoupon');
                    Route::get('coupons/{id}/edit', [CouponController::class, 'edit'])->name('admin.' . $key . '.editCoupon')->where('id', '[0-9]+');
                    Route::put('coupons/{id}', [CouponController::class, 'update'])->name('admin.' . $key . '.updateCoupon');
                    Route::delete('coupons/{id}', [CouponController::class, 'destroy'])->name('admin.' . $key . '.destroyCoupon');

                    Route::put('decrementTimesUsedCoupon/{id}', [CouponController::class, 'decrementTimesUsed'])->name('admin.' . $key . '.decrementTimesUsedCoupon');

                    Route::get('createdCoupons/{discountId}', [CouponController::class, 'createByDiscount'])->name('admin.' . $key . '.createCouponInDiscount');
                    Route::post('createdCoupons/{discountId}', [CouponController::class, 'storeByDiscount'])->name('admin.' . $key . '.storeCouponInDiscount');
                    Route::get('listCoupons/{discountId}', [CouponController::class, 'getAllCouponsByDiscount'])->name('admin.' . $key . '.allCouponInDiscount')->where('discountId', '[0-9]+');
                });
            }
        }
    });
});
