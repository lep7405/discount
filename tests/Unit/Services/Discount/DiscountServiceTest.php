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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class DiscountServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        //        $this->artisan('migrate')->run();
        $this->discountRepository = Mockery::mock(DiscountRepository::class);
        $this->couponRepository = Mockery::mock(CouponRepository::class);
        $this->discountService = app()->instance(DiscountService::class, new DiscountServiceImp($this->discountRepository, $this->couponRepository));
        Coupon::on('cs')->delete();
        Discount::on('cs')->delete();
    }

    /**
     * @test
     */
    public function get_all_discounts_fails_when_started_at_not_in_asc_or_desc()
    {
        $filters = [
            'started_at' => 'desss',
        ];
        $this->discountRepository->shouldReceive('countDiscount')
            ->once()
            ->andReturn(5);
        expect(function () use ($filters) {
            $this->discountService->index($filters, 'cs');
        })->toThrow(function (DiscountException $e) {
            expect($e->getErrors()['error'])->toBe('Invalid started_at');
        });
    }

    /**
     * @test
     */
    public function update_discount_fails_when_discount_not_found()
    {
        $this->discountRepository->shouldReceive('findDiscountByIdWithCoupon')->once()->with(100000000, 'cs')->andReturn(null);
        expect(function () {
            $this->discountService->update(100000000, [], 'cs');
        })->toThrow(NotFoundException::class, 'Discount not found');
    }

    /**
     * @test
     */
    public function update_discount_fails_when_discount_has_coupon_used_can_not_update_type_value_trial_days()
    {
        $discount = Discount::on('cs')->create([
            'name' => 'discount1',
            'type' => 'amount',
            'value' => 50,
        ]);
        $coupon = Coupon::factory()->create([
            'discount_id' => $discount->id,
            'times_used' => 1,
        ]);
        $this->discountRepository
            ->shouldReceive('findDiscountByIdWithCoupon')
            ->once()
            ->with($discount->id, 'cs')
            ->andReturn($discount);
        $data = [
            'name' => 'Updated Discount Again',
            'type' => 'amount',
            'value' => 50,
            'trial_days' => 15,
            'discount_for_x_month' => 1,
        ];
        expect(function () use ($data, $discount) {
            $this->discountService->update($discount->id, $data, 'cs');
        })->toThrow(function (DiscountException $e) {
            expect($e->getErrors()['error'][0])->toBe('Cannot update type, value, trial_days, discount_for_x_month after discount is used.');
        });
    }

    /**
     * @test
     */
    public function update_discount_success_with_discount_has_coupon_used()
    {
        $discount = Discount::on('cs')->create([
            'name' => 'discount1',
            'type' => 'amount',
            'value' => 50,
        ]);
        $coupon = Coupon::factory()->create([
            'discount_id' => $discount->id,
            'times_used' => 1,
        ]);
        $this->discountRepository
            ->shouldReceive('findDiscountByIdWithCoupon')
            ->once()
            ->with($discount->id, 'cs')
            ->andReturn($discount);
        $data = [
            'name' => 'Updated Discount Again',
            'usage_limit' => 100,
        ];
        $updatedDiscount = $discount->fill($data);
        $this->discountRepository
            ->shouldReceive('updateDiscount')
            ->once()
            ->with($data, $discount->id, 'cs')
            ->andReturn($updatedDiscount);

        $result = $this->discountService->update($discount->id, $data, 'cs');
        $this->assertEquals(100, $result->usage_limit);
    }

    /**
     * @test
     */
    public function update_discount_success_with_discount_not_has_coupon_used()
    {
        $discount = Discount::on('cs')->create([
            'name' => 'discount1',
            'type' => 'amount',
            'value' => 50,
        ]);
        $this->discountRepository
            ->shouldReceive('findDiscountByIdWithCoupon')
            ->once()
            ->with($discount->id, 'cs')
            ->andReturn($discount);
        $data = [
            'name' => 'Updated Discount Again',
            'type' => 'percentage',
        ];
        $this->discountRepository
            ->shouldReceive('updateDiscount')
            ->once()
            ->with($data, $discount->id, 'cs')
            ->andReturn((object) array_merge($discount->toArray(), $data));

        $result = $this->discountService->update($discount->id, $data, 'cs');
        $this->assertEquals('percentage', $result->type);
    }

    /**
     * @test
     */
    public function update_discount_success_with_discount_has_not_discount_used_and_discount_for_x_month_0()
    {
        //        $discount = DB::connection('affiliate')->table('discounts')->insert([
        //            'name' => 'DISCOUNT123',
        //            'type' => 'amount',
        //            'value' => 100,
        //            'discount_month' => 10,
        //            // Các trường khác
        //        ]);
        $discount = Discount::on('affiliate')->create([
            'name' => 'DISCOUNT123',
            'type' => 'amount',
            'value' => 100,
            'discount_month' => 10,
            // Các trường khác
        ]);
        $this->discountRepository
            ->shouldReceive('findDiscountByIdWithCoupon')
            ->once()
            ->with($discount->id, 'affiliate')
            ->andReturn($discount);

        $data = [
            'name' => 'Updated Discount Again',
            'type' => 'percentage',
            'discount_month' => null,
        ];
        $dataService = [
            'name' => 'Updated Discount Again',
            'type' => 'percentage',
            'discount_for_x_month' => '0',
        ];
        $this->discountRepository
            ->shouldReceive('updateDiscount')
            ->once()
            ->with($data, $discount->id, 'affiliate')
            ->andReturn(true);

        $result = $this->discountService->update($discount->id, $dataService, 'affiliate');
        $this->assertEquals(true, $result);
        //        $this->assertEquals(null, $result->discount_month);
    }

    /**
     * @test
     */
    public function delete_discount_fails_when_discount_not_found()
    {
        $this->discountRepository->shouldReceive('findDiscountByIdWithCoupon')->once()->with(100000000, 'cs')->andReturn(null);
        expect(function () {
            $this->discountService->delete(100000000, 'cs');
        })->toThrow(NotFoundException::class, 'Discount not found');
    }

    /**
     * @test
     */
    public function delete_discount_fails_when_discount_has_coupon_used()
    {
        $discount = Discount::on('cs')->create([
            'name' => 'discount1',
            'type' => 'amount',
            'value' => 50,
        ]);
        $coupon = Coupon::on('cs')->create([
            'code' => 'code12',
            'discount_id' => $discount->id,
            'times_used' => 1,
        ]);
        $this->discountRepository
            ->shouldReceive('findDiscountByIdWithCoupon')
            ->once()
            ->with($discount->id, 'cs')
            ->andReturn($discount);
        expect(function () use ($discount) {
            $this->discountService->delete($discount->id, 'cs');
        })->toThrow(function (DiscountException $e) {
            expect($e->getErrors()['error'][0])->toBe('Can not delete discount');
        });
    }

    /**
     * @test
     */
    public function delete_discount_success()
    {
        $discount = Discount::on('cs')->create([
            'name' => 'discount1',
            'type' => 'amount',
            'value' => 50,
        ]);
        $coupon = Coupon::on('cs')->create([
            'code' => 'code123',
            'discount_id' => $discount->id,
            'times_used' => 0,
        ]);

        $this->discountRepository
            ->shouldReceive('findDiscountByIdWithCoupon')
            ->once()
            ->with($discount->id, 'cs')
            ->andReturn($discount);
        $this->couponRepository
            ->shouldReceive('deleteCouponByDiscountId')
            ->once()
            ->with($discount->id, 'cs')
            ->andReturn(1);
        $this->discountRepository
            ->shouldReceive('deleteDiscount')
            ->once()
            ->with($discount->id, 'cs')
            ->andReturn(null);
        $result = $this->discountService->delete($discount->id, 'cs');
        $this->assertEquals(null, $result);
    }

    /**
     * @test
     */
    public function get_discount_info_success()
    {
        $discount = Discount::on('cs')->create([
            'name' => 'discount1',
            'type' => 'amount',
            'value' => 50,
        ]);
        $this->discountRepository
            ->shouldReceive('findDiscountByIdNoCoupon')
            ->once()
            ->with($discount->id, 'cs')
            ->andReturn($discount);
        $result = $this->discountService->getDiscountInfo($discount->id, 'cs');
        $this->assertEquals('discount1', $result['name']);
        $this->assertEquals('amount', $result['type']);
    }

    /**
     * @test
     */
    public function get_discount_info_fails_when_discount_not_found()
    {
        $this->discountRepository
            ->shouldReceive('findDiscountByIdNoCoupon')
            ->once()
            ->with(1000000000, 'cs')
            ->andReturn(null);
        expect(function () {
            $this->discountService->getDiscountInfo(1000000000, 'cs');
        })->toThrow(NotFoundException::class, 'Discount not found');

    }
}

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
