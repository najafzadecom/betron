<?php

namespace App\Http\Controllers\Admin;

use App\Services\TransactionService;
use App\Services\WithdrawalService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;

class DashboardController extends BaseController
{
    private TransactionService $transactionService;
    private WithdrawalService $withdrawalService;

    public function __construct(
        TransactionService $transactionService,
        WithdrawalService  $withdrawalService,
    ) {
        $this->module = 'dashboard';
        $this->transactionService = $transactionService;
        $this->withdrawalService = $withdrawalService;
    }

    public function index(): Renderable
    {
        $this->data = [
            'module' => __('Admin'),
            'title' => __('Dashboard'),
            'transactions' => $this->transactionService->last(),
            'withdrawals' => $this->withdrawalService->last(),
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
