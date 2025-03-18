<?php

namespace Tests\Unit\Services\Discount;

use App\Exceptions\DiscountException;
use App\Exceptions\NotFoundException;
use App\Models\Coupon;
use App\Models\Discount;
use App\Repositories\Coupon\CouponRepository;
use App\Repositories\Discount\DiscountRepository;
use App\Services\Discount\DiscountService;
use App\Services\Discount\DiscountServiceImp;
use App\Validator\UpdateDiscountValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->discountRepository = Mockery::mock(DiscountRepository::class);
    $this->couponRepository = Mockery::mock(CouponRepository::class);
    $this->discountService = app()->make(DiscountService::class, [
        'discountRepository' => $this->discountRepository,
        'couponRepository' => $this->couponRepository,
    ]);
    $this->discountValidator = Mockery::mock('alias:App\Validator\UpdateDiscountValidator');
    Coupon::on('cs')->delete();
    Discount::on('cs')->delete();
});

//test index
test('index returns paginated discount data with custom parameters', function () {
    // Arrange
    $filters = ['perPageDiscount' => 5];
    $databaseName = 'test_db';
    $totalCount = 15;

    $discountItems = collect([
        (object) ['id' => 1, 'name' => 'Discount 1', 'value' => 10, 'type' => 'percentage'],
        (object) ['id' => 2, 'name' => 'Discount 2', 'value' => 20, 'type' => 'fixed_amount'],
    ]);

    $paginatedData = new LengthAwarePaginator($discountItems, $totalCount, 5, 1);

    $this->discountRepository->shouldReceive('countDiscount')
        ->once()
        ->with($databaseName)
        ->andReturn($totalCount);

    $this->discountRepository->shouldReceive('getAll')
        ->withArgs(function ($dbNameArg, $filtersArg) use ($databaseName) {
            return $dbNameArg === $databaseName && $filtersArg['perPageDiscount'] === 5;
        })
        ->andReturn($paginatedData);

    // Act
    $result = $this->discountService->index($databaseName, $filters);

    // Assert
    expect($result)
        ->toBeArray()
        ->toHaveKeys(['discountData', 'totalPagesDiscount', 'totalItemsDiscount', 'currentPagesDiscount', 'totalItems'])
        ->and($result['discountData'])->toBe($paginatedData)
        ->and($result['totalPagesDiscount'])->toBe($paginatedData->lastPage())
        ->and($result['totalItemsDiscount'])->toBe($paginatedData->total())
        ->and($result['currentPagesDiscount'])->toBe($paginatedData->currentPage())
        ->and($result['totalItems'])->toBe($totalCount);
});

test('index sanitizes invalid date parameters', function () {
    // Arrange
    $filters = ['perPageDiscount' => 5, 'startedAt' => 'invalid'];
    $databaseName = 'test_db';
    $totalCount = 15;

    $this->discountRepository->shouldReceive('countDiscount')
        ->once()
        ->with($databaseName)
        ->andReturn($totalCount);

    $this->discountRepository->shouldReceive('getAll')
        ->withArgs(function ($dbNameArg, $filtersArg) {
            return $dbNameArg === 'test_db' && $filtersArg['startedAt'] === null;
        })
        ->andReturn(new LengthAwarePaginator(collect([]), $totalCount, 5, 1));

    // Act
    $result = $this->discountService->index($databaseName, $filters);

    // Assert
    expect($result)->toBeArray();
});

test('index applies default pagination when no filters provided', function () {
    // Arrange
    $databaseName = 'test_db';
    $filters = [];
    $countAll = 100;
    $perPage = config('constant.default_per_page', 15);

    $paginator = Mockery::mock(LengthAwarePaginator::class);
    $paginator->shouldReceive('lastPage')->andReturn(7);
    $paginator->shouldReceive('total')->andReturn($countAll);
    $paginator->shouldReceive('currentPage')->andReturn(1);

    $this->discountRepository->shouldReceive('countDiscount')
        ->once()
        ->with($databaseName)
        ->andReturn($countAll);

    $this->discountRepository->shouldReceive('getAll')
        ->with($databaseName, Mockery::on(function ($arg) use ($perPage) {
            return isset($arg['perPageDiscount']) && $arg['perPageDiscount'] == $perPage;
        }))
        ->andReturn($paginator);

    // Act
    $result = $this->discountService->index($databaseName, $filters);

    // Assert
    expect($result)->toBe([
        'discountData' => $paginator,
        'totalPagesDiscount' => 7,
        'totalItemsDiscount' => $countAll,
        'currentPagesDiscount' => 1,
        'totalItems' => $countAll,
    ]);
});

