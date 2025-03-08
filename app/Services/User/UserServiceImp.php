<?php

namespace App\Services\User;

use App\Exceptions\AuthException;
use App\Repositories\User\UserRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class UserServiceImp implements UserService
{
    public function __construct(protected UserRepository $userRepository) {}

    public function create(array $attributes)
    {
        return $this->userRepository->create($attributes);
    }

    public function login(array $attributes)
    {
        $formData = Arr::only($attributes, ['email', 'password']);
        if (! auth()->attempt($formData)) {
            throw AuthException::loginFailed(['error' => 'Invalid email or password']);
        }

        return true;
    }

    public function changePassword($data, $id)
    {
        $newPassword = Arr::get($data, 'password');
        $this->userRepository->update(['password' => Hash::make($newPassword)], $id);
    }
}
