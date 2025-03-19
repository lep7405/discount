<?php

namespace Tests\Unit\Services\Coupon;

use App\Exceptions\CouponException;
use App\Exceptions\NotFoundException;
use App\Models\Coupon;
use App\Models\Discount;
use App\Repositories\Coupon\CouponRepository;
use App\Repositories\Discount\DiscountRepository;
use App\Services\Coupon\CouponService;
use App\Services\Coupon\CouponServiceImp;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
uses(\Tests\TestCase::class);

beforeEach(function () {
    $this->discountRepository = Mockery::mock(DiscountRepository::class);
    $this->couponRepository = Mockery::mock(CouponRepository::class);
    $this->couponService = app()->make(CouponService::class, [
        'couponRepository' => $this->couponRepository,
        'discountRepository' => $this->discountRepository,
    ]);

    $this->couponService2 = app(CouponService::class);
    $this->databaseName = 'cs';
    Coupon::on($this->databaseName)->delete();
    Discount::on($this->databaseName)->delete();

});

//test index
test('index return correct pagination ', function () {
    $discount = Discount::on($this->databaseName)->create([
        'name' => 'Test Discount',
        'code' => 'TEST',
        'value' => 10,
        'type' => 'percentage',
    ]);
    Coupon::factory()->count(3)->make()->each(function ($coupon) use ($discount) {
        $coupon->discount_id = $discount->id;
        $coupon->setConnection($this->databaseName)->save();
    });

    $filters = [];

    $result = $this->couponService2->index($this->databaseName, $filters);

    expect($result['couponData'])->toBeInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class)
        ->and($result['totalPagesCoupon'])->toBe(1)
        ->and($result['totalItemsCoupon'])->toBe(3)
        ->and($result['currentPagesCoupon'])->toBe(1)
        ->and($result['totalCoupons'])->toBe(3)
        ->and($result['couponData']->items())->toHaveCount(3);
});
test('index return correct data with filters contain status', function () {
    $discount = Discount::on($this->databaseName)->create([
        'name' => 'Test Discount',
        'code' => 'TEST',
        'value' => 10,
        'type' => 'percentage',
    ]);

    // Create coupons with different status
    Coupon::factory()->count(2)->make()->each(function ($coupon) use ($discount) {
        $coupon->discount_id = $discount->id;
        $coupon->status = 1;
        $coupon->setConnection($this->databaseName)->save();
    });

    Coupon::factory()->count(1)->make()->each(function ($coupon) use ($discount) {
        $coupon->discount_id = $discount->id;
        $coupon->status = 0;
        $coupon->setConnection($this->databaseName)->save();
    });

    $filters = ['status' => '1'];

    $result = $this->couponService2->index($this->databaseName, $filters);

    expect($result['totalItemsCoupon'])->toBe(2);
    foreach ($result['couponData']->items() as $coupon) {
        expect($coupon->status)->toBe(1);
    }
});
test('index return correct data with filters contain seachCoupon', function () {
    $discount = Discount::on($this->databaseName)->create([
        'name' => 'Test Discount',
        'code' => 'TEST',
        'value' => 10,
        'type' => 'percentage',
    ]);
    Coupon::on($this->databaseName)->insert([
        [
            'code' => 'CODE123',
            'shop' => 'Test Shop',
            'discount_id' => $discount->id
        ],
        [
            'code' => 'CODE456',
            'shop' => 'Another Shop',
            'discount_id' => $discount->id
        ]
    ]);

    $filters = ['searchCoupon' => 'CODE123'];

    $result = $this->couponService2->index($this->databaseName, $filters);

    expect($result['totalItemsCoupon'])->toBe(1)
        ->and($result['couponData']->first()->code)->toBe('CODE123');
});
test('index return correct data with timesUsed', function () {
    // Arrange
    $discount = Discount::on($this->databaseName)->create([
        'name' => 'Test Discount',
        'code' => 'TEST',
        'value' => 10,
        'type' => 'percentage',
    ]);

    Coupon::on($this->databaseName)->insert([
        [
            'code' => 'CODE123',
            'shop' => 'Test Shop',
            'discount_id' => $discount->id,
            'times_used' => 5
        ],
        [
            'code' => 'CODE456',
            'shop' => 'Another Shop',
            'discount_id' => $discount->id,
            'times_used' => 10
        ]
    ]);

    $filters = ['timeUsed' => 'asc'];

    // Act
    $result = $this->couponService2->index($this->databaseName, $filters);

    // Assert
    expect($result['couponData']->first()->times_used)->toBe(5)
        ->and($result['couponData']->last()->times_used)->toBe(10);
});

