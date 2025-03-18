<?php

namespace App\Services\Generate;

interface GenerateService
{
    public function index(array $filters);

    public function create(array $databaseName);

    public function store(array $attributes);

    public function edit(int $id,array $databaseName);

    public function update(int $id, array $attributes);
    public function destroy(int $id);

    public function changeStatus(int $id);

    public function generateCoupon(int $generateId, $timestamp, $shopId);
    public function privateGenerateCoupon($ip, int $generateId,string $shopName);

    public function createCouponFromAffiliatePartner(array $formData, string $appCode, string $shopName);
}
