<?php

namespace App\Services\Discount;

interface DiscountService
{
    // get all discounts,
    public function index(array $filters, $databaseName);

    public function getAllDiscountIdAndName($databaseName);

    public function store(array $attributes, $databaseName);

    public function update($id, array $attributes, $databaseName);

    public function delete($id, $databaseName);

    // for edit
    //    public function getDiscountAndStatus($id, $databaseName);

    // get discount info
    public function getDiscountInfo(int $id, string $databaseName);

    // cái method này để lấy cái discount id với discount name cho coupon trong select
}
