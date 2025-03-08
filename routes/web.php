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
//Route::fallback(function () {
//        return view('errors.404');
//});

Route::group([], function () {
    Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});
Route::get('/check-auth', function () {
    if (auth()->check()) {
        return "Đã đăng nhập";
    } else {
        return "Chưa đăng nhập, đáng lẽ phải redirect sang login";
    }
})->middleware('auth');
Route::get('coupon/{generate_id}/{timestamp}/{shop_id}', [GenerateController::class, 'generateCoupon'])->name('generate.url.coupon');

Route::middleware('auth')->group(function () {
    Route::group(['prefix' => 'admin'], function () {

        Route::get('me', [AuthController::class, 'me'])->name('admin.user.current');
        Route::post('changePassWord', [AuthController::class, 'changePassword'])->name('admin.user.changePassword');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard.index');
        Route::post('generate', [DashboardController::class, 'generate'])->name('admin.generate.index');

        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard.index');

        Route::get('generates', [GenerateController::class, 'index'])->name('admin.get_generate');

        Route::get('generates_new', [GenerateController::class, 'showCreate'])->name('admin.get_new_generate');
        Route::post('generates_new', [GenerateController::class, 'create'])->name('admin.post_new_generate');
        Route::get('generates/{id}', [GenerateController::class, 'showUpdate'])->name('admin.get_edit_generate');
        Route::put('generates/{id}', [GenerateController::class, 'update'])->name('admin.post_edit_generate');
        Route::delete('generates/{id}', [GenerateController::class, 'destroy'])->name('admin.destroy_generate');
        Route::put('generates/change_status/{id}', [GenerateController::class, 'changeStatus'])->name('admin.change_status_generate');

        foreach (config('database.connections') as $key => $db) {
            if ($key && ! in_array($key, ['mysql', 'mariadb', 'pgsql', 'sqlsrv', 'sqlite'])) {
                Route::group(['prefix' => $key], function () use ($key) {
                    Route::get('reports', [ReportController::class, 'index'])->name('admin.' . $key . '.reports');

                    Route::get('discounts', [DiscountController::class, 'index'])->name('admin.' . $key . '.discounts');
                    Route::get('discounts/create', [DiscountController::class, 'create'])->name('admin.' . $key . '.create_discount');
                    Route::post('discounts', [DiscountController::class, 'store'])->name('admin.' . $key . '.store_discount');
                    Route::get('discounts/{id}/edit', [DiscountController::class, 'edit'])->name('admin.' . $key . '.edit_discount');
                    Route::put('discounts/{id}', [DiscountController::class, 'update'])->name('admin.' . $key . '.update_discount');
                    Route::delete('discounts/{id}', [DiscountController::class, 'destroy'])->name('admin.' . $key . '.destroy_discount');
                    Route::post('discounts/{id}', [DiscountController::class, 'getDiscountInfo'])->name('admin.' . $key . '.discountInfo');

                    Route::get('all_discounts', [DiscountController::class, 'getAllDiscounts']);

                    Route::get('coupons', [CouponController::class, 'index'])->name('admin.' . $key . '.coupons');
                    Route::get('coupons/create', [CouponController::class, 'create'])->name('admin.' . $key . '.create_coupon');
                    Route::post('coupons', [CouponController::class, 'store'])->name('admin.' . $key . '.store_coupon');
                    Route::get('coupons/{id}/edit', [CouponController::class, 'edit'])->name('admin.' . $key . '.edit_coupon');
                    Route::put('coupons/{id}', [CouponController::class, 'update'])->name('admin.' . $key . '.update_coupon');
                    Route::delete('coupons/{id}', [CouponController::class, 'destroy'])->name('admin.' . $key . '.destroy_coupon');

                    Route::put('decrement_times_used_coupon/{id}', [CouponController::class, 'decrementTimesUsed'])->name('admin.' . $key . '.decrement_times_used_coupon');

                    Route::get('created_coupons/{discount_id}', [CouponController::class, 'getCreatedByDiscount'])->name('admin.' . $key . '.show_create_coupon_in_discount');
                    Route::post('created_coupons/{discount_id}', [CouponController::class, 'createByDiscount'])->name('admin.' . $key . '.create_coupon_in_discount');
                    Route::get('list_coupons/{discount_id}', [CouponController::class, 'getAllCouponsByDiscount'])->name('admin.' . $key . '.show_all_coupon_in_discount');
                });
            }
        }
    });
});
