<?php

//
//use App\Http\Controllers\AuthController;
//use App\Http\Controllers\DiscountController;
//use App\Http\Controllers\ReportController;
//use App\Http\Controllers\TestController;
//use Illuminate\Support\Facades\Route;
//
//foreach (config('database.connections') as $key => $db) {
//    if ($key && $key !== 'mysql') {
//        Route::group(['prefix' => $key], function () use ($key) {
//            Route::get('reports', [ReportController::class, 'index'])->name('admin.' . $key . '.reports');
//            Route::get('discounts', [DiscountController::class, 'index'])->name('admin.' . $key . '.discounts');
//            Route::get('discounts_new', [DiscountController::class, 'create'])->name('admin.' . $key . '.discounts_new');
//            Route::post('discounts_new', [DiscountController::class, 'store'])->name('admin.' . $key . '.post_new_discount');
//            Route::get('discounts/{id}', [DiscountController::class, 'edit'])->name('admin.' . $key . '.get_edit_discount');
//            Route::post('discounts/{id}', [DiscountController::class, 'update'])->name('admin.' . $key . '.update_discount');
//            Route::delete('discounts/{id}', [DiscountController::class, 'destroy'])->name('admin.' . $key . '.delete_discount');
//            Route::post('discount_ajax/{id}', [DiscountController::class, 'getDiscount'])->name('admin.' . $key . '.ajaxGetDiscount');
//
//            Route::get('coupons', [DiscountController::class, 'index'])->name('admin.' . $key . '.coupons');
//            Route::get('test', [\App\Http\Controllers\CouponController::class, 'getAllCoupons']);
//        });
//    }
//}
//Route::post('cs', [DiscountController::class, 'testException']);
//
//Route::post('test', [TestController::class, 'testException']);
//Route::group(['prefix' => 'auth'], function () {
//    Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register');
//    Route::post('register', [AuthController::class, 'register']);
//    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
//    Route::post('login', [AuthController::class, 'login']);
//    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
//});
