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

Route::group(['prefix' => 'auth'], function () {
    Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware('auth')->group(function () {
    Route::group(['prefix' => 'admin'], function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard.index');
        Route::get('me', [AuthController::class, 'me'])->name('admin.user.current');
        Route::post('generate', [DashboardController::class, 'generate'])->name('admin.generate.index');

        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard.index');

        Route::get('generates', [GenerateController::class, 'index'])->name('admin.get_generate');

        Route::get('generates_new', [GenerateController::class, 'showCreate'])->name('admin.get_new_generate');
        Route::post('generates_new', [GenerateController::class, 'create'])->name('admin.post_new_generate');
        Route::get('generates/{id}', [GenerateController::class, 'showUpdate'])->name('admin.get_edit_generate');
        Route::put('generates/{id}', [GenerateController::class, 'update'])->name('admin.post_edit_generate');
        Route::delete('generates/{id}', [GenerateController::class, 'destroy'])->name('admin.destroy_generate');
        Route::put('generates/change_status/{id}', [GenerateController::class, 'changeStatus'])->name('admin.change_status_generate');
        Route::get('/coupon/{generate_id}/{timestamp}/{shop_id}', [GenerateController::class,'generateCoupon'])->name('generate.url.coupon');
    });
});
Route::post('get/{id}', [DiscountController::class, 'getDiscount']);
foreach (config('database.connections') as $key => $db) {
    if ($key && $key !== 'mysql') {
        Route::group(['prefix' => $key], function () use ($key) {
            Route::get('reports', [ReportController::class, 'index'])->name('admin.' . $key . '.reports');
            Route::get('discounts', [DiscountController::class, 'show'])->name('admin.' . $key . '.discounts');
            Route::get('discounts_new', [DiscountController::class, 'showCreate'])->name('admin.' . $key . '.discounts_new');
            Route::post('discounts_new', [DiscountController::class, 'create'])->name('admin.' . $key . '.post_new_discount');
            Route::get('discounts/{id}', [DiscountController::class, 'showUpdate'])->name('admin.' . $key . '.get_edit_discount');
            Route::post('discounts/{id}', [DiscountController::class, 'update'])->name('admin.' . $key . '.update_discount');
            Route::delete('discounts/{id}', [DiscountController::class, 'destroy'])->name('admin.' . $key . '.delete_discount');
            Route::post('discount_ajax/{id}', [DiscountController::class, 'getDiscount'])->name('admin.' . $key . '.ajaxGetDiscount');
            Route::get('all_discounts', [DiscountController::class, 'getAllDiscounts']);

            Route::get('coupons', [CouponController::class, 'show'])->name('admin.' . $key . '.coupons');
            Route::get('coupons_new', [CouponController::class, 'showCreate'])->name('admin.' . $key . '.coupons_new');
            Route::post('coupons_new', [CouponController::class, 'create'])->name('admin.' . $key . '.post_new_coupon');
            Route::get('coupons/{id}', [CouponController::class, 'showUpdate'])->name('admin.' . $key . '.get_edit_coupon');
            Route::post('coupons/{id}', [CouponController::class, 'update'])->name('admin.' . $key . '.edit_coupon');
            Route::delete('coupons/{id}', [CouponController::class, 'destroy'])->name('admin.' . $key . '.delete_coupon');
            Route::put('decrement_times_used_coupon/{id}', [CouponController::class, 'decrementTimesUsed'])->name('admin.' . $key . '.decrement_times_used_coupon');

            Route::get('created_coupons/{discount_id}', [CouponController::class, 'getCreatedByDiscount'])->name('admin.' . $key . '.show_create_coupon_in_discount');
            Route::post('created_coupons/{discount_id}', [CouponController::class, 'createByDiscount'])->name('admin.' . $key . '.create_coupon_in_discount');
            Route::get('list_coupons/{discount_id}', [CouponController::class, 'getAllCouponsByDiscount'])->name('admin.' . $key . '.show_all_coupon_in_discount');

        });
    }
}
