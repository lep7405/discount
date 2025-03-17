<?php

namespace App\Repositories\User;

use Prettus\Repository\Contracts\RepositoryInterface;

interface UserRepository extends RepositoryInterface
{
    public function create(array $attributes);

    public function update(array $attributes, $id);

    public function delete($id);
}
