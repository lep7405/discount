<?php

namespace App\Repositories\Coupon;

use Prettus\Repository\Contracts\RepositoryInterface;

interface CouponRepository extends RepositoryInterface
{
    public function getAllCoupons(array $filters, string $databaseName);

    public function getAllCouponsReport(array $filters, string $databaseName);

    public function countCoupons(string $databaseName);

    public function createCoupon(array $data, string $databaseName);

    public function getCouponById(int $id, string $databaseName);

    public function getCouponByCode(string $code, string $databaseName);

    public function getCouponByDiscountIdAndCode(int $discountId, string $databaseName);
    public function countCouponByDiscountIdAndCode(int $discountId, string $databaseName);

    public function decrementTimesUsed(int $id, int $numDecrement, string $databaseName);

    public function updateCoupon(array $data, int $id, string $databaseName);

    public function deleteCoupon(int $id, string $databaseName);

    public function deleteCouponByDiscountId(int $discountId, string $databaseName);

    public function getCouponByDiscountIdandShop($discountId, $shopName, $databaseName);

    public function getAllCouponsByDiscount($discount_id, array $filters, string $databaseName);
}
