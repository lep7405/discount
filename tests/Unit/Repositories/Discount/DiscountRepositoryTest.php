<?php

namespace Tests\Unit\Repositories\Discount;

use App\Models\Coupon;
use App\Models\Discount;
use App\Repositories\Coupon\CouponRepository;
use App\Repositories\Discount\DiscountRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

//it('create discount', function () {
//    // Mock DiscountRepository
//    $mockRepository = mock(DiscountRepository::class);
//
//    // Giả lập phương thức createDiscount
//    $mockRepository->shouldReceive('createDiscount')
//        ->once()
//        ->andReturn(new Discount([
//            'name' => 'Discount 20%',
//            'value' => 120,
//            'type' => 'percentage',
//            'started_at' => '2025-01-01',
//            'expired_at' => '2025-12-31',
//            'usage_limit' => 100,
//            'trial_days' => 30,
//            'discount_month' => 6,
//        ]));
//
//    // Kiểm tra xem phương thức có trả về đối tượng Discount không
//    $discount = $mockRepository->createDiscount([
//        'name' => 'Discount 20%11',
//        'value' => 120,
//        'type' => 'percentage',
//        'started_at' => '2025-01-01',
//        'expired_at' => '2025-12-31',
//        'usage_limit' => 100,
//        'trial_days' => 30,
//        'discount_month' => 6,
//    ], 'cs');
//
//    expect($discount)->toBeInstanceOf(Discount::class);
//});
//beforeEach(function(){
//    $this->discountRepository=app(DiscountRepository::class);
//});
//
//test('create discount',function(){
//    $data=
//        [
//            'name' => 'Discount 20%',
//            'value' => 120,
//            'type' => 'percentage',
//            'started_at' => '2025-01-01',
//            'expired_at' => '2025-12-31',
//            'usage_limit' => 100,
//            'trial_days' => 30,
//            'discount_month' => 6,
//        ];
//    $discount=$this->repository->createDiscount($data,'cs');
//    $this->assertInstanceOf(Discount::class, $discount);
//});
class DiscountRepositoryTest extends TestCase
{
    use RefreshDatabase;
    private DiscountRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(DiscountRepository::class);
        $this->couponRepository = app(CouponRepository::class);
        Coupon::on('cs')->delete();
        Discount::on('cs')->delete();
    }

    /**
     * @test
     */
    public function create_discount()
    {
        $data =
            [
                'name' => 'Discount 20%',
                'value' => 120,
                'type' => 'percentage',
                'started_at' => '2025-01-01',
                'expired_at' => '2025-12-31',
                'usage_limit' => 100,
                'trial_days' => 30,
                'discount_month' => 6,
            ];
        $discount = $this->repository->createDiscount($data, 'cs');
        $this->assertInstanceOf(Discount::class, $discount);
    }

    /**
     * @test
     */
    public function update_discount()
    {
        $data =
            [
                'name' => 'Discount 20%',
                'value' => 120,
                'type' => 'percentage',
                'started_at' => '2025-01-01',
                'expired_at' => '2025-12-31',
                'usage_limit' => 100,
                'trial_days' => 30,
                'discount_month' => 6,
            ];
        $discount = $this->repository->createDiscount($data, 'cs');
        $dataUpdate =
            [
                'name' => 'Discount 50%',
                'value' => 120,
                'type' => 'percentage',
                'started_at' => '2025-01-01',
                'expired_at' => '2025-12-31',
                'usage_limit' => 100,
                'trial_days' => 30,
                'discount_month' => 6,
            ];
        $discountUpdate = $this->repository->updateDiscount($dataUpdate, $discount->id, 'cs');
        $this->assertDatabaseMissing('discounts', [
            'id' => $discount->id,
            'name' => 'Discount 20%',
        ], 'cs');
        $this->assertDatabaseHas('discounts', [
            'id' => $discount->id,
            'name' => 'Discount 50%',
        ], 'cs');
    }

    /**
     * @test
     */
    public function delete_discount()
    {
        $data =
            [
                'name' => 'discount1',
                'type' => 'amount',
                'value' => 50,
            ];
        $discount = Discount::on('cs')->create($data);
        $result = $this->repository->deleteDiscount($discount->id, 'cs');
        $this->assertEquals(1, $result);
        $this->assertDatabaseMissing('discounts', [
            'id' => $discount->id,
        ], 'cs');
    }

    /**
     * @test
     */
    public function find_discount_by_id_with_coupon()
    {
        $data = [
            'name' => 'Discount 20%',
            'value' => 120,
            'type' => 'percentage',
            'started_at' => '2025-01-01',
            'expired_at' => '2025-12-31',
            'usage_limit' => 100,
            'trial_days' => 30,
            'discount_month' => 6,
        ];
        $discount = $this->repository->createDiscount($data, 'cs');
        $coupon = $this->couponRepository->createCoupon([
            'code' => 'DISCOUNT20',
            'shop' => 'shop1',
            'discount_id' => $discount->id,
        ], 'cs');
        $discountWithCoupon = $this->repository->findDiscountByIdWithCoupon($discount->id, 'cs');
        $this->assertInstanceOf(Discount::class, $discountWithCoupon);
        $this->assertInstanceOf(Coupon::class, $discountWithCoupon->coupon->first());
        $this->assertEquals($coupon->id, $discountWithCoupon->coupon->first()->id);
        $this->assertEquals('DISCOUNT20', $discountWithCoupon->coupon->first()->code);
    }

    /**
     * @test
     */
    public function test_get_all_discounts_return_paginated_results()
    {
        DB::connection('cs')->table('discounts')->insert([
            [
                'name' => 'Discount 20%',
                'type' => 'percentage',
            ], [
                'name' => 'Discount 20%',
                'type' => 'percentage',
            ],
            [
                'name' => 'Discount 20%',
                'type' => 'percentage',
            ],
        ]);
        //            Discount::factory()->count(10)->create();
        $filters = [
            'per_page_discount' => 10,
        ];
        $result = $this->repository->getAllDiscounts($filters, 'cs');
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(3, $result->total());
    }

    //        public function test_get_all_discounts_by_search(){
    //            DB::connection('cs')->table('discounts')->insert([
    //                [
    //                    'name' => 'Discount 20%',
    //                    'type'=>'percentage',
    //                ],[
    //                    'name' => 'Discount 20%',
    //                    'type'=>'percentage',
    //                ],
    //                [
    //                    'name' => 'Discount 20%',
    //                    'type'=>'percentage',
    //                ]
    //            ]);
    //            $filters=[
    //                'search_discount'=>''
    //            ];
    //            $this->repository->getAllDiscounts($filters,'cs');
    //            $this->
    //        }
    /**
 * @test
 */
    public function get_all_discounts_by_search()
    {
        // Kết nối đến database 'cs' và thêm dữ liệu test
        DB::connection('cs')->table('discounts')->insert([
            [
                'name' => 'Discount 20%',
                'type' => 'percentage',
                'started_at' => '2023-10-01',
                'expired_at' => '2023-10-31',
            ],
            [
                'name' => 'Discount 30%',
                'type' => 'percentage',
                'started_at' => '2023-11-01',
                'expired_at' => '2023-11-30',
            ],
            [
                'name' => 'Discount 50%',
                'type' => 'percentage',
                'started_at' => '2023-12-01',
                'expired_at' => '2023-12-31',
            ],
        ]);

        // Trường hợp 1: Khi search_discount rỗng
        $filters = ['search_discount' => ''];
        $result = $this->repository->getAllDiscounts($filters, 'cs');
        $this->assertCount(3, $result); // Kiểm tra trả về tất cả 3 discount

        // Trường hợp 2: Khi search_discount có giá trị khớp với name
        $filters = ['search_discount' => '50%'];
        $result = $this->repository->getAllDiscounts($filters, 'cs');
        $this->assertCount(1, $result); // Kiểm tra chỉ trả về 1 discount có name chứa '20%'
        $this->assertEquals('Discount 50%', $result->first()->name); // Kiểm tra name của discount

        //        // Trường hợp 3: Khi search_discount là một số (tìm kiếm theo id)
        //        $filters = ['search_discount' => '1']; // Giả sử id của discount đầu tiên là 1
        //        $result = $this->repository->getAllDiscounts($filters, 'cs');
        //        $this->assertCount(1, $result); // Kiểm tra chỉ trả về 1 discount có id = 1
        //        $this->assertEquals(1, $result->first()->id); // Kiểm tra id của discount

        //         Trường hợp 4: Khi search_discount khớp với started_at hoặc expired_at
        $filters = ['search_discount' => '2023-10'];
        $result = $this->repository->getAllDiscounts($filters, 'cs');
        $this->assertCount(1, $result); // Kiểm tra chỉ trả về 1 discount có started_at hoặc expired_at chứa '2023-10'
        $this->assertEquals('2023-10-01 00:00:00', $result->first()->started_at); // Kiểm tra started_at của discount
    }
}
