@extends('admin.layouts.app')
@section('title', $title)

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize Select2 for wallet and bank selects with search
            $('#wallet_select').select2({
                placeholder: "{{ __('Select wallet') }}",
                allowClear: true,
                width: '100%'
            });

            $('#bank_select').select2({
                placeholder: "{{ __('Select bank') }}",
                allowClear: true,
                width: '100%'
            });

            $('#site_select').select2({
                placeholder: "{{ __('Select site') }}",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@endpush

@section('content')
    <div class="content">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ $title }}</h5>
            </div>

            <div class="card-body">

                @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        <div class="alert alert-danger border-0 alert-dismissible fade show">
                            {{ $error }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endforeach
                @endif

                <form action="{{ $action }}" method="POST">
                    @method($method)
                    @csrf
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('User ID') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="number"
                                name="user_id"
                                class="form-control @error('user_id') is-invalid @enderror"
                                placeholder="{{ __('User ID') }}"
                                value="{{ old('user_id', $item->user_id ?? '') }}"
                            />
                            @error('user_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Order ID') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="number"
                                name="order_id"
                                class="form-control @error('order_id') is-invalid @enderror"
                                placeholder="{{ __('Order ID') }}"
                                value="{{ old('order_id', $item->order_id ?? '') }}"
                            />
                            @error('order_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('First Name') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="text"
                                name="first_name"
                                class="form-control @error('first_name') is-invalid @enderror"
                                placeholder="{{ __('First Name') }}"
                                value="{{ old('first_name', $item->first_name ?? '') }}"
                            />
                            @error('first_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Last Name') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="text"
                                name="last_name"
                                class="form-control @error('last_name') is-invalid @enderror"
                                placeholder="{{ __('Last Name') }}"
                                value="{{ old('last_name', $item->last_name ?? '') }}"
                            />
                            @error('last_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Phone') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="text"
                                name="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                placeholder="{{ __('Phone') }}"
                                value="{{ old('phone', $item->phone ?? '') }}"
                            />
                            @error('phone')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Wallet') }}:</label>
                        <div class="col-lg-9">
                            <select
                                id="wallet_select"
                                name="wallet_id"
                                class="form-control @error('wallet_id') is-invalid @enderror"
                                data-placeholder="{{ __('Select wallet') }}"
                            >
                                <option value="">{{ __('Select wallet') }}</option>
                                @forelse($wallets ?? [] as $wallet)
                                    <option
                                        @selected(old('wallet_id', $item->wallet_id ?? '') == $wallet->id)
                                        value="{{ $wallet->id }}">
                                        {{ $wallet->name }} ({{ $wallet->iban }})
                                    </option>
                                @empty
                                    <option value="">{{ __('No wallets found') }}</option>
                                @endforelse
                            </select>
                            @error('wallet_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Bank') }}:</label>
                        <div class="col-lg-9">
                            <select
                                id="bank_select"
                                name="bank_id"
                                class="form-control @error('bank_id') is-invalid @enderror"
                                data-placeholder="{{ __('Select bank') }}"
                            >
                                <option value="">{{ __('Select bank') }}</option>
                                @forelse($banks ?? [] as $bank)
                                    <option
                                        @selected(old('bank_id', $item->bank_id ?? '') == $bank->id)
                                        value="{{ $bank->id }}">
                                        {{ $bank->name }}
                                    </option>
                                @empty
                                    <option value="">{{ __('No banks found') }}</option>
                                @endforelse
                            </select>
                            @error('bank_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Client IP') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="text"
                                name="client_ip"
                                class="form-control @error('client_ip') is-invalid @enderror"
                                placeholder="{{ __('Client IP') }}"
                                value="{{ old('client_ip', $item->client_ip ?? request()->ip()) }}"
                            />
                            @error('client_ip')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Site') }}:</label>
                        <div class="col-lg-9">
                            <select
                                id="site_select"
                                name="site_id"
                                class="form-control @error('site_id') is-invalid @enderror"
                                data-placeholder="{{ __('Select site') }}"
                            >
                                <option value="">{{ __('Select site') }}</option>
                                @forelse($sites ?? [] as $site)
                                    <option
                                        @selected(old('site_id', $item->site_id ?? '') == $site->id)
                                        value="{{ $site->id }}">
                                        {{ $site->name }}
                                    </option>
                                @empty
                                    <option value="">{{ __('No sites found') }}</option>
                                @endforelse
                            </select>
                            @error('site_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Amount') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="number"
                                step="0.01"
                                min="0.01"
                                name="amount"
                                class="form-control @error('amount') is-invalid @enderror"
                                placeholder="{{ __('Amount') }}"
                                value="{{ old('amount', $item->amount ?? '') }}"
                            />
                            @error('amount')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Currency') }}:</label>
                        <div class="col-lg-9">
                            <select
                                name="currency"
                                class="form-control form-control-select2 @error('currency') is-invalid @enderror"
                                data-placeholder="{{ __('Select currency') }}"
                            >
                                <option value="">{{ __('Select currency') }}</option>
                                @foreach($currencies as $currency)
                                    <option
                                        @selected(old('currency', $item->currency->value ?? '') == $currency->value)
                                        value="{{ $currency->value }}">
                                        {{ $currency->value }} ({{ $currency->code() }})
                                    </option>
                                @endforeach
                            </select>
                            @error('currency')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Status') }}:</label>
                        <div class="col-lg-9">
                            <select
                                name="status"
                                class="form-control form-control-select2 @error('status') is-invalid @enderror"
                                data-placeholder="{{ __('Select status') }}"
                            >
                                <option value="">{{ __('Select status') }}</option>
                                @foreach($statuses as $status)
                                    <option
                                        @selected(old('status', $item->status->value ?? '') == $status->value)
                                        value="{{ $status->value }}">
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Paid Status') }}:</label>
                        <div class="col-lg-9">
                            <select
                                name="paid_status"
                                class="form-control form-control-select2 @error('paid_status') is-invalid @enderror"
                                data-placeholder="{{ __('Select paid status') }}"
                            >
                                <option value="">{{ __('Select paid status') }}</option>
                                @foreach($paid_statuses as $paid_status)
                                    <option
                                        @selected(old('paid_status', $item->paid_status ?? false) == $paid_status->value)
                                        value="{{ $paid_status->value }}">
                                        {{ $paid_status->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('paid_status')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Payment Method') }}:</label>
                        <div class="col-lg-9">
                            <select
                                name="payment_method"
                                class="form-control form-control-select2 @error('payment_method') is-invalid @enderror"
                                data-placeholder="{{ __('Select payment method') }}"
                            >
                                <option value="">{{ __('Select payment method') }}</option>
                                @foreach($payment_providers as $payment_provider)
                                    <option
                                        @selected(old('payment_method', $item->payment_method ?? '') == $payment_provider->value)
                                        value="{{ $payment_provider->value }}"
                                    >{{ $payment_provider->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_method')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    @isset($item)
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Transaction Fee') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="text"
                                disabled="disabled"
                                name="fee"
                                class="form-control"
                                placeholder="{{ __('Transaction Fee') }}"
                                value="{{ $item->fee ?? '' }}"
                            />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Transaction Amount') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="text"
                                disabled="disabled"
                                name="fee_amount"
                                class="form-control"
                                placeholder="{{ __('Transaction Amount') }}"
                                value="{{ old('fee_amount', $item->fee_amount ?? '') }}"
                            />
                        </div>
                    </div>
                    @endisset
                    <div class="text-end">
                        <button type="button" class="btn btn-danger" onclick="history.back()">
                            <i class="ph-arrow-left me-2"></i> {{ __('Back') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ __('Submit') }} <i class="ph-paper-plane-tilt ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
