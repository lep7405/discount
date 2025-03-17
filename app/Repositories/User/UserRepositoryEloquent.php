<?php

namespace App\Repositories\User;

use App\Models\User;
use Illuminate\Support\Arr;
use Prettus\Repository\Eloquent\BaseRepository;

class UserRepositoryEloquent extends BaseRepository implements UserRepository
{
    public function model(): string
    {
        return User::class;
    }

    public function create(array $attributes)
    {
        return $this->getModel()->create([
            'name' => Arr::get($attributes, 'name'),
            'email' => Arr::get($attributes, 'email'),
            'password' => Arr::get($attributes, 'password'),
        ]);
    }

    public function update(array $attributes, $id)
    {
        return $this->getModel()
            ->where('id', $id)
            ->update($attributes);
    }
}
