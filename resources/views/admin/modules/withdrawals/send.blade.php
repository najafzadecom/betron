@extends('admin.layouts.app')
@section('title', $title)

@push('scripts')
<script>
$(document).ready(function() {
    // Yeni tutar input əlavə etmək
    $('#add-amount').click(function() {
        var amountHtml = `
            <div class="amount-input-group mb-2">
                <div class="input-group">
                    <input
                        type="number"
                        name="amounts[]"
                        class="form-control @error('amounts.*') is-invalid @enderror"
                        placeholder="{{ __('Withdrawal Amount') }}"
                        step="0.01"
                        min="0"
                    />
                    <button type="button" class="btn btn-outline-primary remove-amount">
                        <i class="ph-minus"></i>
                    </button>
                </div>
            </div>
        `;
        $('#amounts-container').append(amountHtml);
    });

    // Tutar input silmək
    $(document).on('click', '.remove-amount', function() {
        $(this).closest('.amount-input-group').remove();
    });
});
</script>
@endpush

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('Account Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.withdrawals.send') }}" method="POST">
                            @csrf

                            <div class="fw-bold border-bottom pb-2 mb-3">{{ __('Sender Information') }}</div>
                            <div class="row mb-3">
                                <label class="col-lg-3 col-form-label">{{ __('Sender Account') }}:</label>
                                <div class="col-lg-9">
                                    <select name="wallet_id" class="form-control">
                                        <option value="0">{{ __('Select Sender Account') }}</option>
                                        @forelse($wallets as $wallet)
                                            <option
                                                @selected(old('wallet_id', $item->wallet_id ?? 0) == $wallet->id) value="{{ $wallet->id }}">{{ $wallet->iban }} - {{ $wallet->name }} - {{ $wallet->total_amount }}</option>
                                        @empty
                                            <option value="0">{{ __('Data not found') }}</option>
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
                                <label class="col-lg-3 col-form-label">{{ __('Withdrawal Amount') }}:</label>
                                <div class="col-lg-9">
                                    <div id="amounts-container">
                                        <div class="amount-input-group mb-2">
                                            <div class="input-group">
                                                <input
                                                    type="number"
                                                    name="amounts[]"
                                                    class="form-control @error('amounts.*') is-invalid @enderror"
                                                    placeholder="{{ __('Withdrawal Amount') }}"
                                                    step="0.01"
                                                    min="0"
                                                    value="{{ old('amounts.0', $item->amount ?? '') }}"
                                                />
                                                <button type="button" class="btn btn-outline-primary remove-amount">
                                                    <i class="ph-minus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <button type="button" id="add-amount" class="btn btn-outline-primary btn-sm">
                                            <i class="ph-plus"></i> {{ __('Add Amount') }}
                                        </button>
                                    </div>

                                    @error('amounts.*')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="fw-bold border-bottom pb-2 mb-3">{{ __('Receiver Information') }}</div>
                            <div class="row mb-3">
                                <label class="col-lg-3 col-form-label">{{ __('Receiver Iban') }}:</label>
                                <div class="col-lg-9">
                                    <input
                                        type="text"
                                        name="receiver_iban"
                                        class="form-control  @error('receiver_iban') is-invalid @enderror"
                                        placeholder="{{ __('TR ___ ___ ___ ___ ___ ___') }}"
                                        value="{{ old('receiver_iban', $item->receiver_iban ?? '') }}"
                                    />
                                    @error('receiver_iban')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-lg-3 col-form-label">{{ __('Receiver Name') }}:</label>
                                <div class="col-lg-9">
                                    <input
                                        type="text"
                                        name="receiver_name"
                                        class="form-control  @error('receiver_name') is-invalid @enderror"
                                        placeholder="{{ __('Firstname Lastname') }}"
                                        value="{{ old('receiver_name', $item->receiver_name ?? '') }}"
                                    />
                                    @error('receiver_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="text-end">
                                <a href="{{ route('admin.withdrawals.send') }}" class="btn btn-light text-body">
                                    {{ __('Cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Create Withdrawal Request') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('Waiting Operations') }}</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-xs text-nowrap">
                            <thead>
                            <th class="text-center" style="width: 20px;">
                                <i class="ph-dots-three"></i>
                            </th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Sender') }}</th>
                            <th>{{ __('Receiver') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Created At') }}</th>
                            </thead>
                            <tbody>
                            @forelse($items as $item)
                            <tr>
                                <td></td>
                                <td>{!! $item->status_html !!}</td>
                                <td>{{ $item->sender }}</td>
                                <td>
                                    {{ $item->receiver }}<br/>
                                    <small>{{ $item->receiver_iban }}</small>
                                </td>
                                <td><span class="badge bg-indigo bg-opacity-10 text-indigo">{{ $item->currency->code() }} {{ $item->amount }}</span></td>
                                <td>{{ $item->created_at->isoFormat('DD MMM YYYY HH:mm') }}</td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="6">{{ __('Data not found') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
