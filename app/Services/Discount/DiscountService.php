<?php

namespace App\Services\Discount;

interface DiscountService
{
    // get all discounts,
    public function index(string $databaseName, array $filters);

    public function store(string $databaseName, array $attributes);

    public function update(int $id, string $databaseName, array $attributes);

    public function delete(int $id, string $databaseName);

    public function getAllDiscountIdAndName(string $databaseName);

    public function getDiscountInfo(int $id, string $databaseName);
}
