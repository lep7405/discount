<?php

namespace App\Services\User;

interface UserService
{
    public function create(array $attributes);

    public function login(array $attributes);

    public function changePassword($data, $id);
}
