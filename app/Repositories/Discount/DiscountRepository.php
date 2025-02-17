<?php

namespace App\Repositories\Discount;

use Prettus\Repository\Contracts\RepositoryInterface;

interface DiscountRepository extends RepositoryInterface
{
    public function findDiscountById(int $id, string $databaseName);

    public function findDiscountByIdNoCoupon(int $id, string $databaseName);

    public function createDiscount(array $data, string $databaseName);

    public function updateDiscount(array $data, int $id, string $databaseName);

    public function destroyDiscount(int $id, string $databaseName);

    public function countDiscount(string $databaseName);

    public function getAllDiscounts(array $filters, string $databaseName);

    public function getAllDiscountsReports(array $filters, string $databaseName);

    public function findDiscountsByIdsAndApp($discountIds, $appName);

    public function getAllNotFilterWithCoupon($databaseName);

    public function getAllDiscountsNoPagination($databaseName);
}
