<?php

namespace App\Services\Generate;

interface GenerateService
{
    public function index(array $filters);

    public function showCreate(array $databaseName);

    public function create(array $data);
    public function testCreate($discount_id,$app_name);

    public function showUpdate($id);

    public function update($id, array $data);

    public function changeStatus($id);

    public function destroy($id);

    public function generateCoupon($generate_id, $timestamp, $shop_id);

    public function createCouponFromAffiliatePartner(array $data, string $appCode, string $shopName);

    //test
    public function test1();
    public function testCreateName(string $name,string $app_name);
}
