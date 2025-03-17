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
});

//test index
test('it returns paginated coupons correctly', function () {
    // Tạo dữ liệu giả cho kết quả phân trang
    $couponCollection = collect([
        (object) ['id' => 1, 'code' => 'TEST1', 'discount_id' => 1, 'times_used' => 1],
        (object) ['id' => 2, 'code' => 'TEST2', 'discount_id' => 1, 'times_used' => 2],
        (object) ['id' => 3, 'code' => 'TEST3', 'discount_id' => 1, 'times_used' => 0],
        (object) ['id' => 4, 'code' => 'TEST4', 'discount_id' => 1, 'times_used' => 1],
        (object) ['id' => 5, 'code' => 'TEST5', 'discount_id' => 1, 'times_used' => 2],
    ]);

    $paginatedData = new LengthAwarePaginator(
        $couponCollection,
        5,
        5,
        1
    );
    $this->couponRepository->shouldReceive('countCoupons')
        ->once()
        ->with('cs')
        ->andReturn(5);
    $this->couponRepository->shouldReceive('getAllCoupons')
        ->once()
        ->with(null, ['perPageCoupon' => 5, 'pageCoupon' => 1], 'cs')
        ->andReturn($paginatedData);

    $result = $this->couponService->index([
        'perPageCoupon' => 5,
        'pageCoupon' => 1,
    ], 'cs');

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['couponData', 'totalItemsCoupon', 'currentPagesCoupon'])
        ->and($result['couponData'])->toHaveCount(5)
        ->and($result['totalItemsCoupon'])->toBe(5)
        ->and($result['currentPagesCoupon'])->toBe(1);
});

test('index returns paginated coupon data with custom parameters', function () {
    // Arrange
    $filters = ['perPageCoupon' => 5];
    $databaseName = 'test_db';
    $totalCount = 15;

    $couponItems = collect([
        (object) ['id' => 1, 'code' => 'COUPON1', 'timesUsed' => 0, 'shop' => 'shop1'],
        (object) ['id' => 2, 'code' => 'COUPON2', 'timesUsed' => 3, 'shop' => 'shop2'],
    ]);

    $paginatedData = new LengthAwarePaginator($couponItems, $totalCount, 5, 1);

    $this->couponRepository->shouldReceive('countCoupons')
        ->once()
        ->with($databaseName)
        ->andReturn($totalCount);

    $this->couponRepository->shouldReceive('getAll')
        ->withArgs(function ($discountId, $dbName, $filtersArg) use ($databaseName) {
            return $discountId === null &&
                $dbName === $databaseName &&
                $filtersArg['perPageCoupon'] === 5;
        })
        ->andReturn($paginatedData);

    // Act
    $result = $this->couponService->index($databaseName, $filters);

    // Assert
    expect($result)
        ->toBeArray()
        ->toHaveKeys(['couponData', 'totalPagesCoupon', 'totalItemsCoupon', 'currentPagesCoupon', 'totalItems'])
        ->and($result['couponData'])->toBe($paginatedData)
        ->and($result['totalPagesCoupon'])->toBe($paginatedData->lastPage())
        ->and($result['totalItemsCoupon'])->toBe($paginatedData->total())
        ->and($result['currentPagesCoupon'])->toBe($paginatedData->currentPage())
        ->and($result['totalItems'])->toBe($totalCount);
});

test('index sanitizes invalid filter parameters', function () {
    // Arrange
    $filters = ['perPageCoupon' => 5, 'timeUsed' => 'invalid', 'status' => '2'];
    $databaseName = 'test_db';
    $totalCount = 15;

    $this->couponRepository->shouldReceive('countCoupons')
        ->once()
        ->with($databaseName)
        ->andReturn($totalCount);

    $this->couponRepository->shouldReceive('getAll')
        ->withArgs(function ($discountId, $dbName, $filtersArg) {
            return $discountId === null &&
                $dbName === 'test_db' &&
                $filtersArg['timeUsed'] === null &&
                $filtersArg['status'] === null;
        })
        ->andReturn(new LengthAwarePaginator(collect([]), $totalCount, 5, 1));

    // Act
    $result = $this->couponService->index($databaseName, $filters);

    // Assert
    expect($result)->toBeArray();
});