//test update
test('update thành công khi không có coupon đã sử dụng', function () {
    $id = 1;
    $databaseName = 'cs';
    $attributes = [
        'name' => 'Giảm giá cập nhật',
        'value' => 15,
        'type' => 'percentage',
    ];

    // Tạo discount với coupon chưa sử dụng
    $discount = (object) [
        'id' => $id,
        'coupon' => collect([
            (object) ['times_used' => 0],
        ]),
    ];

    $this->discountRepository->shouldReceive('findByIdWithCoupon')
        ->once()
        ->with($id, $databaseName)
        ->andReturn($discount);

    $this->discountValidator->shouldReceive('validateUpdate')
        ->once()
        ->with($attributes, false, $databaseName)
        ->andReturn($attributes);
    $this->discountRepository->shouldReceive('updateDiscount')
        ->once()
        ->with($id, $databaseName, $attributes)
        ->andReturn($discount);

    $result = $this->discountService->update($id, $databaseName, $attributes);
    expect($result)->toBe($discount);
});

test('update thành công khi có coupon đã sử dụng', function () {
    $id = 1;
    $databaseName = 'test_db';
    $attributes = [
        'name' => 'Giảm giá cập nhật',
    ];

    // Tạo discount với coupon đã sử dụng
    $discount = (object) [
        'id' => $id,
        'coupon' => collect([
            (object) ['times_used' => 1],
        ]),
    ];

    // Mock repository method
    $this->discountRepository->shouldReceive('findByIdWithCoupon')
        ->once()
        ->with($id, $databaseName)
        ->andReturn($discount);

    // Mock validator sử dụng alias
    $this->discountValidator->shouldReceive('validateUpdate')
        ->once()
        ->with($attributes, true, $databaseName)
        ->andReturn($attributes);

    // Mock repository update
    $this->discountRepository->shouldReceive('updateDiscount')
        ->once()
        ->with($id, $databaseName, $attributes)
        ->andReturn($discount);

    $result = $this->discountService->update($id, $databaseName, $attributes);
    expect($result)->toBe($discount);
});

test('update thất bại khi có coupon đã sử dụng và gửi lên các trường không được update', function () {
    $id = 1;
    $databaseName = 'test_db';
    $attributes = [
        'name' => 'Giảm giá cập nhật',
        'value' => 15,
        'type' => 'percentage',
    ];

    // Tạo discount với coupon đã sử dụng
    $discount = (object) [
        'id' => $id,
        'coupon' => collect([
            (object) ['times_used' => 1],
        ]),
    ];

    // Mock repository method
    $this->discountRepository->shouldReceive('findByIdWithCoupon')
        ->once()
        ->with($id, $databaseName)
        ->andReturn($discount);

    // Mock validator để ném ngoại lệ sử dụng alias
    $this->discountValidator->shouldReceive('validateUpdate')
        ->once()
        ->with($attributes, true, $databaseName)
        ->andThrow(DiscountException::class);

    expect(fn () => $this->discountService->update($id, $databaseName, $attributes))
        ->toThrow(DiscountException::class);
});

test('update xử lý đúng khi discount_for_x_month bằng 0', function () {
    $id = 1;
    $databaseName = 'affiliate';
    $attributes = [
        'name' => 'Giảm giá cập nhật',
        'value' => 15,
        'discount_for_x_month' => '0',
        'discount_month' => 3,
    ];

    // Tạo discount với coupon chưa sử dụng
    $discount = (object) [
        'id' => $id,
        'coupon' => collect([
            (object) ['times_used' => 0],
        ]),
    ];

    // Mock config
    config(['constant.SPECIAL_DATABASE_NAMES' => ['affiliate']]);

    // Mock repository method
    $this->discountRepository->shouldReceive('findByIdWithCoupon')
        ->once()
        ->with($id, $databaseName)
        ->andReturn($discount);

    // Mock validator sử dụng alias
    $this->discountValidator->shouldReceive('validateUpdate')
        ->once()
        ->with($attributes, false, $databaseName)
        ->andReturn($attributes);

    // Mong đợi updateDiscount với discount_month = null
    $this->discountRepository->shouldReceive('updateDiscount')
        ->once()
        ->withArgs(function ($discountId, $dbName, $attrs) use ($id, $databaseName) {
            return $discountId === $id &&
                $dbName === $databaseName &&
                $attrs['discount_month'] === null;
        })
        ->andReturn($discount);

    $result = $this->discountService->update($id, $databaseName, $attributes);
    expect($result)->toBe($discount);
});

