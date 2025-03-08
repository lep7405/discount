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
use Mockery;

class CouponServiceTest extends \Tests\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->discountRepository = Mockery::mock(DiscountRepository::class);
        $this->couponRepository = Mockery::mock(CouponRepository::class);
        $this->couponService = app()->instance(CouponService::class, new CouponServiceImp($this->couponRepository, $this->discountRepository));
        Coupon::on('cs')->delete();
        Discount::on('cs')->delete();
    }

    /**
     * @test
     */
    public function update_coupon_fails_when_coupon_not_found()
    {
        $this->couponRepository->shouldReceive('getCouponById')
            ->once()
            ->with(10000000, 'cs')
            ->andReturn(null);

        expect(function () {
            $this->couponService->update([], 10000000, 'cs');
        })->toThrow(NotFoundException::class, 'Coupon not found');
    }

    /**
     * @test
     */
    public function update_coupon_fails_when_coupon_used()
    {
        $discount = Discount::on('cs')->create([
            'name' => 'discount1',
            'type' => 'percentage',
        ]);
        $coupon = Coupon::on('cs')->create(
            [
                'code' => '1',
                'discount_id' => $discount->id,
                'times_used' => 1,
            ]
        );
        $this->couponRepository->shouldReceive('getCouponById')
            ->once()
            ->with($coupon->id, 'cs')
            ->andReturn($coupon);
        expect(function () use ($coupon) {
            $this->couponService->update([], $coupon->id, 'cs');
        })->toThrow(function (CouponException $e) {
            expect($e->getErrors()['error'][0])->tobe('Coupon can not update');
        });
    }

    /**
     * @test
     */
    public function update_coupon_fails_when_new_code_has_exist()
    {
        $discount = Discount::on('cs')->create([
            'name' => 'discount1',
            'type' => 'percentage',
        ]);
        $coupon = Coupon::on('cs')->create(
            [
                'code' => '1',
                'discount_id' => $discount->id,
            ]
        );
        $dataUpdate = [
            'code' => '1',
            'discount_id' => $discount->id,
        ];
        $this->couponRepository->shouldReceive('getCouponById')
            ->once()
            ->with($coupon->id, 'cs')
            ->andReturn($coupon);
        $this->couponRepository->shouldReceive('getCouponByCode')
            ->once()
            ->with($dataUpdate['code'], 'cs')
            ->andReturn($coupon);
        expect(function () use ($coupon, $dataUpdate) {
            $this->couponService->update($dataUpdate, $coupon->id, 'cs');
        })->toThrow(function (Exception $e) {
            expect($e->getErrors()['code'][0])->toBe('Code existed');
        });
    }
}
