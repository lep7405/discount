<?php

use App\Models\Generate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

//generate 1001 not active , discount 1001 expired ,
uses(RefreshDatabase::class);
beforeEach(function () {
    DB::connection('cs')->table('coupons')->whereIn('id', [1000, 1001])->delete();
    DB::connection('cs')->table('discounts')->whereIn('id', [1000, 1001, 1002])->delete();
    DB::connection('cs')->table('discounts')->insert([
        [
            'id' => 1000,
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
            'id' => 1001,
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
            'id' => 1002,
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
    DB::table('generates')->whereIn('id', [1000, 1001])->delete();
    DB::table('generates')->insert([
        [
            'id' => 1000,
            'app_name' => 'cs',
            'app_url' => 'http://localhost:8000/generates_new',
            'discount_id' => 1000,
            'expired_range' => 14,
            'limit' => 1,
        ],
        [
            'id' => 1001,
            'app_name' => 'cs',
            'app_url' => 'http://localhost:8000/generates_new',
            'discount_id' => 1001,
            'expired_range' => 14,
            'limit' => 1,

        ],
        [
            'id' => 1002,
            'app_name' => 'cs',
            'app_url' => 'http://localhost:8000/generates_new',
            'discount_id' => 1002,
            'expired_range' => 14,
            'limit' => 1,
        ],
    ]);

    DB::connection('cs')->table('coupons')->insert([
        [
            'id' => 1000,
            'code' => 'GENAUTO1000',
            'discount_id' => 1000,
            'shop' => 'shop1.myshopify.com',
            'times_used' => 0,
        ],
        [
            'id' => 1001,
            'code' => 'GENAUTO1001',
            'discount_id' => 1000,
            'shop' => 'shop2.myshopify.com',
            'times_used' => 1,
        ],
    ]);
});
//test('private generate coupon fails when ip not supported',function(){
//    $data=[
//        'generate_id'=>1,
//        'shop_name'=>'shop1'
//    ];
//   $response=$this->post(route('private.generate.url.coupon',$data));
//   $response->assertStatus(200);
//   $response->assertJson([
//       'status' => false,
//       'message' => 'Not support!',
//   ]);
//});

//1
test('private generate coupon fails when generate not found', function () {
    $data = [
        'generate_id' => 1,
        'shop_name' => 'shop',
    ];
    $response = $this->post(route('private.generate.url.coupon', $data));
    $response->assertStatus(200);
    $response->assertJson([
        'status' => false,
        'message' => 'Generate not exist!',
    ]);
});
//2
test('private generate coupon fails when generate not active', function () {
    $data = [
        'generate_id' => 1001,
        'shop_name' => 'shop',
    ];
    $generate = Generate::query()->where(['id' => 1001])->first();
    $generate->status = 0;
    $generate->save();

    $response = $this->post(route('private.generate.url.coupon', $data));
    $response->assertStatus(200);
    $response->assertJson([
        'status' => false,
        'message' => 'Generate not active!',
    ]);
});
//3.1 nếu có coupon có shop là .myshopify.com rồi , cái coupon shop2.myshopify.com có cái times_used>0
test('private generate coupon fails when coupon times_used >0', function () {
    $data = [
        'generate_id' => 1000,
        'shop_name' => 'shop2',
    ];
    // từ cái shop2 , generate_id=>discount_id tìm được cái coupon2

    $response = $this->post(route('private.generate.url.coupon', $data));
    $response->assertStatus(200);
    $response->assertJson([
        'status' => false,
        'message' => 'Coupon used!',
    ]);
});
//3.2 nếu có coupon có shop là .myshopify.com rồi , cái coupon shop1.myshopify có times_used=0;
test('private generate coupon success when ! coupon times_used >0', function () {
    $data = [
        'generate_id' => 1000,
        'shop_name' => 'shop1',
    ];
    // từ cái shop2 , generate_id=>discount_id tìm được cái coupon2

    $response = $this->post(route('private.generate.url.coupon', $data));
    $response->assertStatus(200);
    $response->assertJson([
        'status' => true,
        'message' => 'Coupon created!',
    ]);
});

//4 cái gen 1001 chưa có coupon nào có shop là shop1 cả
test('private generate coupon fails when discount expired', function () {
    $data = [
        'generate_id' => 1001,
        'shop_name' => 'shop1',
    ];
    $response = $this->post(route('private.generate.url.coupon', $data));
    $response->assertStatus(200);
    $response->assertJson([
        'status' => false,
        'message' => 'Discount Expired!',
    ]);
});
//5 limit là đếm số coupon mà có code dạng GENAUTO% , cái generate 1000 đang có 2 cái copon có code dạng GENAUTO% và không có coupon nào có shop là shop.myshopify.com
//và cái generate 1000 đang có limit coupon là 2
test('private generate coupon fails when generate limit number coupon', function () {
    $data = [
        'generate_id' => 1000,
        'shop_name' => 'shop', //cho nên cái shop_name ở đây thì chưa có trong db là cái shop1,shop2
    ];
    $response = $this->post(route('private.generate.url.coupon', $data));
    $response->assertStatus(200);
    $response->assertJson([
        'status' => false,
        'message' => 'Limit Coupon',
    ]);
});

//6 tạo coupon mới sau khi đủ các điều kiện trên ,gen 1002 đang có limit là 1 và chưa có cái coupon nào
test('private generate coupon when create new coupon success', function () {
    $data = [
        'generate_id' => 1002,
        'shop_name' => 'shop', //cho nên cái shop_name ở đây thì chưa có trong db là cái shop1,shop2
    ];
    $response = $this->post(route('private.generate.url.coupon', $data));
    $response->assertStatus(200);
    $response->assertJson([
        'status' => true,
        'message' => 'Success generate coupon!',
    ]);
});
