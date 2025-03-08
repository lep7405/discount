<?php

use App\Models\Generate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

//generate 1001 not active , discount 1001 expired ,
uses(RefreshDatabase::class);

//chuẩn bị dữ liệu
beforeEach(function () {
    DB::connection('cs')->table('coupons')->whereIn('id', [2000, 2001])->delete();
    DB::connection('cs')->table('discounts')->whereIn('id', [2000, 2001, 2002])->delete();
    DB::connection('cs')->table('discounts')->insert([
        [
            'id' => 2000,
            'name' => 'Discount test 1000',
            'expired_at' => now()->addDays(10), // Còn hạn
            'started_at' => now()->subDays(10),
            'type' => 'percentage',
            'value' => 15,
            'usage_limit' => 10,
            'trial_days' => 5,
            'discount_month' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 2001,
            'name' => 'Discount test 1001',
            'expired_at' => now()->subDays(5), // Còn hạn
            'started_at' => now()->subDays(10),
            'type' => 'percentage',
            'value' => 15,
            'usage_limit' => 10,
            'trial_days' => 5,
            'discount_month' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 2002,
            'name' => 'Discount test 1002',
            'expired_at' => now()->addDays(5), // Còn hạn
            'started_at' => now()->subDays(10),
            'type' => 'percentage',
            'value' => 15,
            'usage_limit' => 10,
            'trial_days' => 5,
            'discount_month' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
    DB::table('generates')->whereIn('id', [2000, 2001])->delete();
    DB::table('generates')->insert([
        [
            'id' => 2000,
            'app_name' => 'cs',
            'app_url' => 'http://localhost:8000/generates_new',
            'discount_id' => 2000,
            'expired_range' => 14,
            'limit' => 1,
        ],
        [
            'id' => 2001,
            'app_name' => 'cs',
            'app_url' => 'http://localhost:8000/generates_new',
            'discount_id' => 2001,
            'expired_range' => 14,
            'limit' => 1,

        ],
        [
            'id' => 2002,
            'app_name' => 'cs',
            'app_url' => 'http://localhost:8000/generates_new',
            'discount_id' => 2002,
            'expired_range' => 14,
            'limit' => 1,
        ],
    ]);

    DB::connection('cs')->table('coupons')->insert([
        [
            'id' => 2000,
            'code' => 'GENAUTO1000',
            'discount_id' => 2000,
            'shop' => 'shop3.myshopify.com',
            'times_used' => 0,
        ],
        [
            'id' => 2001,
            'code' => 'GENAUTO1001',
            'discount_id' => 2000,
            'shop' => 'shop4.myshopify.com',
            'times_used' => 1,
        ],
    ]);
});

//cần có gì để test , generate
test('Generate coupon fails when generate not found', function () {
    $data = [
        'generate_id' => 5000,
        'timestamp' => 1,
        'shop_id' => 1,
    ];
    $response = $this->get(route('generate.url.coupon', $data));
    $response->assertViewHasAll([
        'header_message' => 'Welcome to Secomapp special offer!',
        'content_message' => 'WHOOPS!',
        'reasons' => 'This offer does not exist!',
    ]);
});

test('Generate coupon fails when generate not active', function () {
    $generate = Generate::query()->where(['id' => 2000])->first();
    $generate->status = 0;
    $generate->save();
    $data = [
        'generate_id' => 2000,
        'timestamp' => 1,
        'shop_id' => 1,
    ];
    $response = $this->get(route('generate.url.coupon', $data));
    $response->assertViewHasAll([
        'header_message' => 'Welcome to Secomapp special offer!',
        'content_message' => 'WHOOPS!',
        'reasons' => 'This offer was disabled!',
        'app_url' => $generate->app_url,
        'generate_id' => $data['generate_id'],
    ]);
});

//test xem shop có coupon trong app chưa , coupon dạng .myshopify.com
//3.1 nếu có coupon có shop là .myshopify.com rồi , cái coupon shop4.myshopify.com có cái times_used>0
test('Generate coupon fails when coupon times_used >0', function () {});
