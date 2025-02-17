<?php

namespace App\Services\User;

use App\Exceptions\AuthException;
use App\Repositories\User\UserRepository;
use Illuminate\Support\Arr;

class UserServiceImp implements UserService
{
    public function __construct(protected UserRepository $userRepository) {}

    public function create(array $attributes)
    {
        $formData = Arr::only($attributes, ['name', 'email', 'password']);

        return $this->userRepository->create($formData);
    }

    public function login(array $attributes)
    {
        $formData = Arr::only($attributes, ['email', 'password']);
        if (! auth()->attempt($formData)) {
            throw AuthException::loginFailed();
        }

        return true;
    }

    public function store($request)
    {
        // TODO: Implement store() method.
    }

    public function show($id)
    {
        // TODO: Implement show() method.
    }

    public function edit($id)
    {
        // TODO: Implement edit() method.
    }

    public function update($request, $id)
    {
        // TODO: Implement update() method.
    }

    public function destroy($id)
    {
        // TODO: Implement destroy() method.
    }
}
