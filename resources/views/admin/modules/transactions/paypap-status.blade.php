@extends('admin.layouts.app')
@section('title', $title)
@section('content')
    <div class="content">
        <div class="card">
            <div class="card-header d-flex">
                <h5 class="mb-0">{{ $module }}</h5>
                <div class="ms-auto">
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary">
                        <i class="ph-arrow-left me-1"></i> {{ __('Back') }}
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="alert alert-info mb-4">
                    <strong>{{ __('Transaction') }}:</strong> #{{ $transaction->id }} - {{ $transaction->order_id }}
                    ({{ $transaction->sender }}
                    - {{ $transaction->currency->code() }} {{ number_format($transaction->amount, 2) }})
                    @if($transaction->deposit_id)
                        <br><strong>{{ __('Deposit ID') }}:</strong> {{ $transaction->deposit_id }}
                    @endif
                </div>

                @if($error)
                    <div class="alert alert-danger">
                        <i class="ph-warning me-2"></i>
                        <strong>{{ __('Error') }}:</strong> {{ $error }}
                    </div>
                @elseif($paypapStatus)
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">{{ __('Transaction Information') }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-5 fw-semibold text-muted">{{ __('Transaction ID') }}:</div>
                                        <div class="col-7 text-end">{{ $paypapStatus['transactionId'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-5 fw-semibold text-muted">{{ __('Status') }}:</div>
                                        <div class="col-7 text-end">
                                            @if(isset($paypapStatus['status']))
                                                <span
                                                    class="badge bg-{{ $paypapStatus['status']['code'] == 4001 ? 'success' : 'warning' }}">
                                                    {{ $paypapStatus['status']['code'] ?? '-' }} - {{ $paypapStatus['status']['desc'] ?? '-' }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-5 fw-semibold text-muted">{{ __('Full Name') }}:</div>
                                        <div class="col-7 text-end">{{ $paypapStatus['fullName'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-5 fw-semibold text-muted">{{ __('Currency') }}:</div>
                                        <div class="col-7 text-end">{{ $paypapStatus['currency'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-5 fw-semibold text-muted">{{ __('Requested Amount') }}:</div>
                                        <div class="col-7 text-end">
                                            <strong>{{ number_format($paypapStatus['requestedAmount'] ?? 0, 2) }} {{ $paypapStatus['currency'] ?? 'TRY' }}</strong>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-5 fw-semibold text-muted">{{ __('Amount') }}:</div>
                                        <div class="col-7 text-end">
                                            <strong>{{ number_format($paypapStatus['amount'] ?? 0, 2) }} {{ $paypapStatus['currency'] ?? 'TRY' }}</strong>
                                        </div>
                                    </div>
                                    @if(isset($paypapStatus['createdAt']))
                                        <div class="row mb-2">
                                            <div class="col-5 fw-semibold text-muted">{{ __('Created At') }}:</div>
                                            <div
                                                class="col-7 text-end">{{ \Carbon\Carbon::parse($paypapStatus['createdAt'])->format('Y-m-d H:i:s') }}</div>
                                        </div>
                                    @endif
                                    @if(isset($paypapStatus['updatedAt']))
                                        <div class="row mb-2">
                                            <div class="col-5 fw-semibold text-muted">{{ __('Updated At') }}:</div>
                                            <div
                                                class="col-7 text-end">{{ \Carbon\Carbon::parse($paypapStatus['updatedAt'])->format('Y-m-d H:i:s') }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="card border mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">{{ __('User Information') }}</h6>
                                </div>
                                <div class="card-body">
                                    @if(isset($paypapStatus['user']))
                                        <div class="row mb-2">
                                            <div class="col-5 fw-semibold text-muted">{{ __('User ID') }}:</div>
                                            <div
                                                class="col-7 text-end">{{ $paypapStatus['user']['userId'] ?? '-' }}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-5 fw-semibold text-muted">{{ __('Username') }}:</div>
                                            <div
                                                class="col-7 text-end">{{ $paypapStatus['user']['username'] ?? '-' }}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-5 fw-semibold text-muted">{{ __('Full Name') }}:</div>
                                            <div
                                                class="col-7 text-end">{{ $paypapStatus['user']['fullName'] ?? '-' }}</div>
                                        </div>
                                    @else
                                        <div class="text-muted">{{ __('No user information available') }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">

                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">{{ __('Raw Response Data') }}</h6>
                                </div>
                                <div class="card-body">
                                    <pre class="bg-light p-3 rounded"
                                         style="max-height: 400px; overflow-y: auto;">{{ json_encode($paypapStatus, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="ph-info me-2"></i>
                        {{ __('No Paypap status information available') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