//test create
test('create coupon successfully', function () {
    $databaseName = 'cs';
    $discount = Discount::on($databaseName)->create([
        'name' => 'Test Discount',
        'value' => 10
    ]);
    $attributes = [
        'code' => 'NEW_CODE',
        'shop' => 'new_shop',
        'discount_id' => $discount->id,
        'times_used' => 0
    ];
    $coupon = $this->couponService2->store($databaseName, $attributes);
    $this->assertDatabaseHas('coupons', [
        'code' => $attributes['code'],
        'shop' => $attributes['shop'],
        'discount_id' => $attributes['discount_id'],
        'times_used' => $attributes['times_used'],
        'automatic' => 1 // Vì $databaseName = 'cs' và $attributes['shop'] != null
    ],'cs');
})->only();
test('create coupon successfully with automatic = false when shop is null', function () {
    $databaseName = 'cs';
    $discount = Discount::on($databaseName)->create([
        'name' => 'Test Discount',
        'value' => 10
    ]);
    $attributes = [
        'code' => 'NEW_CODE',
        'shop' => null,
        'discount_id' => $discount->id,
        'times_used' => 0
    ];
    $coupon = $this->couponService2->store($databaseName, $attributes);
    $this->assertDatabaseHas('coupons', [
        'code' => $attributes['code'],
        'shop' => null,
        'discount_id' => $attributes['discount_id'],
        'times_used' => $attributes['times_used'],
        'automatic' => 0 // Vì $attributes['shop'] == null
    ],'cs');
});
//test update
test('update fails when coupon not found', function () {
    $id = 999;
    $databaseName = 'cs';
    $attributes = [
        'code' => 'NEW_CODE',
        'shop' => 'new_shop'
    ];
    $couponIdNotFound = 100000000000;
    expect(fn () => $this->couponService2->update($couponIdNotFound, $databaseName, $attributes))
        ->toThrow(NotFoundException::class);
});
test('update coupon fails when coupon has been used', function () {
    $databaseName = 'cs';
    $attributes = [
        'code' => 'NEW_CODE',
        'shop' => 'new_shop'
    ];
    $discount=Discount::on($databaseName)->create([
        'id' => 1,
        'name' => 'Test Discount',
        'value' => 10
    ]);
    $coupon=Coupon::on($databaseName)->create([
        'id' => 1,
        'discount_id' => $discount->id,
        'code' => 'OLD_CODE',
        'shop' => 'old_shop',
        'times_used' => 5
    ]);
    expect(fn () => $this->couponService2->update($coupon->id, $databaseName, $attributes))
        ->toThrow(CouponException::class);
});
test('update coupon fails when coupon code not used and already exists for different ID', function () {
    $id = 1;

    $discount=Discount::on($this->databaseName)->create([
        'id' => 1,
        'name' => 'Test Discount',
        'value' => 10
    ]);
    $attributes = [
        'code' => 'EXISTING_CODE',
        'shop' => 'new_shop',
        'discount_id' => $discount->id,
    ];
    $couponNeedUpdate = Coupon::on($this->databaseName)->create([
        'id' => 1,
        'discount_id' => $discount->id,
        'code' => 'OLD_CODE',
        'shop' => 'old_shop',
        'times_used' => 0
    ]);
    $existCouponCode = Coupon::on($this->databaseName)->create([
        'id' => 2,
        'discount_id' => $discount->id,
        'code' => 'EXISTING_CODE',
        'shop' => 'old_shop',
        'times_used' => 0
    ]);
    expect(fn () => $this->couponService2->update($couponNeedUpdate->id, $this->databaseName, $attributes))
        ->toThrow(function(CouponException $e){
            expect($e->getErrors()['error'])->toBe('Code existed!');
        });
});
test('update coupon fails when coupon code not used and not validate data update', function () {
    $id = 1;

    $discount=Discount::on($this->databaseName)->create([
        'id' => 1,
        'name' => 'Test Discount',
        'value' => 10
    ]);
    $attributes = [
        'code' => 'EXISTING_CODE',
        'shop' => 'new_shop',
//        'discount_id' => $discount->id,
    ];
    $couponUpdate = Coupon::on($this->databaseName)->create([
        'id' => 1,
        'discount_id' => $discount->id,
        'code' => 'OLD_CODE',
        'shop' => 'old_shop',
        'times_used' => 0
    ]);

    expect(fn () => $this->couponService2->update($couponUpdate->id, $this->databaseName, $attributes))
        ->toThrow(CouponException::class);
});
test('update coupon successfully when times_used is 0 and not exist coupon code want to update', function () {
    $id = 1;
    $attributes = [
        'code' => 'EXISTING_CODE',
        'shop' => 'new_shop'
    ];
    $discount=Discount::on($this->databaseName)->create([
        'id' => 1,
        'name' => 'Test Discount',
        'value' => 10
    ]);
    $attributes = [
        'code' => 'EXISTING_CODE',
        'shop' => 'new_shop',
        'discount_id' => $discount->id,
    ];
    $coupon = Coupon::on($this->databaseName)->create([
        'id' => 1,
        'discount_id' => $discount->id,
        'code' => 'OLD_CODE',
        'shop' => 'old_shop',
        'times_used' => 0
    ]);
    Coupon::on($this->databaseName)->where('code',$attributes['code'])->delete();
    $result= $this->couponService2->update($coupon->id, $this->databaseName, $attributes);
    expect($result)->toBe(1);
    $this->assertDatabaseHas('coupons', [
        'id' => $coupon->id,
        'code' => $attributes['code'],
        'shop' => $attributes['shop'],
        'times_used' => 0
    ],'cs');
});

