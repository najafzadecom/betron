@extends('admin.layouts.app')
@section('title', $title)

@section('content')
    <div class="content">
        <div class="card mb-3">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <h5 class="mb-0">{{ $module }}</h5>
                <span class="badge bg-secondary">{{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}</span>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.vendor-reconciliations.summary') }}" method="GET" class="row align-items-end">
                    <div class="col-12 col-md-4 col-lg-3">
                        <label class="form-label">{{ __('Date') }}</label>
                        <input type="date" name="date" class="form-control" value="{{ $date }}" required>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="ph-magnifying-glass me-1"></i> {{ __('Show') }}
                        </button>
                        <a href="{{ route('admin.vendor-reconciliations.index', ['date' => $date]) }}" class="btn btn-outline-secondary ms-1">
                            {{ __('Vendor Reconciliation') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-primary">
                    <div class="card-body text-center py-4">
                        <div class="text-muted text-uppercase fs-sm mb-1">{{ __('System Balance') }}</div>
                        <h2 class="mb-0 text-primary">{{ number_format($totals['kalan'] ?? 0, 2) }} ₺</h2>
                        <div class="text-muted fs-sm mt-2">{{ __('Total remaining for all vendors on this date') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3 g-2">
            @foreach([
                'devir' => __('Carryover (Devir)'),
                'yatirim' => __('Deposit (Yatırım)'),
                'man_yatirim' => __('Manual Deposit'),
                'cekim' => __('Withdrawal (Çekim)'),
                'man_cekim' => __('Manual Withdrawal'),
                'cekim_iptal' => __('Withdrawal Cancelled (Çekim İptal)'),
                'y_komisyon' => __('Deposit Commission'),
                'teslimat' => __('Settlement (Teslimat)'),
                't_komisyon' => __('Settlement Commission'),
            ] as $key => $label)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card card-body py-2">
                        <div class="text-muted fs-sm">{{ $label }}</div>
                        <div class="fw-semibold">{{ number_format($totals[$key] ?? 0, 2) }} ₺</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">{{ __('Vendor reconciliations') }} — {{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover mb-0 reconciliation-summary-table">
                    <thead class="table-light">
                    <tr>
                        <th>{{ __('Vendor') }}</th>
                        <th class="text-end">{{ __('Devir') }}</th>
                        <th class="text-end">{{ __('Yatırım') }}</th>
                        <th class="text-end">{{ __('Man. Yatırım') }}</th>
                        <th class="text-end">{{ __('Çekim') }}</th>
                        <th class="text-end">{{ __('Man. Çekim') }}</th>
                        <th class="text-end">{{ __('Çekim İptal') }}</th>
                        <th class="text-end">{{ __('Y.Kom.') }}</th>
                        <th class="text-end">{{ __('Teslimat') }}</th>
                        <th class="text-end">{{ __('T.Kom.') }}</th>
                        <th class="text-end">{{ __('Kalan') }}</th>
                        <th class="text-center">{{ __('Status') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($rows as $row)
                        <tr class="@if(!$row['exists']) table-warning @endif">
                            <td>
                                <span class="fw-semibold">{{ $row['vendor_name'] }}</span>
                                @if($row['parent_name'])
                                    <br><small class="text-muted">{{ $row['parent_name'] }}</small>
                                @endif
                                @if(!$row['exists'])
                                    <br><small class="text-warning-emphasis"><i class="ph-warning-circle me-1"></i>{{ __('Reconciliation not created') }}</small>
                                @endif
                            </td>
                            @foreach(['devir', 'yatirim', 'man_yatirim', 'cekim', 'man_cekim', 'cekim_iptal', 'y_komisyon', 'teslimat', 't_komisyon', 'kalan'] as $field)
                                <td class="text-end">{{ number_format($row['values'][$field] ?? 0, 2) }}</td>
                            @endforeach
                            <td class="text-center">
                                @if($row['exists'] && $row['status'])
                                    <span class="badge {{ $row['status']->badgeClass() }}">{{ $row['status']->label() }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted py-4">{{ __('Data not found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                    <tfoot class="table-primary fw-bold">
                    <tr>
                        <td>{{ __('Total') }}</td>
                        @foreach(['devir', 'yatirim', 'man_yatirim', 'cekim', 'man_cekim', 'cekim_iptal', 'y_komisyon', 'teslimat', 't_komisyon', 'kalan'] as $field)
                            <td class="text-end">{{ number_format($totals[$field] ?? 0, 2) }}</td>
                        @endforeach
                        <td></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
            @if($missingCount > 0)
                <div class="card-footer text-muted fs-sm">
                    <i class="ph-info me-1"></i>
                    {{ __(':count vendor(s) have no reconciliation for this date. Those rows are shown in yellow with zero values.', ['count' => $missingCount]) }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .reconciliation-summary-table .table-warning > td {
            --bs-table-bg: rgba(var(--bs-warning-rgb), 0.25);
        }
    </style>
@endpush
