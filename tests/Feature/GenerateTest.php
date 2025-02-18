<?php

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    DB::connection('cs')->table('discounts')->whereIn('id', [50, 52])->delete();
    DB::connection('cs')->table('discounts')->insert([
        [
            'id' => 50,
            'name' => 'Discount 50',
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
            'id' => 52,
            'name' => 'Discount 52',
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
});

test('authenticated user can access generate index page', function () {
    $response = $this->get(route('admin.get_generate'));

    $response->assertStatus(200);
    $response->assertViewIs('admin.generates.index');
});

test('unauthenticated user is redirected from generate index page', function () {
    auth()->logout();

    $response = $this->get(route('admin.get_generate'));

    $response->assertRedirect(route('login'));
});

test('user can create a generate record', function () {
    $data = [
        'discount_app' => '50&cs',
        'expired_range' => '1',
        'app_url' => 'http://localhost:8000/generates_new',
    ];
    $response = $this->post(route('admin.post_new_generate'), $data);

    $response->assertRedirect(route('admin.get_generate'));
    $response->assertSessionHas('success', 'Created Generate Success');

    $this->assertDatabaseHas('generates', [
        'discount_id' => 50,
        'app_name' => 'cs',
        'expired_range' => '1',
        'app_url' => 'http://localhost:8000/generates_new',
    ]);
});
test('create generate fails when discount_id not found', function () {
    $data = [
        'discount_app' => '100&cs',
        'expired_range' => '1',
        'app_url' => 'http://localhost:8000/generates_new',
    ];
    $response = $this->post(route('admin.post_new_generate'), $data);

    $response->assertStatus(404);
    $response->assertSessionHas('error', 'Discount not found');
});
test('create generate fails when discount_id and app_name not unique in generate', function () {
    $data = [
        'discount_app' => '50&cs',
        'expired_range' => '1',
        'app_url' => 'http://localhost:8000/generates_new',
    ];
    $this->post(route('admin.post_new_generate'), $data);
    $data = [
        'discount_app' => '50&cs',
        'expired_range' => '1',
        'app_url' => 'http://localhost:8000/generates_new',
    ];
    $response = $this->post(route('admin.post_new_generate'), $data);
    $response->assertStatus(409);
    $response->assertSessionHas('error', 'Config Discount already exist');
});

test('create generate fails when discount expired', function () {
    $data = [
        'discount_app' => '52&cs',
        'expired_range' => '1',
        'app_url' => 'http://localhost:8000/generates_new',
    ];
    $response = $this->post(route('admin.post_new_generate'), $data);
    $response->assertStatus(400);
    $response->assertSessionHas('error', 'Discount is expired');
});
