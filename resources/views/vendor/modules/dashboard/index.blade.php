@extends('vendor.layouts.app')
@section('title', $title)
@section('content')
    <div class="content">
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-sm-6 col-xl-3">
                <a href="{{ route('vendor.deposit-transactions') }}" class="card card-body text-decoration-none">
                    <div class="d-flex align-items-center">
                        <div class="flex-fill">
                            <h4 class="mb-0">{{ number_format($depositAmount, 2) }} ₺</h4>
                            <span class="text-muted">{{ __('Deposit Amount') }}</span>
                        </div>

                        <i class="ph-bank ph-2x text-success opacity-75 ms-3"></i>
                    </div>
                </a>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-fill">
                            <h4 class="mb-0">{{ $totalWallets }}</h4>
                            <span class="text-muted">{{ __('Total Wallets') }}</span>
                        </div>

                        <i class="ph-wallet ph-2x text-primary opacity-75 ms-3"></i>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-fill">
                            <h4 class="mb-0">{{ $totalTransactions }}</h4>
                            <span class="text-muted">{{ __('Total Transactions') }}</span>
                        </div>

                        <i class="ph-coins ph-2x text-success opacity-75 ms-3"></i>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-fill">
                            <h4 class="mb-0">{{ $pendingTransactions }}</h4>
                            <span class="text-muted">{{ __('Pending Transactions') }}</span>
                        </div>

                        <i class="ph-clock ph-2x text-warning opacity-75 ms-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Recent Transactions') }}</h5>
            </div>

            <div class="table-responsive">
                <table class="table table-xs text-nowrap">
                    <thead>
                    <tr>
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('Order ID') }}</th>
                        <th>{{ __('Sender') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Wallet') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Date') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($recentTransactions as $transaction)
                        <tr>
                            <td>{{ $transaction->id }}</td>
                            <td>{{ $transaction->order_id }}</td>
                            <td>{{ $transaction->first_name }} {{ $transaction->last_name }}</td>
                            <td>
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    {{ $transaction->currency->code() ?? 'TRY' }} {{ number_format($transaction->amount, 2) }}
                                </span>
                            </td>
                            <td>{{ $transaction->wallet?->name }}</td>
                            <td>{!! $transaction->status_html !!}</td>
                            <td>{{ $transaction->created_at?->isoFormat('DD MMM YYYY HH:mm') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">{{ __('No transactions found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($recentTransactions->count() > 0)
            <div class="card-footer">
                <a href="{{ route('vendor.transactions.index') }}" class="btn btn-primary btn-sm">
                    {{ __('View All Transactions') }} <i class="ph-arrow-right ms-1"></i>
                </a>
            </div>
            @endif
        </div>
    </div>
@endsection
