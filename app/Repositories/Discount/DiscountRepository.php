<?php

namespace App\Repositories\Discount;

use Prettus\Repository\Contracts\RepositoryInterface;

interface DiscountRepository extends RepositoryInterface
{
    public function countDiscount(string $databaseName);

    public function getAll(string $databaseName,array $filters);

    public function createDiscount(string $databaseName,array $attributes);

    public function updateDiscount( int $id, string $databaseName,array $attributes);

    public function deleteDiscount(int $id, string $databaseName);
    public function findByIdWithCoupon(int $id, string $databaseName);

    public function findById(int $id, string $databaseName);

    public function getAllDiscountIdAndName(string $databaseName);

    public function findByIdsAndApp(array $discountIds, string $appName);

    public function getAllWithCoupon(string $databaseName);

    public function UpdateOrCreateDiscountInAffiliatePartner(string $connection,array $attributes);

    public function findByName(string $name, string $databaseName);

    public function getAllDiscountsWithCoupon($databaseName);
}
