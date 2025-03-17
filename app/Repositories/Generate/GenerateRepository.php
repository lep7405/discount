<?php

namespace App\Repositories\Generate;

use Prettus\Repository\Contracts\RepositoryInterface;

interface GenerateRepository extends RepositoryInterface
{
    public function getAll(array $filters);

    public function countGenerate();

    public function findByDiscountIdAndAppName(int $discount_id,string $app_name);

    public function createGenerate(array $attributes);

    public function updateGenerate(int $id, array $attributes);

    public function updateGenerateStatus(int $id, $status);

    public function destroyGenerate(int $id);

    public function findById(int $id);
}
