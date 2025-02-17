<?php

namespace App\Services\Discount;

interface DiscountService
{
    public function store(array $data, $databaseName);

    public function edit($id, array $data, $databaseName);

    public function destroy($id, $databaseName);

    public function findDiscountById($id, $databaseName);

    public function getAllDiscounts(array $filters, $databaseName);

    public function getDiscountNoCoupon(int $id, string $databaseName);
}
