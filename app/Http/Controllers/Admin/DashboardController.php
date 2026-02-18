<?php

namespace App\Http\Controllers\Admin;

use App\Services\SiteService;
use App\Services\SiteStatisticService;
use App\Services\TransactionService;
use App\Services\WithdrawalService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;

class DashboardController extends BaseController
{
    private TransactionService $transactionService;
    private WithdrawalService $withdrawalService;
    private SiteService $siteService;
    private SiteStatisticService $siteStatisticService;

    public function __construct(
        TransactionService   $transactionService,
        WithdrawalService    $withdrawalService,
        SiteService          $siteService,
        SiteStatisticService $siteStatisticService
    ) {
        $this->module = 'dashboard';
        $this->transactionService = $transactionService;
        $this->withdrawalService = $withdrawalService;
        $this->siteService = $siteService;
        $this->siteStatisticService = $siteStatisticService;
    }

    public function index(): Renderable
    {
        $transactions = $this->transactionService->last();

        $withdrawals = $this->withdrawalService->last();

        $siteStatistics = $this->siteStatisticService->getBySiteId(1);

        $site = $this->siteService->getById(1);

        $this->data = [
            'module' => __('Admin'),
            'title' => __('Dashboard'),
            'transactions' => $transactions,
            'withdrawals' => $withdrawals,
            'siteStatistics' => $siteStatistics,
            'site' => $site,
            'transactions_total' => $this->transactionService->sumAmount(),
            'transactions_count' => $this->transactionService->paidTransactionsCount(),
            'transactions_fee_total' => $this->transactionService->sumFeeAmount(),
            'withdrawals_total' => $this->withdrawalService->sumAmount(),
            'withdrawals_fee_total' => $this->withdrawalService->sumFeeAmount(),
            'withdrawals_count' => $this->withdrawalService->paidWithdrawalsCount(),
        ];

        return $this->render('index');
    }

    public function ajaxTransactions(): JsonResponse
    {
        $draw = request('draw');
        $start = (int)request('start', 0);
        $length = (int)request('length', 10);
        $searchValue = request('search.value');

        $query = $this->transactionService->query();

        $query->orderBy('created_at', 'desc');

        $recordsTotal = $query->count();

        if ($searchValue) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('id', 'like', "%$searchValue%")
                    ->orWhere('first_name', 'ilike', "%{$searchValue}%")
                    ->orWhere('last_name', 'ilike', "%{$searchValue}%")
                    ->orWhere('amount', 'ilike', "%$searchValue%")
                    ->orWhere('receiver_iban', 'ilike', "%$searchValue%")
                    ->orWhere('receiver_name', 'ilike', "%$searchValue%");
            });
        }

        $filteredTotal = $query->count();

        $transactions = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($transactions as $transaction) {
            $data[] = [
                'id' => $transaction->id,
                'sender' => $transaction->sender ?? '-',
                'amount' => $transaction->currency->code() . ' ' . number_format($transaction->amount, 2),
                'status_html' => $transaction->status_html,
                'created_at_date' => $transaction->created_at->isoFormat('DD MMM YYYY'),
                'created_at_time' => $transaction->created_at->isoFormat('HH:mm:ss'),
            ];
        }

        return response()->json([
            'draw' => (int)$draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $filteredTotal,
            'data' => $data,
        ]);
    }

    public function ajaxWithdrawals(): JsonResponse
    {
        $draw = request('draw');
        $start = (int)request('start', 0);
        $length = (int)request('length', 10);
        $searchValue = request('search.value');

        $query = $this->withdrawalService->query();

        $query->orderBy('created_at', 'desc');

        $recordsTotal = $query->count();

        if ($searchValue) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('id', 'like', "%$searchValue%")
                    ->orWhere('iban', 'like', "%$searchValue%")
                    ->orWhere('amount', 'like', "%$searchValue%");
            });
        }

        $filteredTotal = $query->count();

        $withdrawals = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($withdrawals as $withdrawal) {
            $data[] = [
                'id' => $withdrawal->id,
                'receiver' => $withdrawal->receiver ?? '-',
                'iban' => $withdrawal->iban,
                'amount' => $withdrawal->currency->code() . ' ' . number_format($withdrawal->amount, 2),
                'status_html' => $withdrawal->status_html,
                'created_at_date' => $withdrawal->created_at->isoFormat('DD MMM YYYY'),
                'created_at_time' => $withdrawal->created_at->isoFormat('HH:mm:ss'),
            ];
        }

        return response()->json([
            'draw' => (int)$draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $filteredTotal,
            'data' => $data,
        ]);
    }
}