//test delete
test('xóa discount thành công khi không có coupon đã sử dụng', function () {
    $id = 1;
    $databaseName = 'test_db';

    // Mock discount với coupon chưa sử dụng
    $discount = (object) [
        'id' => $id,
        'coupon' => collect([
            (object) ['times_used' => 0],
        ]),
    ];

    // Mock findDiscountByIdWithCoupon trả về discount hợp lệ
    $this->discountRepository->shouldReceive('findByIdWithCoupon')
        ->once()
        ->with($id, $databaseName)
        ->andReturn($discount);

    // Mock deleteCouponByDiscountId để xác nhận nó được gọi
    $this->couponRepository->shouldReceive('deleteByDiscountId')
        ->once()
        ->with($id, $databaseName)
        ->andReturnNull();

    // Mock deleteDiscount để xác nhận nó được gọi
    $this->discountRepository->shouldReceive('deleteDiscount')
        ->once()
        ->with($id, $databaseName)
        ->andReturnNull();

    // Gọi phương thức delete
    $this->discountService->delete($id, $databaseName);

    // Không có exception nghĩa là test passed
    expect(true)->toBeTrue();
});

test('xóa discount thất bại khi discount không tồn tại', function () {
    $id = 999;
    $databaseName = 'test_db';

    // Mock findDiscountByIdWithCoupon trả về null (không tìm thấy)
    $this->discountRepository->shouldReceive('findByIdWithCoupon')
        ->once()
        ->with($id, $databaseName)
        ->andReturnNull();

    // Kiểm tra exception khi discount không tồn tại
    expect(fn () => $this->discountService->delete($id, $databaseName))
        ->toThrow(NotFoundException::class, 'Discount not found');
});


test('xóa discount thất bại khi có coupon đã được sử dụng', function () {
    $id = 1;
    $databaseName = 'test_db';

    // Mock discount với coupon đã sử dụng
    $discount = (object) [
        'id' => $id,
        'coupon' => collect([
            (object) ['times_used' => 1],
        ]),
    ];

    // Mock findDiscountByIdWithCoupon trả về discount với coupon đã sử dụng
    $this->discountRepository->shouldReceive('findByIdWithCoupon')
        ->once()
        ->with($id, $databaseName)
        ->andReturn($discount);

    // Kiểm tra exception khi có coupon đã được sử dụng
    expect(fn () => $this->discountService->delete($id, $databaseName))
        ->toThrow(DiscountException::class);

    // Kiểm tra phương thức delete và deleteCouponByDiscountId không được gọi
    $this->discountRepository->shouldNotHaveReceived('deleteDiscount');
    $this->couponRepository->shouldNotHaveReceived('deleteByDiscountId');
});


test('xóa discount thành công với nhiều coupon chưa sử dụng', function () {
    $id = 1;
    $databaseName = 'test_db';

    // Mock discount với nhiều coupon chưa sử dụng
    $discount = (object) [
        'id' => $id,
        'coupon' => collect([
            (object) ['times_used' => 0],
            (object) ['times_used' => 0],
            (object) ['times_used' => 0],
        ]),
    ];

    // Mock findDiscountByIdWithCoupon trả về discount hợp lệ
    $this->discountRepository->shouldReceive('findByIdWithCoupon')
        ->once()
        ->with($id, $databaseName)
        ->andReturn($discount);

    // Mock deleteCouponByDiscountId
    $this->couponRepository->shouldReceive('deleteByDiscountId')
        ->once()
        ->with($id, $databaseName)
        ->andReturnNull();

    // Mock deleteDiscount
    $this->discountRepository->shouldReceive('deleteDiscount')
        ->once()
        ->with($id, $databaseName)
        ->andReturnNull();

    // Gọi phương thức delete
    $this->discountService->delete($id, $databaseName);

    // Không có exception nghĩa là test passed
    expect(true)->toBeTrue();
});

//test getDiscountInfo, xem lại 3 cái test này
test('lấy thông tin discount thành công', function () {
    $id = 1;
    $databaseName = 'test_db';

    $discount = Mockery::mock('App\Models\Discount');
    $discount->shouldReceive('toArray')
        ->andReturn([
            'id' => $id,
            'name' => 'Test Discount',
            'started_at' => '2023-01-01',
            'expired_at' => '2023-12-31',
            'type' => 'percentage',
            'value' => 10,
            'usage_limit' => 100,
            'trial_days' => 30,
            'created_at' => '2022-12-01',
            'updated_at' => '2022-12-01',
        ]);

    $this->discountRepository->shouldReceive('findById')
        ->once()
        ->with($id, $databaseName)
        ->andReturn($discount);

    $result = $this->discountService->getDiscountInfo($id, $databaseName);

    expect($result)->toBeArray()
        ->toHaveKeys(['id', 'name', 'started_at', 'expired_at', 'type', 'value', 'usage_limit', 'trial_days'])
        ->and($result['id'])->toBe($id)
        ->and($result['name'])->toBe('Test Discount')
        ->and($result['type'])->toBe('percentage');
});
