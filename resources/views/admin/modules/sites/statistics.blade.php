@extends('admin.layouts.app')
@section('title', $title)
@section('content')
    <div class="content">
        <div class="card">
            <div class="card-header d-flex">
                <h5 class="mb-0">{{ $module }}</h5>
            </div>

            <div class="card-body">
                <form action="" method="GET" id="searchForm">
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="mb-3">
                                <input type="text" id="creation_date_range" name="creation_date_range" class="form-control daterange-picker" placeholder="{{ __('Select date range') }}" value="{{ request('created_from', date('Y-m-d')) . ' - ' . request('created_to', date('Y-m-d')) }}">
                                <input type="hidden" name="created_from" value="{{ request('created_from', date('Y-m-d')) }}">
                                <input type="hidden" name="created_to" value="{{ request('created_to', date('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary btn-labeled btn-labeled-start me-2">
                                    <span class="btn-labeled-icon bg-black bg-opacity-20">
                                        <i class="ph-magnifying-glass"></i>
                                    </span>
                                    {{ __('Search') }}
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                                    <i class="ph-x"></i>
                                    {{ __('Clear Filters') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-header d-flex">
                <h5 class="mb-0">{{ $module }} {{ $createdFrom }} - {{ $createdTo }}</h5>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header d-flex">
                                <h6 class="mb-0"><i class="icon-move-down"></i> {{ __('Pay in Statistics') }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">{{ __('Total Amount') }}</label>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <input type="number" disabled="disabled" class="form-control" value="{{ $transaction_amount }}">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">{{ __('Total Fee Amount') }}</label>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <input type="number" disabled="disabled" class="form-control" value="{{ $transaction_fee_amount }}">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">{{ __('Grand Total') }}</label>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <input type="number" disabled="disabled" class="form-control" value="{{ $transaction_total_amount }}">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">{{ __('Count') }}</label>
                                    <div class="col-lg-8">
                                            <input type="number" disabled="disabled" class="form-control" value="{{ $transaction_count }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header d-flex">
                                <h6 class="mb-0"><i class="icon-move-up"></i> {{ __('Pay out statistics') }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">{{ __('Total Amount') }}</label>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <input type="number" disabled="disabled"  class="form-control" value="{{ $withdrawal_amount }}">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">{{ __('Total Fee Amount') }}</label>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <input type="number" disabled="disabled"  class="form-control" value="{{ $withdrawal_fee_amount }}">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">{{ __('Grand Total') }}</label>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <input type="number" disabled="disabled"  class="form-control" value="{{ $withdrawal_total_amount }}">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">{{ __('Count') }}</label>
                                    <div class="col-lg-8">
                                        <input type="number" disabled="disabled" class="form-control" value="{{ $withdrawal_count }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex">
                                <h6 class="mb-0"><i class="icon-move-up"></i> {{ __('Total statistics') }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">{{ __('Total Amount') }}</label>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <input type="number" disabled="disabled"  class="form-control" value="{{ $total }}">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">{{ __('Settlement Fee') }}</label>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <input type="number" disabled="disabled"  class="form-control" value="{{ $settlement_fee }}">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">{{ __('Settlement Fee Amount') }}</label>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <input type="number" disabled="disabled"  class="form-control" value="{{ $settlement_fee_amount }}">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">{{ __('Total with Settlement Fee') }}</label>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <input type="number" disabled="disabled"  class="form-control" value="{{ $total_with_settlement_fee }}">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script>

        // Module-specific functionality for transactions
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize daterange pickers
            initializeDateRangePickers();
        });
    </script>
@endpush
