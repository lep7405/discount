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

uses(\Tests\TestCase::class);

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

    // Create a proper Mockery mock object instead of stdClass
    $mockDiscount = Mockery::mock('stdClass');

    // Set properties on the mock
    $mockDiscount->id = $id;
    $mockDiscount->name = 'Test Discount';
    $mockDiscount->started_at = '2023-01-01';
    $mockDiscount->expired_at = '2023-12-31';
    $mockDiscount->type = 'percentage';
    $mockDiscount->value = 10;
    $mockDiscount->usage_limit = 100;
    $mockDiscount->trial_days = 30;
    $mockDiscount->created_at = '2022-12-01';
    $mockDiscount->updated_at = '2022-12-01';
    $mockDiscount->some_other_field = 'Không cần thiết';

    // Now you can set expectations on the Mockery mock
    $mockDiscount->shouldReceive('toArray')
        ->once()
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
            'some_other_field' => 'Không cần thiết',
        ]);

    // Mock repository to return the mock discount
    $this->discountRepository->shouldReceive('findByIdWithoutCoupon')
        ->once()
        ->with($id, $databaseName)
        ->andReturn($mockDiscount);

    // Call the method under test
    $result = $this->discountService->getDiscountInfo($id, $databaseName);

    // Assert the result
    expect($result)->toBeArray()
        ->toHaveKeys(['id', 'name', 'started_at', 'expired_at', 'type', 'value', 'usage_limit', 'trial_days'])
        ->not->toHaveKeys(['created_at', 'updated_at', 'some_other_field'])
        ->and($result['id'])->toBe($id)
        ->and($result['name'])->toBe('Test Discount')
        ->and($result['type'])->toBe('percentage');
});

//test('lấy thông tin discount thất bại khi không tìm thấy discount', function () {
//    $id = 999;
//    $databaseName = 'test_db';
//
//    // Mock repository không tìm thấy discount
//    $this->discountRepository->shouldReceive('findDiscountByIdNoCoupon')
//        ->once()
//        ->with($id, $databaseName)
//        ->andReturnNull();
//
//    // Kiểm tra exception khi không tìm thấy discount
//    expect(fn () => $this->discountService->getDiscountInfo($id, $databaseName))
//        ->toThrow(NotFoundException::class, 'Discount not found');
//});
//
//test('lấy thông tin discount chỉ trả về các trường cần thiết', function () {
//    $id = 1;
//    $databaseName = 'test_db';
//
//    // Mock dữ liệu discount với nhiều trường
//    $mockDiscount = (object) [
//        'id' => $id,
//        'name' => 'Test Discount',
//        'started_at' => '2023-01-01',
//        'expired_at' => '2023-12-31',
//        'type' => 'amount',
//        'value' => 50,
//        'usage_limit' => 50,
//        'trial_days' => 15,
//        'created_at' => '2022-12-01',
//        'updated_at' => '2023-01-15',
//        'status' => true,
//        'discount_for_x_month' => 1,
//        'discount_month' => 3,
//        'metadata' => '{"author": "admin"}',
//    ];
//
//    // Thiết lập phương thức toArray() cho mock object
//    $mockDiscount->shouldReceive('toArray')
//        ->once()
//        ->andReturn([
//            'id' => $id,
//            'name' => 'Test Discount',
//            'started_at' => '2023-01-01',
//            'expired_at' => '2023-12-31',
//            'type' => 'amount',
//            'value' => 50,
//            'usage_limit' => 50,
//            'trial_days' => 15,
//            'created_at' => '2022-12-01',
//            'updated_at' => '2023-01-15',
//            'status' => true,
//            'discount_for_x_month' => 1,
//            'discount_month' => 3,
//            'metadata' => '{"author": "admin"}',
//        ]);
//
//    // Mock repository trả về discount
//    $this->discountRepository->shouldReceive('findDiscountByIdNoCoupon')
//        ->once()
//        ->with($id, $databaseName)
//        ->andReturn($mockDiscount);
//
//    // Gọi phương thức cần test
//    $result = $this->discountService->getDiscountInfo($id, $databaseName);
//
//    // Kiểm tra kết quả chỉ chứa các trường được chỉ định
//    expect($result)->toBeArray()
//        ->toHaveCount(8)
//        ->toHaveKeys(['id', 'name', 'started_at', 'expired_at', 'type', 'value', 'usage_limit', 'trial_days'])
//        ->not->toHaveKeys([
//            'created_at', 'updated_at', 'status',
//            'discount_for_x_month', 'discount_month', 'metadata',
//        ]);
//});













