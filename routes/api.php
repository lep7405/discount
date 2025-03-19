<?php

use App\Http\Controllers\DiscountController;
use App\Http\Controllers\GenerateController;
use Illuminate\Support\Facades\Route;

Route::post('coupon/private/{generate_id}/{shop_name}', [GenerateController::class, 'privateGenerateCoupon'])->name('private.generate.url.coupon');
Route::post('/coupon/affiliate-partner/{appCode}/{shopName}', [GenerateController::class,'createCouponFromAffiliatePartner'])->middleware('ip.checker');
