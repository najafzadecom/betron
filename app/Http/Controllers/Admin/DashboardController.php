<?php

namespace App\Http\Controllers\Admin;

use App\Services\SiteService;
use App\Services\SiteStatisticService;
use App\Services\StatisticsService;
use App\Services\TransactionService;
use App\Services\WithdrawalService;
use App\Support\Merchant;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;

class DashboardController extends BaseController
{
    private TransactionService $transactionService;
    private WithdrawalService $withdrawalService;
    private SiteService $siteService;
    private SiteStatisticService $siteStatisticService;
    private StatisticsService $statisticsService;

    public function __construct(
        TransactionService   $transactionService,
        WithdrawalService    $withdrawalService,
        SiteService          $siteService,
        SiteStatisticService $siteStatisticService,
        StatisticsService    $statisticsService,
    ) {
        $this->module = 'dashboard';
        $this->transactionService = $transactionService;
        $this->withdrawalService = $withdrawalService;
        $this->siteService = $siteService;
        $this->siteStatisticService = $siteStatisticService;
        $this->statisticsService = $statisticsService;
    }

    public function index(): Renderable
    {
        $transactions = $this->transactionService->last();

        $withdrawals = $this->withdrawalService->last();

        $merchantSiteId = Merchant::siteIdFor();
        $siteStatistics = null;
        $site = null;
        $transactionsCount = $this->transactionService->paidTransactionsCount();
        $withdrawalsCount = $this->withdrawalService->paidWithdrawalsCount();

        if ($merchantSiteId) {
            $this->statisticsService->siteId = $merchantSiteId;
            $this->statisticsService->createdFrom = '2000-01-01';
            $this->statisticsService->createdTo = date('Y-m-d');
            $this->statisticsService->vendorIds = [];
            $this->statisticsService->walletIds = [];

            $payInTotal = (float) $this->statisticsService->getAcceptedTransactionsAmount();
            $payInFeeTotal = $this->statisticsService->getAcceptedTransactionsFeeAmount();
            $payOutTotal = (float) $this->statisticsService->getAcceptedWithdrawalsAmount();
            $payOutFeeTotal = $this->statisticsService->getAcceptedWithdrawalsFeeAmount();
            $total = ($payInTotal - $payInFeeTotal) - ($payOutTotal + $payOutFeeTotal);

            $siteStatistics = (object) [
                'pay_in_total' => $payInTotal,
                'pay_in_fee_total' => $payInFeeTotal,
                'pay_in_grand_total' => $payInTotal - $payInFeeTotal,
                'pay_out_total' => $payOutTotal,
                'pay_out_fee_total' => $payOutFeeTotal,
                'pay_out_grand_total' => $payOutTotal + $payOutFeeTotal,
                'total' => $total,
            ];

            $site = $this->siteService->getById($merchantSiteId);
            $transactionsCount = $this->statisticsService->getAcceptedTransactions();
            $withdrawalsCount = $this->statisticsService->getAcceptedWithdrawals();
        } else {
            $siteStatistics = $this->siteStatisticService->getBySiteId(1);
            $site = $this->siteService->getById(1);
        }

        $this->data = [
            'module' => __('Admin'),
            'title' => __('Dashboard'),
            'transactions' => $transactions,
            'withdrawals' => $withdrawals,
            'siteStatistics' => $siteStatistics,
            'site' => $site,
            'transactions_total' => $this->transactionService->sumAmount(),
            'transactions_count' => $transactionsCount,
            'transactions_fee_total' => $this->transactionService->sumFeeAmount(),
            'withdrawals_total' => $this->withdrawalService->sumAmount(),
            'withdrawals_fee_total' => $this->withdrawalService->sumFeeAmount(),
            'withdrawals_count' => $withdrawalsCount,
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
