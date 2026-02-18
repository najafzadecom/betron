@extends('vendor.layouts.app')
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
                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Date Range') }}</label>
                                <input type="text" id="creation_date_range" name="creation_date_range"
                                       class="form-control daterange-picker"
                                       placeholder="{{ __('Select date range') }}"
                                       value="{{ request('created_from', date('Y-m-d')) . ' - ' . request('created_to', date('Y-m-d')) }}">
                                <input type="hidden" name="created_from" value="{{ request('created_from', date('Y-m-d')) }}">
                                <input type="hidden" name="created_to" value="{{ request('created_to', date('Y-m-d')) }}">
                            </div>
                        </div>
                        @if(count($childVendors) > 0)
                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Child Vendor') }}</label>
                                <select name="child_vendor_id" class="form-select">
                                    <option value="">{{ __('All Child Vendors') }}</option>
                                    @foreach($childVendors as $childVendor)
                                        <option
                                            value="{{ $childVendor->id }}"{{ request('child_vendor_id') == $childVendor->id ? ' selected' : '' }}>
                                            {{ $childVendor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif
                        <div class="col-12 col-md-6 col-lg-2 d-flex align-items-end">
                            <div class="mb-3 w-100">
                                <div class="d-flex gap-2 flex-column flex-md-row">
                                    <button type="submit" class="btn btn-primary w-100 w-md-auto">
                                        <i class="ph-magnifying-glass me-1"></i> {{ __('Search') }}
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary w-100 w-md-auto" onclick="clearFilters()">
                                        <i class="ph-x me-1"></i> {{ __('Clear Filters') }}
                                    </button>
                                </div>
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
                                <h6 class="mb-0"><i class="ph-chart-bar-horizontal"></i> Ödeme Statistik Bölümü</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">Toplam Tutar</label>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <input type="number" disabled="disabled" class="form-control" value="{{ $totalTransactionsAmount }}">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">Toplam <span class="text-success"><strong>Onaylanan</strong></span>
                                        Tutar</label>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <input type="number" disabled="disabled" class="form-control" value="{{ $acceptedTransactionsAmount }}">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">Toplam <span class="text-danger"><strong>Reddedilen</strong></span>
                                        Tutar</label>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <input type="number" disabled="disabled" class="form-control" value="{{ $rejectedTransactionsAmount }}">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">Toplam <span class="text-danger"><strong>Beklemede</strong></span>
                                        Tutar</label>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <input type="number" disabled="disabled" class="form-control" value="{{ $pendingTransactionsAmount }}">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex">
                                <h6 class="mb-0"><i class="ph-chart-bar-horizontal"></i> Çekme Statistik Bölümü</h6>
                            </div>

                            <div class="card-body">
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">Toplam Tutar</label>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <input type="number" disabled="disabled" class="form-control" value="{{ $totalWithdrawalsAmount }}">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">Toplam <span class="text-success"><strong>Onaylanan</strong></span>
                                        Tutar</label>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <input type="number" disabled="disabled"  class="form-control" value="{{ $acceptedWithdrawalsAmount }}">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">Toplam <span class="text-danger"><strong>Reddedilen</strong></span>
                                        Tutar</label>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <input type="number" disabled="disabled"  class="form-control" value="{{ $rejectedWithdrawalsAmount }}">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">Toplam <span class="text-warning"><strong>Beklemede</strong></span>
                                        Tutar</label>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <input type="number" disabled="disabled"  class="form-control" value="{{ $pendingWithdrawalsAmount }}">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header d-flex">
                                <h6 class="mb-0"><i class="ph-chart-bar-horizontal"></i> Ödeme Statistik Bölümü</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">Toplam İşlem Sayı</label>
                                    <div class="col-lg-8">
                                        <input type="number" disabled="disabled"  class="form-control" value="{{ $totalTransactions }}">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">Toplam <span class="text-success"><strong>Onaylanan</strong></span>
                                        İşlem Sayı</label>
                                    <div class="col-lg-8">
                                        <input type="number" disabled="disabled"  class="form-control" value="{{ $acceptedTransactions }}">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">Toplam <span class="text-danger"><strong>Reddedilen</strong></span>
                                        İşlem Sayı</label>
                                    <div class="col-lg-8">
                                        <input type="number" disabled="disabled"  class="form-control" value="{{ $rejectedTransactions }}">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">Toplam <span class="text-warning"><strong>Beklemede</strong></span>
                                        İşlem Sayı</label>
                                    <div class="col-lg-8">
                                        <input type="number" disabled="disabled"  class="form-control" value="{{ $pendingTransactions }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex">
                                <h6 class="mb-0"><i class="ph-chart-bar-horizontal"></i> Çekme Statistik Bölümü</h6>
                            </div>

                            <div class="card-body">
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">Toplam İşlem Sayı</label>
                                    <div class="col-lg-8">
                                        <input type="number" disabled="disabled"  class="form-control" value="{{ $totalWithdrawals }}">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">Toplam <span class="text-success"><strong>Onaylanan</strong></span>
                                        İşlem Sayı</label>
                                    <div class="col-lg-8">
                                        <input type="number" disabled="disabled"  class="form-control" value="{{ $acceptedWithdrawals }}">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">Toplam <span class="text-danger"><strong>Reddedilen</strong></span>
                                        İşlem Sayı</label>
                                    <div class="col-lg-8">
                                        <input type="number" disabled="disabled"  class="form-control" value="{{ $rejectedWithdrawals }}">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-lg-4">Toplam <span class="text-warning"><strong>Beklemede</strong></span>
                                        İşlem Sayı</label>
                                    <div class="col-lg-8">
                                        <input type="number" disabled="disabled"  class="form-control" value="{{ $pendingWithdrawals }}">
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