//test delete
test('delete successfully removes unused coupon', function () {
    $id = 1;
    $databaseName = 'test_db';

    // Existing coupon with times_used = 0
    $existingCoupon = (object) [
        'id' => $id,
        'code' => 'TEST123',
        'shop' => 'test-shop',
        'timesUsed' => 0
    ];

    // Mock findById in the repository
    $this->couponRepository->shouldReceive('findById')
        ->once()
        ->with($id, $databaseName)
        ->andReturn($existingCoupon);

    // Mock deleteCoupon
    $this->couponRepository->shouldReceive('deleteCoupon')
        ->once()
        ->with($id, $databaseName)
        ->andReturn(true);

    $result = $this->couponService->delete($id, $databaseName);
    expect($result)->toBeTrue();
});

test('delete throws exception when coupon has been used', function () {
    $id = 1;
    $databaseName = 'test_db';

    // Existing coupon with times_used > 0
    $existingCoupon = (object) [
        'id' => $id,
        'code' => 'TEST123',
        'shop' => 'test-shop',
        'timesUsed' => 5
    ];

    // Mock findById in the repository
    $this->couponRepository->shouldReceive('findById')
        ->once()
        ->with($id, $databaseName)
        ->andReturn($existingCoupon);

    // No need to mock deleteCoupon since exception will be thrown first

    expect(fn () => $this->couponService->delete($id, $databaseName))
        ->toThrow(CouponException::class);
});

test('delete throws exception when coupon not found', function () {
    $id = 999;
    $databaseName = 'test_db';

    // Mock findById to return null (not found)
    $this->couponRepository->shouldReceive('findById')
        ->once()
        ->with($id, $databaseName)
        ->andReturn(null);

    expect(fn () => $this->couponService->delete($id, $databaseName))
        ->toThrow(NotFoundException::class, 'Coupon not found');
});

// test decrement
test('decrementTimesUsedCoupon successfully decrements times_used', function () {
    $id = 1;
    $databaseName = 'test_db';
    $numDecrement = 3;

    // Existing coupon with times_used > numDecrement
    $existingCoupon = (object) [
        'id' => $id,
        'code' => 'TEST123',
        'shop' => 'test-shop',
        'timesUsed' => 5
    ];

    // Mock findById to return existing coupon
    $this->couponRepository->shouldReceive('findById')
        ->once()
        ->with($id, $databaseName)
        ->andReturn($existingCoupon);

    // Mock decrementTimesUsed
    $this->couponRepository->shouldReceive('decrementTimesUsed')
        ->once()
        ->with($id, $databaseName, $numDecrement)
        ->andReturn(true);

    // This should not throw an exception
    $this->couponService->decrementTimesUsedCoupon($id, $databaseName, $numDecrement);
});