//
//test('update ném ra NotFoundException khi không tìm thấy discount', function () {
//    $id = 999;
//    $databaseName = 'test_db';
//    $attributes = ['name' => 'Giảm giá không tồn tại'];
//
//    $this->discountRepository->shouldReceive('findDiscountByIdWithCoupon')
//        ->once()
//        ->with($id, $databaseName)
//        ->andReturn(null);
//
//    expect(fn () => $this->discountService->update($id, $attributes, $databaseName))
//        ->toThrow(NotFoundException::class, 'Discount not found');
//});
//
//test('update ném ra ValidationException khi dữ liệu không hợp lệ', function () {
//    $id = 1;
//    $databaseName = 'test_db';
//    $attributes = ['value' => -10]; // Giá trị không hợp lệ
//
//    $discount = Mockery::mock(Discount::class);
//    $discount->coupon = collect([]);
//
//    $this->discountRepository->shouldReceive('findDiscountByIdWithCoupon')
//        ->once()
//        ->with($id, $databaseName)
//        ->andReturn($discount);
//
//    // Mock UpdateDiscountValidator để ném ra ValidationException
//    Mockery::mock('alias:' . UpdateDiscountValidator::class)
//        ->shouldReceive('validateEdit')
//        ->once()
//        ->andThrow(DiscountException::class);
//
//    expect(fn () => $this->discountService->update($id, $attributes, $databaseName))
//        ->toThrow(DiscountException::class);
//});

