@extends('admin.layouts.app')
@section('title', $title)

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize Select2 for vendor select with search
            $('#vendor_select').select2({
                placeholder: "{{ __('Select vendor') }}",
                allowClear: true,
                width: '100%'
            });

            $('#bank_select').select2({
                placeholder: "{{ __('Select bank') }}",
                allowClear: true,
                width: '100%'
            });

            $('#wallet_select').select2({
                placeholder: "{{ __('Select wallet (optional)') }}",
                allowClear: true,
                width: '100%'
            });

            $('#site_select').select2({
                placeholder: "{{ __('Select site') }}",
                allowClear: true,
                width: '100%'
            });

            // Bank seçildiğinde bank_name'i otomatik doldur
            function updateBankName() {
                const selectedOption = $('#bank_select').find('option:selected');
                const bankName = selectedOption.data('name');
                if (bankName) {
                    $('#bank_name').val(bankName);
                } else {
                    $('#bank_name').val('');
                }
            }

            // Bank değiştiğinde bank_name'i güncelle
            $('#bank_select').on('change', function() {
                updateBankName();
            });

            // Sayfa yüklendiğinde bank_name'i doldur (edit modunda)
            @if(isset($item) && $item->bank_id)
                updateBankName();
            @endif

            // Wallet seçildiğinde sender fields'ları göster/gizle
            function toggleSenderFields() {
                const walletId = $('#wallet_select').val();
                if (walletId) {
                    // Wallet seçildi, sender fields'ları gizle
                    $('#sender_fields, #sender_iban_fields').hide();
                    $('#sender_name, #sender_iban').val('');
                } else {
                    // Wallet seçilmedi, sender fields'ları göster
                    $('#sender_fields, #sender_iban_fields').show();
                }
            }

            // İlk yüklemede kontrol et (edit modunda wallet_id varsa gizle)
            @if(isset($item) && $item->wallet_id)
                $('#sender_fields, #sender_iban_fields').hide();
            @else
                toggleSenderFields();
            @endif

            // Wallet değiştiğinde kontrol et
            $('#wallet_select').on('change', function() {
                toggleSenderFields();
            });

            // Fee amount hesaplama
            function calculateFeeAmount() {
                const amount = parseFloat($('#amount').val()) || 0;
                const fee = parseFloat($('#fee').val()) || 0;
                const feeAmount = (amount * fee) / 100;
                $('#fee_amount').val(feeAmount.toFixed(2));
            }

            // Amount veya fee değiştiğinde fee_amount'ı hesapla
            $('#amount, #fee').on('input', function() {
                calculateFeeAmount();
            });

            // İlk yüklemede hesapla
            calculateFeeAmount();
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
                        <label class="col-lg-3 col-form-label">{{ __('Vendor') }}:</label>
                        <div class="col-lg-9">
                            <select
                                id="vendor_select"
                                name="vendor_id"
                                class="form-control @error('vendor_id') is-invalid @enderror"
                                data-placeholder="{{ __('Select vendor') }}"
                            >
                                <option value="">{{ __('Select vendor') }}</option>
                                @forelse($vendors ?? [] as $vendor)
                                    <option
                                        @selected(old('vendor_id', $item->vendor_id ?? '') == $vendor->id)
                                        value="{{ $vendor->id }}">
                                        {{ $vendor->name }} ({{ $vendor->email }})
                                    </option>
                                @empty
                                    <option value="">{{ __('Vendor not found') }}</option>
                                @endforelse
                            </select>
                            @error('vendor_id')
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
                                        value="{{ $bank->id }}"
                                        data-name="{{ $bank->name }}">
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
                    <input
                        type="hidden"
                        id="bank_name"
                        name="bank_name"
                        value="{{ old('bank_name', $item->bank_name ?? '') }}"
                    />
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Wallet') }}:</label>
                        <div class="col-lg-9">
                            <select
                                id="wallet_select"
                                name="wallet_id"
                                class="form-control @error('wallet_id') is-invalid @enderror"
                                data-placeholder="{{ __('Select wallet (optional)') }}"
                            >
                                <option value="">{{ __('Select wallet (optional)') }}</option>
                                @forelse($wallets ?? [] as $wallet)
                                    <option
                                        @selected(old('wallet_id', $item->wallet_id ?? '') == $wallet->id)
                                        value="{{ $wallet->id }}"
                                        data-name="{{ $wallet->name }}"
                                        data-iban="{{ $wallet->iban }}">
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
                    <div class="row mb-3" id="sender_fields" style="display: none;">
                        <label class="col-lg-3 col-form-label">{{ __('Sender Name') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="text"
                                id="sender_name"
                                name="sender_name"
                                class="form-control @error('sender_name') is-invalid @enderror"
                                placeholder="{{ __('Sender Name') }}"
                                value="{{ old('sender_name', $item->sender_name ?? '') }}"
                            />
                            @error('sender_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3" id="sender_iban_fields" style="display: none;">
                        <label class="col-lg-3 col-form-label">{{ __('Sender IBAN') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="text"
                                id="sender_iban"
                                name="sender_iban"
                                class="form-control @error('sender_iban') is-invalid @enderror"
                                placeholder="{{ __('Sender IBAN') }}"
                                value="{{ old('sender_iban', $item->sender_iban ?? '') }}"
                            />
                            @error('sender_iban')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('IBAN') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="text"
                                name="iban"
                                class="form-control @error('iban') is-invalid @enderror"
                                placeholder="{{ __('IBAN') }}"
                                value="{{ old('iban', $item->iban ?? '') }}"
                            />
                            @error('iban')
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
                                id="amount"
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
                        <label class="col-lg-3 col-form-label">{{ __('Fee (%)') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="number"
                                step="0.01"
                                id="fee"
                                name="fee"
                                class="form-control @error('fee') is-invalid @enderror"
                                placeholder="{{ __('Fee percentage') }}"
                                value="{{ old('fee', $item->fee ?? 0) }}"
                                min="0"
                                max="100"
                            />
                            @error('fee')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Fee Amount') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="number"
                                step="0.01"
                                id="fee_amount"
                                name="fee_amount"
                                class="form-control @error('fee_amount') is-invalid @enderror"
                                placeholder="{{ __('Fee Amount') }}"
                                value="{{ old('fee_amount', $item->fee_amount ?? 0) }}"
                                min="0"
                                readonly
                            />
                            @error('fee_amount')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                            <small class="text-muted">{{ __('Calculated automatically from amount and fee percentage') }}</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Order ID') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="text"
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
                        <label class="col-lg-3 col-form-label">{{ __('Currency') }}:</label>
                        <div class="col-lg-9">
                            <select
                                name="currency"
                                class="form-control @error('currency') is-invalid @enderror"
                            >
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->value }}" @selected(old('currency', ($item->currency ?? null)?->value ?? $currencies[0]->value) == $currency->value)>
                                        {{ $currency->name() }}
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
                        <label class="col-lg-3 col-form-label">{{ __('Site ID') }}:</label>
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
                        <label class="col-lg-3 col-form-label">{{ __('Manual') }}:</label>
                        <div class="col-lg-9">
                            <div class="form-check form-switch">
                                <input
                                    type="checkbox"
                                    id="manual"
                                    name="manual"
                                    class="form-check-input @error('manual') is-invalid @enderror"
                                    value="1"
                                    @checked(old('manual', $item->manual ?? true))
                                />
                                <label class="form-check-label" for="manual">
                                    {{ __('Manual withdrawal') }}
                                </label>
                            </div>
                            @error('manual')
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
                                        @selected(old('payment_method', ($item->payment_method ?? null)?->value ?? '') == $payment_provider->value)
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
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Paid Status') }}:</label>
                        <div class="col-lg-9">
                            @php
                                // Withdrawal model casts paid_status as boolean, so we need to convert it to enum value
                                $paidStatusValue = old('paid_status');
                                if ($paidStatusValue === null && isset($item)) {
                                    $paidStatusValue = $item->paid_status ? \App\Enums\PaidStatus::Paid->value : \App\Enums\PaidStatus::Unpaid->value;
                                } else {
                                    $paidStatusValue = $paidStatusValue ?? \App\Enums\PaidStatus::Unpaid->value;
                                }
                            @endphp
                            <select
                                name="paid_status"
                                class="form-control form-control-select2 @error('paid_status') is-invalid @enderror"
                                data-placeholder="{{ __('Select status') }}"
                            >
                                <option value="">{{ __('Select status') }}</option>
                                @foreach($paid_statuses as $status)
                                    <option value="{{ $status->value }}" @selected($paidStatusValue == $status->value)>{{ __($status->label()) }}</option>
                                @endforeach
                            </select>
                            @error('paid_status')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-danger" onclick="history.back()"><i
                                class="ph-arrow-left me-2"></i> {{ __('Back') }} </button>
                        <button type="submit" class="btn btn-primary">{{ __('Submit') }} <i
                                class="ph-paper-plane-tilt ms-2"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