test('index applies default pagination when no filters provided', function () {
    // Arrange
    $databaseName = 'test_db';
    $filters = [];
    $countAll = 100;
    $defaultPerPage = 5; // Default per page value in handleFilters method

    $paginator = Mockery::mock(LengthAwarePaginator::class);
    $paginator->shouldReceive('lastPage')->andReturn(20);
    $paginator->shouldReceive('total')->andReturn($countAll);
    $paginator->shouldReceive('currentPage')->andReturn(1);

    $this->couponRepository->shouldReceive('countCoupons')
        ->once()
        ->with($databaseName)
        ->andReturn($countAll);

    $this->couponRepository->shouldReceive('getAll')
        ->withArgs(function ($discountId, $dbName, $filtersArg) use ($defaultPerPage) {
            return $discountId === null &&
                $dbName === 'test_db' &&
                isset($filtersArg['perPageCoupon']) &&
                $filtersArg['perPageCoupon'] == $defaultPerPage;
        })
        ->andReturn($paginator);

    // Act
    $result = $this->couponService->index($databaseName, $filters);

    // Assert
    expect($result)->toBe([
        'couponData' => $paginator,
        'totalPagesCoupon' => 20,
        'totalItemsCoupon' => $countAll,
        'currentPagesCoupon' => 1,
        'totalItems' => $countAll,
    ]);
});

test('handleFilters correctly processes perPageCoupon parameter', function () {
    // Arrange
    $countAll = 100;
    $service = $this->couponService;

    // Test with -1 value (should use countAll)
    $filters1 = ['perPageCoupon' => -1];
    $result1 = $service->handleFilters($countAll, $filters1);
    expect($result1['perPageCoupon'])->toBe($countAll);

    // Test with normal value
    $filters2 = ['perPageCoupon' => 20];
    $result2 = $service->handleFilters($countAll, $filters2);
    expect($result2['perPageCoupon'])->toBe(20);

    // Test with default value when not provided
    $filters3 = [];
    $result3 = $service->handleFilters($countAll, $filters3);
    expect($result3['perPageCoupon'])->toBe(5);
});

test('handleFilters sanitizes invalid timeUsed and status parameters', function () {
    // Arrange
    $countAll = 50;
    $service = $this->couponService;

    // Test with invalid timeUsed
    $filters1 = ['timeUsed' => 'invalid'];
    $result1 = $service->handleFilters($countAll, $filters1);
    expect($result1['timeUsed'])->toBeNull();

    // Test with valid timeUsed
    $filters2 = ['timeUsed' => 'desc'];
    $result2 = $service->handleFilters($countAll, $filters2);
    expect($result2['timeUsed'])->toBe('desc');

    // Test with invalid status
    $filters3 = ['status' => '2'];
    $result3 = $service->handleFilters($countAll, $filters3);
    expect($result3['status'])->toBeNull();

    // Test with valid status
    $filters4 = ['status' => '1'];
    $result4 = $service->handleFilters($countAll, $filters4);
    expect($result4['status'])->toBe('1');
});


//test update
test('update coupon successfully when times_used is 0', function () {
    $id = 1;
    $databaseName = 'test_db';
    $attributes = [
        'code' => 'NEW_CODE',
        'shop' => 'shop1',
        'discountId' => 5,
        'other_field' => 'should be filtered out'
    ];

    // Existing coupon with times_used = 0
    $existingCoupon = (object) [
        'id' => $id,
        'code' => 'OLD_CODE',
        'shop' => 'old_shop',
        'discountId' => 3,
        'timesUsed' => 0
    ];

    // Mock findById to return existing coupon
    $this->couponRepository->shouldReceive('findById')
        ->once()
        ->with($id, $databaseName)
        ->andReturn($existingCoupon);

    // Mock getCouponByCode to return null (no duplicate code)
    $this->couponRepository->shouldReceive('getCouponByCode')
        ->once()
        ->with('NEW_CODE', $databaseName)
        ->andReturn(null);

    // Expected filtered attributes that should be passed to updateCoupon
    $expectedFilteredAttributes = [
        'code' => 'NEW_CODE',
        'shop' => 'shop1',
        'discountId' => 5
    ];

    // Mock updateCoupon
    $updatedCoupon = (object) array_merge(
        (array) $existingCoupon,
        $expectedFilteredAttributes,
    );

    $this->couponRepository->shouldReceive('updateCoupon')
        ->once()
        ->with($id, $databaseName, $expectedFilteredAttributes)
        ->andReturn($updatedCoupon);

    $result = $this->couponService->update($id, $databaseName, $attributes);
    expect($result)->toBe($updatedCoupon);
});

