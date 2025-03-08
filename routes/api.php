<?php

use App\Http\Controllers\DiscountController;
use App\Http\Controllers\GenerateController;
use Illuminate\Support\Facades\Route;

foreach (config('database.connections') as $key => $db) {
    if ($key && $key !== 'mysql') {
        Route::group(['prefix' => $key], function () use ($key) {
            Route::post('discountInfo/{id}', [DiscountController::class, 'getDiscountInfo'])->name('admin.' . $key . '.discountInfo');
        });
    }
}
Route::post('coupon/private/{generate_id}/{shop_name}', [GenerateController::class, 'privateGenerateCoupon'])->name('private.generate.url.coupon');

Route::get('test1', [GenerateController::class, 'test1']);
Route::get('test2', [GenerateController::class, 'test2']);
Route::get('test3', [GenerateController::class, 'test3']);

Route::post('cs/discounts/{id}', [DiscountController::class, 'getDiscountInfo']);
Route::group(['prefix' => 'cs'], function () {
    Route::get('discounts/{id}', [DiscountController::class, 'getDiscountInfo']);
});
