<?php

namespace App\Repositories\Coupon;

use Prettus\Repository\Contracts\RepositoryInterface;

interface CouponRepository extends RepositoryInterface
{
    public function getAll( $discount_id, string $databaseName, array $filters);
    public function countCoupons(string $databaseName);
    public function createCoupon(string $databaseName, array $attributes);
    public function updateCoupon(int $id, string $databaseName, array $attributes);
    public function deleteCoupon(int $id, string $databaseName);
    public function findById(int $id, string $databaseName);

    public function findByDiscountIdAndCode(int $discountId, string $databaseName);
    public function countByDiscountIdAndCode(int $discountId, string $databaseName);
    public function decrementTimesUsed(int $id, string $databaseName, int $numDecrement);
    public function deleteByDiscountId(int $discountId, string $databaseName);
    public function findByDiscountIdandShop(int $discountId, string $shopName, string $databaseName);

    public function findByCode(string $code, string $databaseName);
}
