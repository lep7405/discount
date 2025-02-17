<?php

use App\Models\User;

uses(\Illuminate\Foundation\Testing\DatabaseTransactions::class);
test('return login view', function () {
    $response = $this->get('/auth/login');

    $response->assertStatus(200);
});
test('user can login successfully', function () {
    $user = User::factory()->create([
        'email' => 'johndoe@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post(route('login'), [
        'email' => 'johndoe@example.com',
        'password' => 'password123',
    ]);
    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('admin.dashboard.index'));
});

test('login fails when email or password not correct', function () {
    $user = User::factory()->create([
        'email' => 'johndoe@example.com',
        'password' => bcrypt('password123'),
    ]);
    $response = $this->post(route('login'), [
        'email' => 'johndoe@example.com',
        'password' => 'wrongpassword',
    ]);
    $this->assertGuest();
    $response->assertSessionHas('error', 'Email or password is incorrect');
});
