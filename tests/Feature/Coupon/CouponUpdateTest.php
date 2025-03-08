<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    DB::connection('cs')->table('coupons')->where('id', 100)->delete();
    Db::connection('cs')->table('coupons')->insert([
        [
            'id' => 100,
            'code' => 'code100',
            'shop' => 'shop100',
            'discount_id' => 1,
            'times_used' => 1,
        ], [
            'id' => 500,
            'code' => 'code100',
            'shop' => 'shop100',
            'discount_id' => 1,
            'times_used' => 1,
        ],
    ]);
});

test('update-coupon success', function () {
    DB::connection('cs')->table('coupons')->where('code', 'code101')->delete();
    $data = [
        'code' => 'code101',
        'shop' => 'shop1',
        'discount_id' => '1',
    ];
    $response = $this->put(route('admin.cs.update_coupon', ['id' => 500]), $data);
    $response->assertStatus(302);
    $response->assertSessionHas('success');
});
test('update-coupon fails when coupon id not found', function () {
    $data = [
        'code' => 'code101',
        'shop' => 'shop1',
        'discount_id' => '1',
    ];
    $response = $this->put(route('admin.cs.update_coupon', ['id' => 1000]), $data);
    $response->assertStatus(302);
    $response->assertSessionHasErrors('error');
});
test('update-coupon fails when code is exists', function () {
    DB::connection('cs')->table('coupons')->where('id', '101')->delete();
    DB::connection('cs')->table('coupons')->insert([
        'id' => 101,
        'code' => 'code102',
        'shop' => 'shop100',
        'discount_id' => 1,
        'times_used' => 1,
    ]);
    $data = [
        'code' => 'code102',
        'shop' => 'shop1',
        'discount_id' => '1',
    ];
    $response = $this->put(route('admin.cs.update_coupon', ['id' => 100]), $data);
    $response->assertStatus(302)->assertSessionHasErrors('error');
});
test('update-coupon fails when times_used >0', function () {
    DB::connection('cs')->table('coupons')->where('id', '101')->delete();
    DB::connection('cs')->table('coupons')->insert([
        'id' => 101,
        'code' => 'code105',
        'shop' => 'shop100',
        'discount_id' => 1,
        'times_used' => 1,
    ]);
    $data = [
        'code' => 'code111111',
        'shop' => 'shop1',
        'discount_id' => '1',
    ];

    $response = $this->put(route('admin.cs.update_coupon', ['id' => 101]), $data);
    $response->assertStatus(302)->assertSessionHasErrors('error');
});

test('change times_used success', function () {
    $data = [
        'numDecrement' => 1,
    ];
    DB::connection('cs')->table('coupons')->where('id', '102')->delete();
    DB::connection('cs')->table('coupons')->insert([
        'id' => 102,
        'code' => 'code105',
        'shop' => 'shop100',
        'discount_id' => 1,
        'times_used' => 1,
    ]);
    $response = $this->put(route('admin.cs.decrement_times_used_coupon', ['id' => 102]), $data);
    $response->assertStatus(302);
    $response->assertSessionHas('success');
});

test('change times_used fails when times_used < numDecrement', function () {
    $data = [
        'numDecrement' => 2,
    ];
    DB::connection('cs')->table('coupons')->where('id', '102')->delete();
    DB::connection('cs')->table('coupons')->insert([
        'id' => 102,
        'code' => 'code105',
        'shop' => 'shop100',
        'discount_id' => 1,
        'times_used' => 1,
    ]);
    $response = $this->put(route('admin.cs.decrement_times_used_coupon', ['id' => 102]), $data);
    $response->assertStatus(302);
    //    $response->assertSessionHas('success');
});
