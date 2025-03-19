<?php

namespace App\Services\Coupon;

interface CouponService
{
    public function index(string $databaseName, array $filters);

    public function store(string $databaseName, array $attributes);

    public function update(int $id, string $databaseName, array $formData);

    public function delete(int $id, string $databaseName);

    public function decrementTimesUsedCoupon(int $id, string $databaseName, int $numDecrement);

    public function createCouponByDiscount(int $discountId, string $databaseName, array $attributes);

    public function getAllCouponsByDiscount($discountId, string $databaseName, array $filters);
}
