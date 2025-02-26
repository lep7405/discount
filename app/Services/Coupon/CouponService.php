<?php

namespace App\Services\Coupon;

interface CouponService
{
    public function index(array $filters, $databaseName);

    public function create(array $data, string $databaseName);
    public function update(array $data, int $id, string $databaseName);
    public function delete(int $id, string $databaseName);


    public function getCoupon(int $id, string $databaseName);

    public function decrementCoupon(int $id, int $numDecrement, string $databaseName);

    public function createByDiscount(array $data, int $discount_id, string $databaseName);

    public function allCouponsByDiscount($discount_id, string $databaseName, array $filters);
}
