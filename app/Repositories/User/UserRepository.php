<?php

namespace App\Repositories\User;

use Dotenv\Repository\RepositoryInterface;

interface UserRepository extends RepositoryInterface
{
    public function create(array $data);

    public function update(array $data, $id);

    public function delete($id);
}
