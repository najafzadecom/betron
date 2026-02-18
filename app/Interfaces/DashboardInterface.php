<?php

namespace App\Interfaces;

interface DashboardInterface
{
    public function getVendorStatistics(array $walletIds): array;
    public function getRecentTransactions(array $walletIds, int $limit = 10);
}
