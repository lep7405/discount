<?php

//
// use App\Models\User;
// use App\Models\Generate;
// use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Support\Facades\DB;
//
// uses(RefreshDatabase::class);
//
// beforeEach(function () {
//    $this->user = User::factory()->create();
//    $this->actingAs($this->user);
//    DB::connection('cs')->table('discounts')->whereIn('id', [54, 55])->delete();
//    DB::connection('cs')->table('discounts')->insert([
//        [
//            'id' => 54,
//            'name' => 'Discount 54',
//            'expired_at' => now()->addDays(10), // Còn hạn
//            'started_at' => now()->subDays(10),
//            'type' => 'percentage',
//            'value' => 15,
//            'usage_limit' => 10,
//            'trial_days' => 5,
//            'discount_month' => 2,
//            'created_at' => now(),
//            'updated_at' => now(),
//        ],
//        [
//            'id' => 55,
//            'name' => 'Discount 55',
//            'expired_at' => now()->subDays(1), // Hết hạn
//            'started_at' => now()->subDays(20),
//            'type' => 'amount',
//            'value' => 10,
//            'usage_limit' => 5,
//            'trial_days' => 3,
//            'discount_month' => 1,
//            'created_at' => now(),
//            'updated_at' => now(),
//        ],
//    ]);
//
//    DB::connection('cs')->table('coupons')->whereIn('id', [100])->delete();
//    DB::connection('cs')->table('coupons')->insert([
//        'id'=>100,
//        'code'=>'GenAuto100',
//        'shop'=>'shop1',
//        'discount_id'=>55,
//        'times_used'=>0,
//    ]);
//    $this->generate= Generate::factory()->create();
// });

// test('generate coupon success',function(){
//    $generate_id = 123;
//    $timestamp = now()->timestamp;
//    $shop_id = 456;
//
//    $response = $this->get(route('generate.url.coupon', [
//        'generate_id' => $generate_id,
//        'timestamp' => $timestamp,
//        'shop_id' => $shop_id
//    ]));
// });
