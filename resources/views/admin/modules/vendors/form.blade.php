@extends('admin.layouts.app')
@section('title', $title)

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
                        <label class="col-lg-3 col-form-label">{{ __('Name') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="text"
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="{{ __('Name') }}"
                                value="{{ old('name', $item->name ?? '') }}"
                            />
                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Email') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="email"
                                name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="{{ __('Email') }}"
                                value="{{ old('email', $item->email ?? '') }}"
                            />
                            @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Password') }}:</label>
                        <div class="col-lg-9">
                            <div class="form-control-feedback input-group">
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="{{ __('Password') }}"
                                />
                                <span class="input-group-text cursor-pointer" onclick="togglePassword()"
                                      data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Show Password') }}"
                                      data-show-text="{{ __('Show Password') }}" data-hide-text="{{ __('Hide Password') }}">
                                    <i class="ph-eye text-muted" id="togglePassword"></i>
                                </span>
                            </div>
                            @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Password Confirmation') }}:</label>
                        <div class="col-lg-9">
                            <div class="form-control-feedback  input-group">
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    id="password_confirmation"
                                    class="form-control @error('password_confirmation') is-invalid @enderror"
                                    placeholder="{{ __('Password Confirmation') }}"
                                />
                                <span class="input-group-text cursor-pointer" onclick="togglePasswordConfirmation()"
                                      data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Show Password') }}"
                                      data-show-text="{{ __('Show Password') }}" data-hide-text="{{ __('Hide Password') }}">
                                    <i class="ph-eye text-muted" id="togglePasswordConfirmation"></i>
                                </span>
                            </div>
                            @error('password_confirmation')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Transaction Fee') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="number"
                                name="transaction_fee"
                                class="form-control @error('transaction_fee') is-invalid @enderror"
                                placeholder="{{ __('Transaction Fee') }}"
                                value="{{ old('transaction_fee', $item->transaction_fee ?? '') }}"
                            />
                            @error('transaction_fee')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Withdrawal Fee') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="number"
                                name="withdrawal_fee"
                                class="form-control @error('withdrawal_fee') is-invalid @enderror"
                                placeholder="{{ __('Withdrawal Fee') }}"
                                value="{{ old('withdrawal_fee', $item->withdrawal_fee ?? '') }}"
                            />
                            @error('withdrawal_fee')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Settlement Fee') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="number"
                                name="settlement_fee"
                                class="form-control @error('settlement_fee') is-invalid @enderror"
                                placeholder="{{ __('Settlement Fee') }}"
                                value="{{ old('settlement_fee', $item->settlement_fee ?? '') }}"
                            />
                            @error('settlement_fee')
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
                                class="form-control @error('status') is-invalid @enderror"
                            >
                                <option value="1" @selected(old('status', $item->status ?? 1) == 1)>{{ __('Active') }}</option>
                                <option value="0" @selected(old('status', $item->status ?? 1) == 0)>{{ __('Inactive') }}</option>
                            </select>
                            @error('status')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Deposit Enabled') }}:</label>
                        <div class="col-lg-9">
                            <select
                                name="deposit_enabled"
                                class="form-control @error('deposit_enabled') is-invalid @enderror"
                            >
                                <option value="1" @selected(old('deposit_enabled', $item->deposit_enabled ?? 1) == 1)>{{ __('Enabled') }}</option>
                                <option value="0" @selected(old('deposit_enabled', $item->deposit_enabled ?? 1) == 0)>{{ __('Disabled') }}</option>
                            </select>
                            @error('deposit_enabled')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Withdrawal Enabled') }}:</label>
                        <div class="col-lg-9">
                            <select
                                name="withdrawal_enabled"
                                class="form-control @error('withdrawal_enabled') is-invalid @enderror"
                            >
                                <option value="1" @selected(old('withdrawal_enabled', $item->withdrawal_enabled ?? 1) == 1)>{{ __('Enabled') }}</option>
                                <option value="0" @selected(old('withdrawal_enabled', $item->withdrawal_enabled ?? 1) == 0)>{{ __('Disabled') }}</option>
                            </select>
                            @error('withdrawal_enabled')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Minimum Withdrawal Amount') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="number"
                                step="0.01"
                                name="minimum_withdrawal_amount"
                                class="form-control @error('minimum_withdrawal_amount') is-invalid @enderror"
                                placeholder="{{ __('Minimum Withdrawal Amount') }}"
                                value="{{ old('minimum_withdrawal_amount', $item->minimum_withdrawal_amount ?? '') }}"
                            />
                            @error('minimum_withdrawal_amount')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Maximum Withdrawal Amount') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="number"
                                step="0.01"
                                name="maximum_withdrawal_amount"
                                class="form-control @error('maximum_withdrawal_amount') is-invalid @enderror"
                                placeholder="{{ __('Maximum Withdrawal Amount') }}"
                                value="{{ old('maximum_withdrawal_amount', $item->maximum_withdrawal_amount ?? '') }}"
                            />
                            @error('maximum_withdrawal_amount')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-danger" onclick="history.back()"><i class="ph-arrow-left me-2"></i> {{ __('Back') }} </button>
                        <button type="submit" class="btn btn-primary">{{ __('Submit') }} <i class="ph-paper-plane-tilt ms-2"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
