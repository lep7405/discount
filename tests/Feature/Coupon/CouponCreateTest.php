<?php

use App\Models\User;
use App\Models\Discount; // Giả sử có model Discount
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Tạo user và đăng nhập
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Tạo discount trước để thỏa mãn ràng buộc khóa ngoại
    $discount = Discount::factory()->create([
        'id' => 1,
        // Thêm các trường bắt buộc khác của Discount nếu cần
    ]);

    // Xóa và chèn dữ liệu coupon với discount_id hợp lệ
    DB::connection('cs')->table('coupons')->where('id', 100)->delete();
    DB::connection('cs')->table('coupons')->insert([
        'id' => 100,
        'code' => 'code100',
        'shop' => 'shop100',
        'discount_id' => $discount->id, // Dùng id từ discount đã tạo
    ]);
});

test('access coupon page succeeds', function () {
    $response = $this->get(route('admin.cs.coupons'));

    $response->assertStatus(200)
        ->assertViewIs('admin.coupons.index');
});

test('create coupon fails when required fields are missing', function () {
    $response = $this->post(route('admin.cs.createCoupon'), []);

    $response->assertStatus(302)
        ->assertSessionHasErrors(['code', 'discount_id']);
});

test('create coupon fails when code is not unique', function () {
    DB::connection('cs')->table('coupons')->insert([
        'code' => 'code101',
        'shop' => 'shop1',
        'discount_id' => 1, // Đã có discount_id = 1 từ beforeEach
    ]);

    $response = $this->post(route('admin.cs.storeCoupon'), [
        'code' => 'code101',
        'shop' => 'shop1',
        'discount_id' => 1,
    ]);

    $response->assertStatus(302)
        ->assertSessionHasErrors(['code']);
});

test('create coupon fails when discount_id is not found', function () {
    $response = $this->post(route('admin.cs.storeCoupon'), [
        'code' => 'code101',
        'shop' => 'shop1',
        'discount_id' => 10000, // discount_id không tồn tại
    ]);

    $response->assertStatus(302)
        ->assertSessionHasErrors(['discount_id']);
});
