<?php

use App\Exceptions\AuthException;
use App\Repositories\User\UserRepository;
use App\Services\User\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

uses(\Tests\TestCase::class);
beforeEach(function () {
    $this->userRepository = Mockery::mock(UserRepository::class);
    $this->userService = app()->make(UserService::class, [
        'userRepository' => $this->userRepository,

    ]);
});

test('create method passes attributes to repository and returns result', function () {
    $attributes = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123'
    ];

    $expectedUser = (object)[
        'id' => 1,
        'name' => 'Test User',
        'email' => 'test@example.com'
    ];

    $this->userRepository->shouldReceive('create')
        ->once()
        ->with($attributes)
        ->andReturn($expectedUser);

    $result = $this->userService->create($attributes);
    expect($result)->toBe($expectedUser);
});

test('login method throws exception when credentials are invalid', function () {
    $credentials = [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
        'remember' => true // This should be filtered out
    ];

    // Mock auth()->attempt() to return false (invalid credentials)
    Auth::shouldReceive('attempt')
        ->once()
        ->with(['email' => 'test@example.com', 'password' => 'wrong-password'])
        ->andReturn(false);

    expect(fn() => $this->userService->login($credentials))
        ->toThrow(AuthException::class);
});

test('login method returns true when credentials are valid', function () {
    $credentials = [
        'email' => 'test@example.com',
        'password' => 'correct-password'
    ];

    // Mock auth()->attempt() to return true (valid credentials)
    Auth::shouldReceive('attempt')
        ->once()
        ->with($credentials)
        ->andReturn(true);

    $result = $this->userService->login($credentials);
    expect($result)->toBeTrue();
});

test('changePassword method hashes and updates password', function () {
    $userId = 1;
    $data = ['password' => 'new-password123'];
    $hashedPassword = 'hashed-new-password';

    Hash::shouldReceive('make')
        ->once()
        ->with('new-password123')
        ->andReturn($hashedPassword);

    $this->userRepository->shouldReceive('update')
        ->once()
        ->with(['password' => $hashedPassword], $userId)
        ->andReturn(true);

    $this->userService->changePassword($data, $userId);
    // The method doesn't return anything, so we just verify the mocks were called as expected
});
