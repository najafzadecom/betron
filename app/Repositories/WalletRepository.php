<?php

namespace App\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Interfaces\WalletInterface;
use App\Models\Wallet as Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletRepository extends BaseRepository implements WalletInterface
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }

    public function rand($bankId, $amount): ?object
    {
        DB::enableQueryLog();
        $cacheKey = "bank_{$bankId}_last_id";

        $lastId = Cache::get($cacheKey);

        $isPostgres = DB::getDriverName() === 'pgsql';
        $transactionsTable = $isPostgres ? '"transactions"' : 'transactions';

        // Date check for today - only count transactions created today
        $dateCheck = $isPostgres
            ? '"transactions"."created_at"::date = CURRENT_DATE'
            : 'DATE(transactions.created_at) = CURDATE()';

        $walletIdRef = $isPostgres ? '"wallets"."id"' : 'wallets.id';

        $paidStatusCheck = $isPostgres
            ? "{$transactionsTable}.\"paid_status\" = true"
            : "{$transactionsTable}.paid_status = 1";

        // Vendor table references for joins
        // Use unquoted table/column names - Laravel will quote them automatically
        $vendorTable = 'vendors';
        $parentVendorTable = 'vendors';
        $parentVendorAlias = 'parent_vendors';

        // For whereRaw() clauses, we need quoted references for PostgreSQL
        $vendorDepositRef = $isPostgres ? '"vendors"."deposit_amount"' : 'vendors.deposit_amount';
        $vendorTransactionFeeRef = $isPostgres ? '"vendors"."transaction_fee"' : 'vendors.transaction_fee';
        $parentVendorDepositRef = $isPostgres ? '"parent_vendors"."deposit_amount"' : 'parent_vendors.deposit_amount';
        $parentVendorTransactionFeeRef = $isPostgres ? '"parent_vendors"."transaction_fee"' : 'parent_vendors.transaction_fee';

        $query = $this->model
            ->newQuery()
            ->join($vendorTable, function ($join) {
                $join->on('wallets.vendor_id', '=', 'vendors.id');
            })
            ->leftJoin("{$parentVendorTable} as {$parentVendorAlias}", function ($join) {
                $join->on('vendors.parent_id', '=', 'parent_vendors.id');
            })
            ->where('wallets.status', 1)
            ->where('vendors.status', 1) // Vendor is active
            ->where('vendors.deposit_enabled', $isPostgres ? true : 1) // Vendor transaction is enabled
            ->where(function ($q) {
                // Either no parent (parent_id is null) or parent is active
                $q->whereNull('vendors.parent_id')
                    ->orWhere('parent_vendors.status', 1);
            })
            ->whereHas('transactionBanks', function ($query) use ($bankId) {
                $query->where('bank_id', $bankId);
            })
            ->where(function ($q) use ($amount) {
                $q->where('wallets.single_deposit_min_amount', '<=', $amount)
                    ->where('wallets.single_deposit_max_amount', '>=', $amount);
            })
            // Check if vendor has sufficient deposit (amount - fee_amount)
            // fee_amount = (amount * transaction_fee) / 100
            // Required deposit = amount - fee_amount = amount - (amount * transaction_fee / 100)
            ->where(function ($q) use ($vendorDepositRef, $vendorTransactionFeeRef, $amount, $isPostgres) {
                if ($isPostgres) {
                    $q->whereRaw("{$vendorDepositRef} >= (CAST(? AS DECIMAL) - (CAST(? AS DECIMAL) * CAST({$vendorTransactionFeeRef} AS DECIMAL) / 100))", [$amount, $amount]);
                } else {
                    $q->whereRaw("{$vendorDepositRef} >= (? - (? * {$vendorTransactionFeeRef} / 100))", [$amount, $amount]);
                }
            })
            // Check if parent vendor has sufficient deposit (if parent exists)
            ->where(function ($q) use ($parentVendorDepositRef, $parentVendorTransactionFeeRef, $amount, $isPostgres) {
                // Either no parent (parent_id is null) or parent has sufficient deposit
                if ($isPostgres) {
                    $q->whereNull('vendors.parent_id')
                        ->orWhereRaw("{$parentVendorDepositRef} >= (CAST(? AS DECIMAL) - (CAST(? AS DECIMAL) * CAST({$parentVendorTransactionFeeRef} AS DECIMAL) / 100))", [$amount, $amount]);
                } else {
                    $q->whereNull('vendors.parent_id')
                        ->orWhereRaw("{$parentVendorDepositRef} >= (? - (? * {$parentVendorTransactionFeeRef} / 100))", [$amount, $amount]);
                }
            })
            ->select('wallets.*')
            ->where(function ($q) use ($amount, $transactionsTable, $walletIdRef, $paidStatusCheck, $dateCheck) {
                $q->where('wallets.maximum_amount', 0)
                    ->orWhereRaw("wallets.maximum_amount >= (
                        SELECT COALESCE(SUM({$transactionsTable}.amount), 0) + ?
                        FROM {$transactionsTable}
                        WHERE {$paidStatusCheck}
                        AND {$dateCheck}
                        AND {$transactionsTable}.wallet_id = {$walletIdRef}
                    )", [$amount]);
            })
            ->orderBy('wallets.id');

        if ($lastId) {
            $query->where('wallets.id', '>', $lastId);
        }

        $next = $query->first();

        if (!$next) {
            $next = $this->model
                ->newQuery()
                ->join($vendorTable, function ($join) {
                    $join->on('wallets.vendor_id', '=', 'vendors.id');
                })
                ->leftJoin("{$parentVendorTable} as {$parentVendorAlias}", function ($join) {
                    $join->on('vendors.parent_id', '=', 'parent_vendors.id');
                })
                ->where('wallets.status', 1)
                ->where('vendors.status', 1) // Vendor is active
                ->where('vendors.deposit_enabled', $isPostgres ? true : 1) // Vendor transaction is enabled
                ->where(function ($q) {
                    // Either no parent (parent_id is null) or parent is active
                    $q->whereNull('vendors.parent_id')
                        ->orWhere('parent_vendors.status', 1);
                })
                ->whereHas('transactionBanks', function ($query) use ($bankId) {
                    $query->where('bank_id', $bankId);
                })
                ->where(function ($q) use ($amount) {
                    $q->where('wallets.single_deposit_min_amount', '<=', $amount)
                        ->where('wallets.single_deposit_max_amount', '>=', $amount);
                })
                // Check if vendor has sufficient deposit (amount - fee_amount)
                // fee_amount = (amount * transaction_fee) / 100
                // Required deposit = amount - fee_amount = amount - (amount * transaction_fee / 100)
                ->where(function ($q) use ($vendorDepositRef, $vendorTransactionFeeRef, $amount, $isPostgres) {
                    if ($isPostgres) {
                        $q->whereRaw("{$vendorDepositRef} >= (CAST(? AS DECIMAL) - (CAST(? AS DECIMAL) * CAST({$vendorTransactionFeeRef} AS DECIMAL) / 100))", [$amount, $amount]);
                    } else {
                        $q->whereRaw("{$vendorDepositRef} >= (? - (? * {$vendorTransactionFeeRef} / 100))", [$amount, $amount]);
                    }
                })
                // Check if parent vendor has sufficient deposit (if parent exists)
                ->where(function ($q) use ($parentVendorDepositRef, $parentVendorTransactionFeeRef, $amount, $isPostgres) {
                    // Either no parent (parent_id is null) or parent has sufficient deposit
                    if ($isPostgres) {
                        $q->whereNull('vendors.parent_id')
                            ->orWhereRaw("{$parentVendorDepositRef} >= (CAST(? AS DECIMAL) - (CAST(? AS DECIMAL) * CAST({$parentVendorTransactionFeeRef} AS DECIMAL) / 100))", [$amount, $amount]);
                    } else {
                        $q->whereNull('vendors.parent_id')
                            ->orWhereRaw("{$parentVendorDepositRef} >= (? - (? * {$parentVendorTransactionFeeRef} / 100))", [$amount, $amount]);
                    }
                })
                ->select('wallets.*')
                ->where(function ($q) use ($transactionsTable, $walletIdRef, $amount, $paidStatusCheck, $dateCheck) {
                    $q->where('wallets.maximum_amount', 0)
                        ->orWhere(function ($subQ) use ($amount, $walletIdRef, $transactionsTable, $paidStatusCheck, $dateCheck) {
                            $subQ->whereRaw("wallets.maximum_amount >= (
                                SELECT COALESCE(SUM({$transactionsTable}.amount), 0) + ?
                                FROM {$transactionsTable}
                                WHERE {$paidStatusCheck}
                                AND {$dateCheck}
                                AND {$transactionsTable}.wallet_id = {$walletIdRef}
                            )", [$amount]);
                        });
                })
                ->orderBy('wallets.id')
                ->first();
        }

        if ($next) {
            Cache::put($cacheKey, $next->id, now()->addHours(6));
        }

        //Log::info(DB::getRawQueryLog());

        return $next;
    }

    public function firstOrCreate($where, $data): \Illuminate\Database\Eloquent\Model
    {
        return $this->model->newModelQuery()->firstOrCreate($where, $data);
    }
}