test('update coupon with existing code but same ID', function () {
    $id = 1;
    $databaseName = 'test_db';
    $attributes = [
        'code' => 'EXISTING_CODE',
        'shop' => 'new_shop'
    ];

    // Existing coupon with times_used = 0
    $existingCoupon = (object) [
        'id' => $id,
        'code' => 'OLD_CODE',
        'shop' => 'old_shop',
        'timesUsed' => 0
    ];

    // Mock findById to return existing coupon
    $this->couponRepository->shouldReceive('findById')
        ->once()
        ->with($id, $databaseName)
        ->andReturn($existingCoupon);

    // Mock getCouponByCode to return a coupon with same ID (which is allowed)
    $couponWithSameCode = (object) [
        'id' => $id,
        'code' => 'EXISTING_CODE'
    ];

    $this->couponRepository->shouldReceive('getCouponByCode')
        ->once()
        ->with('EXISTING_CODE', $databaseName)
        ->andReturn($couponWithSameCode);

    // Mock updateCoupon
    $updatedCoupon = (object) [
        'id' => $id,
        'code' => 'EXISTING_CODE',
        'shop' => 'new_shop',
        'timesUsed' => 0
    ];

    $this->couponRepository->shouldReceive('updateCoupon')
        ->once()
        ->with($id, $databaseName, $attributes)
        ->andReturn($updatedCoupon);

    $result = $this->couponService->update($id, $databaseName, $attributes);
    expect($result)->toBe($updatedCoupon);
});

test('update throws exception when coupon has been used', function () {
    $id = 1;
    $databaseName = 'test_db';
    $attributes = [
        'code' => 'NEW_CODE',
        'shop' => 'new_shop'
    ];

    // Existing coupon with times_used > 0
    $existingCoupon = (object) [
        'id' => $id,
        'code' => 'OLD_CODE',
        'shop' => 'old_shop',
        'timesUsed' => 5
    ];

    // Mock findById to return existing coupon
    $this->couponRepository->shouldReceive('findById')
        ->once()
        ->with($id, $databaseName)
        ->andReturn($existingCoupon);

    // No need to mock getCouponByCode since exception will be thrown before that

    expect(fn () => $this->couponService->update($id, $databaseName, $attributes))
        ->toThrow(CouponException::class);
});

test('update throws exception when coupon code already exists for different ID', function () {
    $id = 1;
    $databaseName = 'test_db';
    $attributes = [
        'code' => 'EXISTING_CODE',
        'shop' => 'new_shop'
    ];

    // Existing coupon with times_used = 0
    $existingCoupon = (object) [
        'id' => $id,
        'code' => 'OLD_CODE',
        'shop' => 'old_shop',
        'timesUsed' => 0
    ];

    // Mock findById to return existing coupon
    $this->couponRepository->shouldReceive('findById')
        ->once()
        ->with($id, $databaseName)
        ->andReturn($existingCoupon);

    // Mock getCouponByCode to return a coupon with different ID (which should throw exception)
    $couponWithDifferentId = (object) [
        'id' => 2, // Different ID
        'code' => 'EXISTING_CODE'
    ];

    $this->couponRepository->shouldReceive('getCouponByCode')
        ->once()
        ->with('EXISTING_CODE', $databaseName)
        ->andReturn($couponWithDifferentId);

    expect(fn () => $this->couponService->update($id, $databaseName, $attributes))
        ->toThrow(CouponException::class);
});

test('update throws exception when coupon not found', function () {
    $id = 999;
    $databaseName = 'test_db';
    $attributes = [
        'code' => 'NEW_CODE',
        'shop' => 'new_shop'
    ];

    // Mock findById to return null (not found)
    $this->couponRepository->shouldReceive('findById')
        ->once()
        ->with($id, $databaseName)
        ->andReturn(null);

    expect(fn () => $this->couponService->update($id, $databaseName, $attributes))
        ->toThrow(NotFoundException::class);
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

    $this->discountRepository->shouldReceive('findDiscountByIdNoCoupon')
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

    $this->discountRepository->shouldReceive('findDiscountByIdNoCoupon')
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
