<?php

namespace App\Repositories\User;

use App\Models\User;
use Prettus\Repository\Eloquent\BaseRepository;

class UserRepositoryEloquent extends BaseRepository implements UserRepository
{
    public function model(): string
    {
        return User::class;
    }

    public function create(array $attributes)
    {
        return parent::create($attributes);
    }

    public function set(string $name, string $value)
    {
        // TODO: Implement set() method.
    }

    public function clear(string $name)
    {
        // TODO: Implement clear() method.
    }
}
