<?php

namespace App\Repositories\User;

use Prettus\Repository\Contracts\RepositoryInterface;

interface UserRepository extends RepositoryInterface
{
    public function create(array $attributes);

    public function update(array $data, $id);

    public function delete($id);
}
