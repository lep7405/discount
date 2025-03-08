<?php

namespace App\Repositories\Generate;

use Prettus\Repository\Contracts\RepositoryInterface;

interface GenerateRepository extends RepositoryInterface
{
    public function getAllGenerates(array $filters);

    public function countGenerate();

    public function getGenerateByDiscountIdAndAppName($discount_id, $app_name);

    public function createGenerate(array $data);

    public function updateGenerate($id, array $data);

    public function updateGenerateStatus($id, $status);

    public function destroyGenerate($id);

    public function getGenerateById($id);
}
