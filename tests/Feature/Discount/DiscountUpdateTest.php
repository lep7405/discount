<?php

use App\Models\Coupon;
use App\Models\Discount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Tạo người dùng và đăng nhập vào hệ thống
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Xóa dữ liệu cũ trước khi thêm mới
    DB::connection('cs')->table('coupons')->whereIn('discount_id', [500, 501, 503])->delete();
    DB::connection('cs')->table('discounts')->whereIn('id', [500, 501, 502, 503])->delete();
    DB::connection('affiliate')->table('discounts')->whereIn('id', [502])->delete();
    // Chèn dữ liệu vào bảng discounts
    DB::connection('cs')->table('discounts')->insert([
        [
            'id' => 500,
            'name' => 'Discount 500',
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
            'id' => 501,
            'name' => 'Discount 501',
            'expired_at' => now()->subDays(1), // Hết hạn
            'started_at' => now()->subDays(20),
            'type' => 'amount',
            'value' => 10,
            'usage_limit' => 5,
            'trial_days' => 3,
            'discount_month' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 502,
            'name' => 'Discount 502',
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
            'id' => 503,
            'name' => 'Discount 503',
            'expired_at' => now()->addDays(10), // Còn hạn
            'started_at' => now()->subDays(10),
            'type' => 'amount',
            'value' => 20,
            'usage_limit' => 10,
            'trial_days' => 5,
            'discount_month' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    // Chèn dữ liệu vào bảng coupons (cho các discount có coupon đã sử dụng)
    DB::connection('cs')->table('coupons')->insert([
        [
            'id' => 500,
            'code' => 'COUPON123',
            'discount_id' => 503,
            'times_used' => 1, // Coupon đã được sử dụng
            'shop' => 'shop1',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    // Chèn dữ liệu vào bảng affiliate (để test với discount_id 502)
    DB::connection('affiliate')->table('discounts')->insert([
        [
            'id' => 502,
            'name' => 'Discount 502',
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
    ]);
});

test('update-discount access update discount page success', function () {
    $response = $this->get(route('admin.cs.edit_discount', ['id' => 500]));
    $response->assertStatus(200);
    $response->assertViewIs('admin.discounts.update');
});

test('update discount fails when discount not found', function () {
    $data = [
        'name' => 'Updated Discount',
        'type' => 'percentage',
        'value' => 99,
        'trial_days' => 10,
    ];
    $response = $this->put(route('admin.cs.update_discount', ['id' => 50000]), $data); // ID không tồn tại
    $response->assertSessionHasErrors([
        'error' => ['Discount not found'],
    ]);

});

/** ✅ Trường hợp `discount_status = false` (Không có coupon nào đã sử dụng) */
test('update discount success fields type ,value, trials_days when discount_status is false', function () {
    $data = [
        'name' => 'Updated Discount',
        'type' => 'percentage',
        'value' => 99,
        'trial_days' => 10,
    ];
    $response = $this->put(route('admin.cs.update_discount', ['id' => 501]), $data); // ID tồn tại và discount không có coupon đã sử dụng

    $response->assertStatus(302)
        ->assertRedirect(); // Kiểm tra chuyển hướng sau khi update

    $this->assertDatabaseHas('discounts', [
        'name' => 'Updated Discount',
        'type' => 'percentage',
        'value' => 99,
        'trial_days' => 10,
    ], 'cs');
});

test('update discount DB : affiliate,freegifts_new success fields type ,value, trials_days,discount_month when discount_status is false', function () {
    $data = [
        'name' => 'Updated Discount',
        'type' => 'percentage',
        'value' => 99,
        'trial_days' => 10,
        'discount_for_x_month' => '1',
        'discount_month' => 2,
    ];
    $response = $this->put(route('admin.affiliate.update_discount', ['id' => 502]), $data);

    $response->assertStatus(302)
        ->assertRedirect();

    $this->assertDatabaseHas('discounts', [
        'name' => 'Updated Discount',
        'type' => 'percentage',
        'value' => 99,
        'trial_days' => 10,
        'discount_month' => 2,
    ], 'affiliate');
});

// Thêm coupon với times_used = 1 để test discount_status = true
test('cannot update type, value, or trial_days when discount_status is true', function () {
    $data = [
        'name' => 'Updated Discount Again',
        'type' => 'amount',
        'value' => 50,
        'trial_days' => 15,
        'discount_for_x_month' => 1,
    ];

    $response = $this->put(route('admin.cs.update_discount', ['id' => 503]), $data);
    $response->assertStatus(302)
        ->assertSessionHasErrors('error');
});
