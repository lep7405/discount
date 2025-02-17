<?php

namespace App\Services\Generate;

interface GenerateService
{
    public function index(array $filters);

    public function showCreate(array $databaseName);

    public function create(array $data);

    public function showUpdate($id);

    public function update($id, array $data);

    public function changeStatus($id);

    public function destroy($id);
    public function generateCoupon($generate_id, $timestamp, $shop_id);
}