test('decrementTimesUsedCoupon throws exception when times_used is less than decrement amount', function () {
    $id = 1;
    $databaseName = 'test_db';
    $numDecrement = 5;

    // Existing coupon with times_used < numDecrement
    $existingCoupon = (object) [
        'id' => $id,
        'code' => 'TEST123',
        'shop' => 'test-shop',
        'timesUsed' => 3
    ];

    // Mock findById to return existing coupon
    $this->couponRepository->shouldReceive('findById')
        ->once()
        ->with($id, $databaseName)
        ->andReturn($existingCoupon);

    // We should not reach the decrementTimesUsed method

    expect(fn () => $this->couponService->decrementTimesUsedCoupon($id, $databaseName, $numDecrement))
        ->toThrow(CouponException::class);
});

test('decrementTimesUsedCoupon throws exception when coupon not found', function () {
    $id = 999;
    $databaseName = 'test_db';
    $numDecrement = 1;

    // Mock findById to return null (not found)
    $this->couponRepository->shouldReceive('findById')
        ->once()
        ->with($id, $databaseName)
        ->andReturn(null);

    expect(fn () => $this->couponService->decrementTimesUsedCoupon($id, $databaseName, $numDecrement))
        ->toThrow(NotFoundException::class, 'Coupon not found');
});

//test create coupon by discount
test('createCouponByDiscount throws exception when discount not found', function () {
    $discountId = 999;
    $databaseName = 'test_db';
    $attributes = ['code' => 'NEWCODE123', 'shop' => 'test-shop'];

    $this->discountRepository->shouldReceive('findById')
        ->once()
        ->with($discountId, $databaseName)
        ->andReturn(null);

    expect(fn () => $this->couponService->createCouponByDiscount($discountId, $databaseName, $attributes))
        ->toThrow(NotFoundException::class, 'Discount not found');
});
test('createCouponByDiscount successfully creates coupon when discount exists', function () {
    $discountId = 1;
    $databaseName = 'test_db';
    $attributes = ['code' => 'NEWCODE123', 'shop' => 'test-shop'];

    $discount = (object) [
        'id' => $discountId,
        'name' => 'Test Discount',
        'value' => 10
    ];

    $expectedCoupon = (object) [
        'id' => 5,
        'code' => 'NEWCODE123',
        'shop' => 'test-shop',
        'discountId' => $discountId,
        'timesUsed' => 0
    ];
    $expectedCoupon1 = [
        'code' => 'NEWCODE123',
        'shop' => 'test-shop',
        'discountId' => $discountId,
        'timesUsed' => 0
    ];

    $this->discountRepository->shouldReceive('findById')
        ->once()
        ->with($discountId, $databaseName)
        ->andReturn($discount);

    $this->couponRepository->shouldReceive('createCoupon')
        ->once()
        ->with($databaseName, $expectedCoupon1)
        ->andReturn($expectedCoupon);

    $result = $this->couponService->createCouponByDiscount($discountId, $databaseName, $attributes);
    expect($result)->toBe($expectedCoupon);
});

//test get all coupons by discount
test('getAllCouponsByDiscount success',function(){
    Coupon::on($this->databaseName)->delete();
    Discount::on($this->databaseName)->delete();
    $discount=Discount::on($this->databaseName)->create([
        'id' => 1,
        'name' => 'Test Discount',
        'value' => 10
    ]);
    $couponsList=Coupon::on($this->databaseName)->insert([
        [
            'code' => 'CODE123',
            'shop' => 'Test Shop',
            'discount_id' => $discount->id
        ],
        [
            'code' => 'CODE456',
            'shop' => 'Another Shop',
            'discount_id' => $discount->id
        ]
    ]);
    $filters = ['searchCoupon' => 'CODE123'];
    $result = $this->couponService2->getAllCouponsByDiscount($discount->id, $this->databaseName, $filters);
    expect($result['couponData'])->toHaveCount(1);
});
