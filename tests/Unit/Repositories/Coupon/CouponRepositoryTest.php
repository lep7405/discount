<?php
//
//namespace Tests\Unit\Repositories\Coupon;
//
//use App\Models\Coupon;
//use App\Models\Discount;
//use App\Repositories\Coupon\CouponRepository;
//use App\Repositories\Discount\DiscountRepository;
//use Illuminate\Foundation\Testing\RefreshDatabase;
//use Tests\TestCase;
//
//uses(\Tests\TestCase::class,RefreshDatabase::class);
//
//beforeEach(function () {
//    $this->discountRepository = app(DiscountRepository::class);
//    $this->couponRepository = app(CouponRepository::class);
//    Coupon::on('cs')->delete();
//    Discount::on('cs')->delete();
//    $this->databaseName = 'cs';
//});
//it('should get all coupons without filters', function () {
//    $discount = Discount::on($this->databaseName)->create([
//        'name' => 'Test Discount',
//        'code' => 'TEST',
//        'value' => 10,
//        'type' => 'percentage',
//    ]);
//
//    Coupon::factory()->count(3)->make()->each(function ($coupon) use ($discount) {
//        $coupon->discount_id = $discount->id;
//        $coupon->setConnection($this->databaseName)->save();
//    });
//
//    $filters = [];
//    $coupons = $this->couponRepository->getAll(null, $this->databaseName, $filters);
//
//    expect($coupons)->toHaveCount(3);
//});
//it('should get coupons with discount id', function () {
//    $discountId = 1;
//    $discount = Discount::on($this->databaseName)->create([
//        'id' => $discountId,
//        'name' => 'Test Discount',
//        'code' => 'TEST',
//        'value' => 10,
//        'type' => 'percentage',
//    ]);
//    Coupon::factory()->count(2)->make()->each(function ($coupon) use ($discount) {
//        $coupon->discount_id = $discount->id;
//        $coupon->setConnection($this->databaseName)->save();
//    });
//    $discountId2 = 2;
//    $discount2 = Discount::on($this->databaseName)->create([
//        'id' => $discountId2,
//        'name' => 'Test Discount 2',
//        'code' => 'TEST2',
//        'value' => 20,
//        'type' => 'percentage',
//    ]);
//    Coupon::factory()->count(2)->make()->each(function ($coupon) use($discount2) {
//        $coupon->discount_id = $discount2->id;
//        $coupon->setConnection($this->databaseName)->save();
//    });
//
//    $filters = [];
//    $coupons = $this->couponRepository->getAll($discount->id, $this->databaseName, $filters);
//    expect($coupons)->toHaveCount(2);
//    foreach ($coupons as $coupon) {
//        expect($coupon->discount_id)->toBe($discount->id);
//    }
//});
