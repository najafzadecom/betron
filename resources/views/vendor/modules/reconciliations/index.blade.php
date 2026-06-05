@extends('vendor.layouts.app')
@section('title', $title)

@section('content')
    <div class="content">
        <div class="card mb-3">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <h5 class="mb-0">{{ $module }}</h5>
                <span class="text-muted fs-sm">{{ $vendor->name }}</span>
            </div>
            <div class="card-body">
                <form action="{{ route('vendor.reconciliations.index') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label">{{ __('Year') }}</label>
                            <select name="year" class="form-select">
                                @for($y = (int) date('Y'); $y >= (int) date('Y') - 3; $y--)
                                    <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label">{{ __('Month') }}</label>
                            <select name="month" class="form-select">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" @selected($month == $m)>{{ str_pad((string) $m, 2, '0', STR_PAD_LEFT) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-12 col-md-4 col-lg-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="ph-magnifying-glass me-1"></i> {{ __('Show') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-header py-2">
                        <h6 class="mb-0">{{ __('Days') }}</h6>
                    </div>
                    <div class="list-group list-group-flush reconciliation-day-list" style="max-height: 70vh; overflow-y: auto;">
                        @foreach($days as $day)
                            @php
                                $isActive = $date === $day['date'];
                                $statusClass = match($day['status'] ?? null) {
                                    'approved' => 'list-group-item-success',
                                    'archived' => 'list-group-item-secondary',
                                    'draft' => 'list-group-item-warning',
                                    default => '',
                                };
                            @endphp
                            <a href="{{ route('vendor.reconciliations.index', ['year' => $year, 'month' => $month, 'date' => $day['date']]) }}"
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 {{ $statusClass }} @if($isActive) active @endif">
                                <div>
                                    <div class="fw-semibold">{{ $day['label'] }}</div>
                                    <small class="opacity-75">{{ $day['weekday'] }}</small>
                                </div>
                                <div class="text-end">
                                    @if($day['status'])
                                        <span class="badge {{ \App\Enums\VendorReconciliationStatus::from($day['status'])->badgeClass() }} mb-1">
                                            {{ \App\Enums\VendorReconciliationStatus::from($day['status'])->label() }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted">{{ __('Empty') }}</span>
                                    @endif
                                    @if($day['kalan'] !== null)
                                        <div class="fs-sm">{{ number_format($day['kalan'], 2) }} ₺</div>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="card @if(!$exists) border-warning @endif">
                    <div class="card-header d-flex flex-wrap align-items-center gap-2">
                        <h5 class="mb-0">{{ __('Reconciliation') }} — {{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}</h5>
                        @if($reconciliation)
                            <span class="badge {{ $reconciliation->status->badgeClass() }}">
                                {{ $reconciliation->status->label() }}
                            </span>
                        @else
                            <span class="badge bg-warning bg-opacity-10 text-warning">{{ __('Reconciliation not created') }}</span>
                        @endif
                    </div>

                    <div class="card-body">
                        @if(!$exists)
                            <div class="alert alert-warning mb-3">
                                <i class="ph-warning-circle me-1"></i>
                                {{ __('No reconciliation has been created for this day yet. All values are shown as zero.') }}
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th style="width: 45%">{{ __('Field') }}</th>
                                    <th class="text-end">{{ __('Amount (₺)') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach([
                                    'devir' => __('Carryover (Devir)'),
                                    'yatirim' => __('Deposit (Yatırım)'),
                                    'man_yatirim' => __('Manual Deposit'),
                                    'yatirim_iptal' => __('Deposit Cancelled (Yatırım İptal)'),
                                    'cekim' => __('Withdrawal (Çekim)'),
                                    'man_cekim' => __('Manual Withdrawal'),
                                    'cekim_iptal' => __('Withdrawal Cancelled (Çekim İptal)'),
                                ] as $field => $label)
                                    <tr>
                                        <td class="fw-semibold">{{ $label }}</td>
                                        <td class="text-end">{{ number_format($values[$field] ?? 0, 2) }} ₺</td>
                                    </tr>
                                @endforeach
                                <tr class="table-light">
                                    <td class="fw-semibold text-danger">
                                        − {{ __('Deposit Commission') }}
                                        <div class="text-muted fs-sm fw-normal">{{ number_format($values['y_komisyon_oran'] ?? 0, 2) }}%</div>
                                    </td>
                                    <td class="text-end">{{ number_format($values['y_komisyon'] ?? 0, 2) }} ₺</td>
                                </tr>
                                <tr class="table-warning">
                                    <td class="fw-semibold text-danger">− {{ __('Settlement (Teslimat)') }}</td>
                                    <td class="text-end">{{ number_format($values['teslimat'] ?? 0, 2) }} ₺</td>
                                </tr>
                                <tr class="table-light">
                                    <td class="fw-semibold text-danger">
                                        − {{ __('Settlement Commission') }}
                                        <div class="text-muted fs-sm fw-normal">{{ number_format($values['t_komisyon_oran'] ?? 0, 2) }}%</div>
                                    </td>
                                    <td class="text-end">{{ number_format($values['t_komisyon'] ?? 0, 2) }} ₺</td>
                                </tr>
                                <tr class="table-primary">
                                    <td class="fw-bold">{{ __('Remaining (Kalan)') }}</td>
                                    <td class="text-end fw-bold">{{ number_format($values['kalan'] ?? 0, 2) }} ₺</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        @if($reconciliation?->notes)
                            <div class="mt-3">
                                <label class="form-label text-muted">{{ __('Notes') }}</label>
                                <p class="mb-0">{{ $reconciliation->notes }}</p>
                            </div>
                        @endif

                        @if($reconciliation?->approved_at)
                            <p class="text-muted fs-sm mt-3 mb-0">
                                {{ __('Approved at') }}: {{ $reconciliation->approved_at->format('d.m.Y H:i') }}
                            </p>
                        @endif
                    </div>
                </div>

                <p class="text-muted fs-sm mt-2 mb-0">
                    {{ __('This page is read-only. Contact the administrator for changes.') }}
                </p>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .reconciliation-day-list .list-group-item.active {
            z-index: 1;
        }
    </style>
@endpush
