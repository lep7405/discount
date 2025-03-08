<?php

namespace App\Repositories\Discount;

use Prettus\Repository\Contracts\RepositoryInterface;

interface DiscountRepository extends RepositoryInterface
{
    public function countDiscount(string $databaseName);

    public function getAllDiscounts(array $filters, string $databaseName);

    public function createDiscount(array $attributes, string $databaseName);

    public function findDiscountByIdWithCoupon(int $id, string $databaseName);

    public function findDiscountByIdNoCoupon(int $id, string $databaseName);

    public function updateDiscount(array $attributes, int $id, string $databaseName);

    public function deleteDiscount(int $id, string $databaseName);

    public function getAllDiscountIdAndName(string $databaseName);

    public function getAllDiscountsReports(array $filters, string $databaseName);

    public function findDiscountsByIdsAndApp($discountIds, $appName);

    public function getAllNotFilterWithCoupon($databaseName);

    public function UpdateOrCreateDiscountInAffiliatePartner($name, $percentage, $trialDays, $connection);

    public function findDiscountByName(string $name,string $databaseName);
}
