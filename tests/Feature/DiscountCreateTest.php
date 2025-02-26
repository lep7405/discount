<?php

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    DB::connection('cs')->table('coupons')->whereIn('discount_id', [100, 101])->delete();
    DB::connection('cs')->table('discounts')->whereIn('id', [100, 101])->delete();
});

test('discount : access create discount page success', function () {
    $response = $this->get(route('admin.cs.discounts'));
    $response->assertStatus(200);
    $response->assertViewIs('admin.discounts.index');
});

test('create-discount success', function () {
    $data = [
        'name' => 'Test Discount',
        'trial_days' => 1,
        'type' => 'percentage',
        'value' => 50,
    ];
    $response = $this->post(route('admin.cs.store_discount'), $data);
    $response->assertRedirect(route('admin.cs.discounts'));
});

test('create-discount fails when required fields are missing', function () {
    $response = $this->post(route('admin.cs.store_discount'), []);

    $response->assertStatus(302)
        ->assertSessionHasErrors(['name', 'type']);
});

test('create-discount fails when type is percentage and value is out of range', function () {
    $data = [
        'name' => 'Invalid Discount',
        'type' => 'percentage',
        'value' => 150,
    ];

    $response = $this->post(route('admin.cs.store_discount'), $data);
    $response->assertStatus(302)
        ->assertSessionHasErrors(['value']);
});
test('create-discount fails when expired_at must be after started_at', function () {
    $data = [
        'name' => 'Invalid Date Discount',
        'type' => 'percentage',
        'value' => 10,
        'started_at' => now()->addDays(5)->toDateString(),
        'expired_at' => now()->addDays(2)->toDateString(),
    ];

    $response = $this->post(route('admin.cs.store_discount'), $data);

    $response->assertStatus(302)
        ->assertSessionHasErrors(['expired_at']);
});

test('craete-discount fails when trial_days can be null but must be integer and non-negative', function () {
    $data = [
        'name' => 'Trial Discount',
        'type' => 'percentage',
        'value' => 10,
        'trial_days' => -1,
    ];
    $response = $this->post(route('admin.cs.store_discount'), $data);
    $response->assertStatus(302)
        ->assertSessionHasErrors(['trial_days']);
});
test('create-discount fails when database in [affiliate,freegift_news] , discount_month is required if discount_for_x_month is 1', function () {
    $data = [
        'name' => 'Monthly Discount',
        'type' => 'percentage',
        'value' => 10,
        'discount_for_x_month' => 1,
        // Thiếu `discount_month`
    ];
    $response = $this->post(route('admin.affiliate.store_discount'), $data);

    $response->assertStatus(302)
        ->assertSessionHasErrors(['discount_month']);
});
test('create-discount success when discount_for_x_month is 1 and discount_month is provided', function () {
    $data = [
        'name' => 'Monthly Discount',
        'type' => 'percentage',
        'value' => 10,
        'discount_for_x_month' => 1,
        'discount_month' => 3, // Provide the discount_month here
    ];

    $response = $this->post(route('admin.affiliate.store_discount'), $data);
    $response->assertStatus(302)
        ->assertRedirect(route('admin.affiliate.discounts'));
});

/** ✅ Test: Nếu `discount_for_x_month` = 0 thì `discount_month` không bắt buộc */
test('create-discount success when database in [affiliate,freegift_news] , discount_month is not required if discount_for_x_month is 0', function () {
    $data = [
        'name' => 'No Monthly Discount',
        'type' => 'percentage',
        'value' => 10,
        'discount_for_x_month' => 0,
    ];

    $response = $this->post(route('admin.affiliate.store_discount'), $data);

    $response->assertStatus(302)
        ->assertRedirect(route('admin.affiliate.discounts'));
});

/** ✅ Test: Kiểm tra dữ liệu được lưu đúng */
test('create-discount check discount is saved correctly in database', function () {
    $data = [
        'name' => 'DB Discount',
        'type' => 'amount',
        'value' => 20,
    ];

    $this->post(route('admin.cs.store_discount'), $data);

    $this->assertDatabaseHas('discounts', [
        'name' => 'DB Discount',
        'type' => 'amount',
        'value' => 20,
    ], 'cs');
});
