<?php

namespace Tests\Unit\Repositories\Coupon;

use App\Models\Coupon;
use App\Models\Discount;
use App\Repositories\Coupon\CouponRepository;
use App\Repositories\Discount\DiscountRepository;
use Tests\TestCase;

class CouponRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->discountRepository = app(DiscountRepository::class);
        $this->couponRepository = app(CouponRepository::class);
        Coupon::on('cs')->delete();
        Discount::on('cs')->delete();
    }

    /**
     * @test
     */
    public function delete_coupon_by_discount_id()
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
        $coupon2 = Coupon::on('cs')->create([
            'code' => 'code1234',
            'discount_id' => $discount->id,
            'times_used' => 0,
        ]);
        $result = $this->couponRepository->deleteCouponByDiscountId($discount->id, 'cs');
        $this->assertEquals(2, $result); //check số lượng coupon bị xóa
    }
}
