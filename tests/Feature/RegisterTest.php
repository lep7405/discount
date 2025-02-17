<?php

use App\Models\User;

uses(\Illuminate\Foundation\Testing\DatabaseTransactions::class);

test('return register view', function () {
    $response = $this->get('/auth/register');

    $response->assertStatus(200);
});
test('user can register successfully', function () {
    $response = $this->post(route('register'), [
        'name' => 'John Doe',
        'email' => 'johndoe@examplde.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('login')); // kiểm trả xem có redirect về route login không
    $this->assertDatabaseHas('users', [
        'email' => 'johndoe@examplde.com',
    ]); // kiểm tra xem trong data base có email này không
});
test('registration fails when required fields are missing', function () {
    $response = $this->post(route('register'), []);

    $response->assertSessionHasErrors(['name', 'email', 'password']);
});
test('registration fails when email is already taken', function () {
    User::factory()->create([
        'email' => 'johndoe@example.com',
    ]);
    $response = $this->post(route('register'), [
        'name' => 'John Doe',
        'email' => 'johndoe@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);
    $response->assertSessionHasErrors('email');
});
test('registration fails when password confirmation does not match', function () {
    $response = $this->post(route('register'), [
        'name' => 'John Doe',
        'email' => 'johndoe@example.com',
        'password' => 'password123',
        'password_confirmation' => 'wrongpassword',
    ]);
    $response->assertSessionHasErrors('password');
});
