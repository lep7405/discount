<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    DB::connection('cs')->table('coupons')->whereIn('discount_id', [100, 101])->delete();
    DB::connection('cs')->table('discounts')->whereIn('id', [100, 101])->delete();
    DB::connection('cs')->table('discounts')->insert([
        [
            'id' => 100,
            'name' => 'Discount 100',
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
            'id' => 101,
            'name' => 'Discount 101',
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
    ]);
    DB::connection()->table('generates')->insert([
        [
            'id' => 100,
            'app_name' => 'cs',
            'app_url' => 'http://localhost:8000/generates_new',
            'discount_id' => 100,
            'expired_range' => 14,
        ],
    ]);

    DB::connection('cs')->table('coupons')->insert([
        [
            'id' => 100,
            'code' => 'GENAUTO12345',
            'discount_id' => 100, // Còn hạn
            'shop' => 'shop1',
        ],
    ]);

});
test('authenticated user can access generate update page', function () {
    $response = $this->get(route('admin.get_edit_generate', [
        'id' => 100,
    ]));
    $response->assertStatus(200);
    $response->assertViewIs('admin.generates.update');
    $response->assertViewHasAll([
        'discountData',
        'apps',
        'generate',
        'status_del',
        'generate_url',
        'current_discountDB',
        'private_generate_url',
    ]);
});

test('authenticated user create generate ', function () {
    $response = $this->post(route('admin.post_edit_generate', [
        'id' => 100,
    ]));
});
