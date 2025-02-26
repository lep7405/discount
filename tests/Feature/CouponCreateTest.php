<?php

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    DB::connection('cs')->table('coupons')->where('id', 100)->delete();
    Db::connection('cs')->table('coupons')->insert([
        'id' => 100,
        'code' => 'code100',
        'shop' => 'shop100',
        'discount_id' => 1,
    ]);
});
test('coupon : access coupon page success', function () {
    $response = $this->get(route('admin.cs.coupons'));
    $response->assertStatus(200);
    $response->assertViewIs('admin.coupons.index');
});
test('create-coupon success', function () {
    DB::connection('cs')->table('coupons')->where('code', 'code101')->delete();
    $data = [
        'code' => 'code101',
        'shop' => 'shop1',
        'discount_id' => '1',
    ];
    $response = $this->post(route('admin.cs.create_coupon'), $data);
    $response->assertStatus(302);
    $response->assertSessionHas('success');
});
test('create-coupon fails when required fields are missing', function () {
    $response = $this->post(route('admin.cs.post_new_coupon'), []);
    $response->assertStatus(302)->assertSessionHasErrors(['code', 'discount_id']);
});

test('create-coupon fails when code not unique', function () {
    DB::connection('cs')->table('coupons')->insert([
        'code' => 'code101',
        'shop' => 'shop1',
        'discount_id' => '1',
    ]);
    $response = $this->post(route('admin.cs.store_coupon'), [
        'code' => 'code101',
        'shop' => 'shop1',
        'discount_id' => '1',
    ]);
    $response->assertStatus(302)->assertSessionHasErrors(['code']);
});

test('create-coupon fails when discount_id not found', function () {
    $response = $this->post(route('admin.cs.store_coupon'), [
        'code' => 'code101',
        'shop' => 'shop1',
        'discount_id' => '10000',
    ]);
    $response->assertStatus(302)->assertSessionHasErrors(['discount_id']);
});