//class DiscountServiceTest extends TestCase
//{
//    use RefreshDatabase;
//
//    protected function setUp(): void
//    {
//        parent::setUp();
//        //        $this->artisan('migrate')->run();
//        $this->discountRepository = Mockery::mock(DiscountRepository::class);
//        $this->couponRepository = Mockery::mock(CouponRepository::class);
//        $this->discountService = app()->instance(DiscountService::class, new DiscountServiceImp($this->discountRepository, $this->couponRepository));
//        Coupon::on('cs')->delete();
//        Discount::on('cs')->delete();
//    }
//
//    /**
//     * @test
//     */
//    public function get_all_discounts_fails_when_started_at_not_in_asc_or_desc()
//    {
//        $filters = [
//            'started_at' => 'desss',
//        ];
//        $this->discountRepository->shouldReceive('countDiscount')
//            ->once()
//            ->andReturn(5);
//        expect(function () use ($filters) {
//            $this->discountService->index($filters, 'cs');
//        })->toThrow(function (DiscountException $e) {
//            expect($e->getErrors()['error'])->toBe('Invalid started_at');
//        });
//    }
//
//    /**
//     * @test
//     */
//    public function update_discount_fails_when_discount_not_found()
//    {
//        $this->discountRepository->shouldReceive('findDiscountByIdWithCoupon')->once()->with(100000000, 'cs')->andReturn(null);
//        expect(function () {
//            $this->discountService->update(100000000, [], 'cs');
//        })->toThrow(NotFoundException::class, 'Discount not found');
//    }
//
//    /**
//     * @test
//     */
//    public function update_discount_fails_when_discount_has_coupon_used_can_not_update_type_value_trial_days()
//    {
//        $discount = Discount::on('cs')->create([
//            'name' => 'discount1',
//            'type' => 'amount',
//            'value' => 50,
//        ]);
//        $coupon = Coupon::factory()->create([
//            'discount_id' => $discount->id,
//            'times_used' => 1,
//        ]);
//        $this->discountRepository
//            ->shouldReceive('findDiscountByIdWithCoupon')
//            ->once()
//            ->with($discount->id, 'cs')
//            ->andReturn($discount);
//        $data = [
//            'name' => 'Updated Discount Again',
//            'type' => 'amount',
//            'value' => 50,
//            'trial_days' => 15,
//            'discount_for_x_month' => 1,
//        ];
//        expect(function () use ($data, $discount) {
//            $this->discountService->update($discount->id, $data, 'cs');
//        })->toThrow(function (DiscountException $e) {
//            expect($e->getErrors()['error'][0])->toBe('Cannot update type, value, trial_days, discount_for_x_month after discount is used.');
//        });
//    }
//
//    /**
//     * @test
//     */
//    public function update_discount_success_with_discount_has_coupon_used()
//    {
//        $discount = Discount::on('cs')->create([
//            'name' => 'discount1',
//            'type' => 'amount',
//            'value' => 50,
//        ]);
//        $coupon = Coupon::factory()->create([
//            'discount_id' => $discount->id,
//            'times_used' => 1,
//        ]);
//        $this->discountRepository
//            ->shouldReceive('findDiscountByIdWithCoupon')
//            ->once()
//            ->with($discount->id, 'cs')
//            ->andReturn($discount);
//        $data = [
//            'name' => 'Updated Discount Again',
//            'usage_limit' => 100,
//        ];
//        $updatedDiscount = $discount->fill($data);
//        $this->discountRepository
//            ->shouldReceive('updateDiscount')
//            ->once()
//            ->with($data, $discount->id, 'cs')
//            ->andReturn($updatedDiscount);
//
//        $result = $this->discountService->update($discount->id, $data, 'cs');
//        $this->assertEquals(100, $result->usage_limit);
//    }
//
//    /**
//     * @test
//     */
//    public function update_discount_success_with_discount_not_has_coupon_used()
//    {
//        $discount = Discount::on('cs')->create([
//            'name' => 'discount1',
//            'type' => 'amount',
//            'value' => 50,
//        ]);
//        $this->discountRepository
//            ->shouldReceive('findDiscountByIdWithCoupon')
//            ->once()
//            ->with($discount->id, 'cs')
//            ->andReturn($discount);
//        $data = [
//            'name' => 'Updated Discount Again',
//            'type' => 'percentage',
//        ];
//        $this->discountRepository
//            ->shouldReceive('updateDiscount')
//            ->once()
//            ->with($data, $discount->id, 'cs')
//            ->andReturn((object) array_merge($discount->toArray(), $data));
//
//        $result = $this->discountService->update($discount->id, $data, 'cs');
//        $this->assertEquals('percentage', $result->type);
//    }
//
//    /**
//     * @test
//     */
//    public function update_discount_success_with_discount_has_not_discount_used_and_discount_for_x_month_0()
//    {
//        //        $discount = DB::connection('affiliate')->table('discounts')->insert([
//        //            'name' => 'DISCOUNT123',
//        //            'type' => 'amount',
//        //            'value' => 100,
//        //            'discount_month' => 10,
//        //            // Các trường khác
//        //        ]);
//        $discount = Discount::on('affiliate')->create([
//            'name' => 'DISCOUNT123',
//            'type' => 'amount',
//            'value' => 100,
//            'discount_month' => 10,
//            // Các trường khác
//        ]);
//        $this->discountRepository
//            ->shouldReceive('findDiscountByIdWithCoupon')
//            ->once()
//            ->with($discount->id, 'affiliate')
//            ->andReturn($discount);
//
//        $data = [
//            'name' => 'Updated Discount Again',
//            'type' => 'percentage',
//            'discount_month' => null,
//        ];
//        $dataService = [
//            'name' => 'Updated Discount Again',
//            'type' => 'percentage',
//            'discount_for_x_month' => '0',
//        ];
//        $this->discountRepository
//            ->shouldReceive('updateDiscount')
//            ->once()
//            ->with($data, $discount->id, 'affiliate')
//            ->andReturn(true);
//
//        $result = $this->discountService->update($discount->id, $dataService, 'affiliate');
//        $this->assertEquals(true, $result);
//        //        $this->assertEquals(null, $result->discount_month);
//    }
//
//    /**
//     * @test
//     */
//    public function delete_discount_fails_when_discount_not_found()
//    {
//        $this->discountRepository->shouldReceive('findDiscountByIdWithCoupon')->once()->with(100000000, 'cs')->andReturn(null);
//        expect(function () {
//            $this->discountService->delete(100000000, 'cs');
//        })->toThrow(NotFoundException::class, 'Discount not found');
//    }
//
//    /**
//     * @test
//     */
//    public function delete_discount_fails_when_discount_has_coupon_used()
//    {
//        $discount = Discount::on('cs')->create([
//            'name' => 'discount1',
//            'type' => 'amount',
//            'value' => 50,
//        ]);
//        $coupon = Coupon::on('cs')->create([
//            'code' => 'code12',
//            'discount_id' => $discount->id,
//            'times_used' => 1,
//        ]);
//        $this->discountRepository
//            ->shouldReceive('findDiscountByIdWithCoupon')
//            ->once()
//            ->with($discount->id, 'cs')
//            ->andReturn($discount);
//        expect(function () use ($discount) {
//            $this->discountService->delete($discount->id, 'cs');
//        })->toThrow(function (DiscountException $e) {
//            expect($e->getErrors()['error'][0])->toBe('Can not delete discount');
//        });
//    }
//
//    /**
//     * @test
//     */
//    public function delete_discount_success()
//    {
//        $discount = Discount::on('cs')->create([
//            'name' => 'discount1',
//            'type' => 'amount',
//            'value' => 50,
//        ]);
//        $coupon = Coupon::on('cs')->create([
//            'code' => 'code123',
//            'discount_id' => $discount->id,
//            'times_used' => 0,
//        ]);
//
//        $this->discountRepository
//            ->shouldReceive('findDiscountByIdWithCoupon')
//            ->once()
//            ->with($discount->id, 'cs')
//            ->andReturn($discount);
//        $this->couponRepository
//            ->shouldReceive('deleteCouponByDiscountId')
//            ->once()
//            ->with($discount->id, 'cs')
//            ->andReturn(1);
//        $this->discountRepository
//            ->shouldReceive('deleteDiscount')
//            ->once()
//            ->with($discount->id, 'cs')
//            ->andReturn(null);
//        $result = $this->discountService->delete($discount->id, 'cs');
//        $this->assertEquals(null, $result);
//    }
//
//    /**
//     * @test
//     */
//    public function get_discount_info_success()
//    {
//        $discount = Discount::on('cs')->create([
//            'name' => 'discount1',
//            'type' => 'amount',
//            'value' => 50,
//        ]);
//        $this->discountRepository
//            ->shouldReceive('findDiscountByIdNoCoupon')
//            ->once()
//            ->with($discount->id, 'cs')
//            ->andReturn($discount);
//        $result = $this->discountService->getDiscountInfo($discount->id, 'cs');
//        $this->assertEquals('discount1', $result['name']);
//        $this->assertEquals('amount', $result['type']);
//    }
//
//    /**
//     * @test
//     */
//    public function get_discount_info_fails_when_discount_not_found()
//    {
//        $this->discountRepository
//            ->shouldReceive('findDiscountByIdNoCoupon')
//            ->once()
//            ->with(1000000000, 'cs')
//            ->andReturn(null);
//        expect(function () {
//            $this->discountService->getDiscountInfo(1000000000, 'cs');
//        })->toThrow(NotFoundException::class, 'Discount not found');
//
//    }
//}

