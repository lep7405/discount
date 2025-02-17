<?php

namespace App\Services\Report;

interface ReportService
{
    public function index(array $filters, string $databaseName);
}