//test('update discount fails when discount not found', function () {
//    // Giả lập hành vi findDiscountByIdWithCoupon trả về null
//    $this->discountRepository
//        ->shouldReceive('findDiscountByIdWithCoupon')
//        ->once()
//        ->with(10000000, 'cs')
//        ->andReturn(null);
//
//    // Kiểm tra xem NotFoundException có được ném ra không
//    expect(function () {
//        $this->discountService->update(10000000, [], 'cs');
//    })->toThrow(NotFoundException::class, 'Discount not found');
//});
//
//test('update discount fails when discount status is false and array content type,value,trial_days',function (){
//    $discount = Discount::factory()->create();
//    $coupon = Coupon::factory()->create([
//        'discount_id' => $discount->id,
//        'times_used' => 1,
//    ]);
//    $data = [
//        'name' => 'Updated Discount Again',
//        'type' => 'amount',
//        'value' => 50,
//        'trial_days' => 15,
//        'discount_for_x_month' => 1,
//    ];
//    expect(function () use ($data, $discount) {
//        $this->discountService->update($discount->id, $data, 'cs');
//    })->toThrow(\App\Exceptions\DiscountException::class, 'Cannot update type, value, trial_days, discount_for_x_month after discount is used.');
//});

//use App\Services\Discount\DiscountServiceImp;
//use App\Repositories\Discount\DiscountRepository;
//use App\Repositories\Coupon\CouponRepository;
//use App\Exceptions\DiscountException;
//use App\Models\Discount;
//
//beforeEach(function () {
//    // Mock DiscountRepository và CouponRepository
//    $this->discountRepository = Mockery::mock(DiscountRepository::class);
//    $this->couponRepository = Mockery::mock(CouponRepository::class);
//
//    // Khởi tạo DiscountService với các mock repositories
//    $this->discountService = new DiscountServiceImp($this->discountRepository, $this->couponRepository);
//});
//
//it('should return true when discount is updated successfully', function () {
//    // Thiết lập các tham số giả lập
//    $id = 1;
//    $databaseName = 'test_database';
//    $attributes = [
//        'name' => 'New Discount Name',
//        'started_at' => '2023-01-01',
//        'expired_at' => '2023-12-31',
//        'usage_limit' => 100,
//    ];
//
//    // Mock getDiscountWithCoupon trả về discount hợp lệ
//    $this->discountRepository->shouldReceive('findDiscountByIdWithCoupon')
//        ->once()
//        ->andReturn(mock(Discount::class));
//
//    // Mock getStatusDiscount trả về false (discount chưa được sử dụng)
//    $this->discountService->shouldReceive('getStatusDiscount')
//        ->once()
//        ->andReturn(false);
//
//    // Mock validateEdit (giả lập không có lỗi khi validate)
//    $this->discountService->shouldReceive('validateEdit')
//        ->once()
//        ->andReturn($attributes);
//
//    // Mock updateDiscount trong DiscountRepository
//    $this->discountRepository->shouldReceive('updateDiscount')
//        ->once()
//        ->andReturn(true);
//
//    // Gọi phương thức update
//    $result = $this->discountService->update($id, $attributes, $databaseName);
//
//    // Kiểm tra kết quả trả về
//    expect($result)->toBeTrue();
//});

//it('should throw exception if discount is not found', function () {
//    // Thiết lập tham số giả lập
//    $id = 1;
//    $databaseName = 'test_database';
//    $attributes = ['name' => 'New Discount'];
//
//    // Mock getDiscountWithCoupon trả về null (discount không tìm thấy)
//    $this->discountRepository->shouldReceive('findDiscountByIdWithCoupon')
//        ->once()
//        ->andReturn(null);
//
//    // Gọi phương thức update và kiểm tra ngoại lệ khi discount không tồn tại
//    expect(fn() => $this->discountService->update($id, $attributes, $databaseName))
//        ->toThrow(DiscountException::class, 'Discount not found');
//});
//
//it('should throw exception for invalid attributes', function () {
//    // Thiết lập tham số giả lập
//    $id = 1;
//    $databaseName = 'test_database';
//    $attributes = [
//        'name' => 'New Discount',
//        'started_at' => 'invalid_date',
//    ];
//
//    // Mock getDiscountWithCoupon trả về discount hợp lệ
//    $this->discountRepository->shouldReceive('findDiscountByIdWithCoupon')
//        ->once()
//        ->andReturn(mock(Discount::class));
//
//    // Mock getStatusDiscount trả về false
//    $this->discountService->shouldReceive('getStatusDiscount')
//        ->once()
//        ->andReturn(false);
//
//    // Giả lập validateEdit throw exception khi có lỗi
//    $this->discountService->shouldReceive('validateEdit')
//        ->once()
//        ->andThrow(new DiscountException('Invalid attributes'));
//
//    // Kiểm tra ngoại lệ khi validate không thành công
//    expect(fn() => $this->discountService->update($id, $attributes, $databaseName))
//        ->toThrow(DiscountException::class, 'Invalid attributes');
//});
//
//it('should return false when update fails', function () {
//    // Thiết lập tham số giả lập
//    $id = 1;
//    $databaseName = 'test_database';
//    $attributes = [
//        'name' => 'Updated Discount',
//        'started_at' => '2023-01-01',
//        'expired_at' => '2023-12-31',
//        'usage_limit' => 100,
//    ];
//
//    // Mock getDiscountWithCoupon trả về discount hợp lệ
//    $this->discountRepository->shouldReceive('findDiscountByIdWithCoupon')
//        ->once()
//        ->andReturn(mock(Discount::class));
//
//    // Mock getStatusDiscount trả về false
//    $this->discountService->shouldReceive('getStatusDiscount')
//        ->once()
//        ->andReturn(false);
//
//    // Mock validateEdit trả về các attributes đã được validate
//    $this->discountService->shouldReceive('validateEdit')
//        ->once()
//        ->andReturn($attributes);
//
//    // Mock updateDiscount trả về false (update thất bại)
//    $this->discountRepository->shouldReceive('updateDiscount')
//        ->once()
//        ->andReturn(false);
//
//    // Gọi phương thức update
//    $result = $this->discountService->update($id, $attributes, $databaseName);
//
//    // Kiểm tra kết quả trả về
//    expect($result)->toBeFalse();
//});
