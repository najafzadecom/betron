@extends('admin.layouts.app')
@section('title', $title)

@section('content')
    <div class="content">
        <div class="card mb-3">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <h5 class="mb-0">{{ $module }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.vendor-reconciliations.index') }}" method="GET" id="reconciliationFilterForm">
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Parent Vendor') }}</label>
                                <select id="parent_vendor_filter" name="parent_vendor_id" class="form-select">
                                    <option value="">{{ __('Select') }}</option>
                                    @foreach($topLevelVendors as $v)
                                        <option value="{{ $v->id }}" @selected($parentVendorId == $v->id)>{{ $v->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Vendor') }}</label>
                                <select id="vendor_filter" name="vendor_id" class="form-select" @disabled(!$parentVendorId)>
                                    <option value="">{{ __('Select') }}</option>
                                    @if($parentVendorId)
                                        <option value="{{ $parentVendorId }}" @selected($vendorId == $parentVendorId)>{{ __('Parent (self)') }}</option>
                                    @endif
                                    @foreach($childVendors as $v)
                                        <option value="{{ $v->id }}" @selected($vendorId == $v->id)>{{ $v->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Year') }}</label>
                                <select name="year" class="form-select">
                                    @for($y = (int) date('Y'); $y >= (int) date('Y') - 3; $y--)
                                        <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Month') }}</label>
                                <select name="month" class="form-select">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" @selected($month == $m)>{{ str_pad((string) $m, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-2 d-flex align-items-end">
                            <div class="mb-3 w-100">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ph-magnifying-glass me-1"></i> {{ __('Show') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(!$vendorId)
            <div class="alert alert-info">{{ __('Select a vendor to view daily reconciliations.') }}</div>
        @else
            <div class="row">
                {{-- Sol: günlük liste --}}
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-header py-2">
                            <h6 class="mb-0">{{ __('Days') }} — {{ $vendor?->name }}</h6>
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
                                <a href="{{ route('admin.vendor-reconciliations.index', [
                                    'vendor_id' => $vendorId,
                                    'parent_vendor_id' => $parentVendorId,
                                    'year' => $year,
                                    'month' => $month,
                                    'date' => $day['date'],
                                ]) }}"
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

                {{-- Sağ: mutabakat formu --}}
                <div class="col-lg-9">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap align-items-center gap-2">
                            <h5 class="mb-0">{{ __('Reconciliation') }} — {{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}</h5>
                            @if($reconciliation)
                                <span class="badge {{ $reconciliation->status->badgeClass() }} ms-2">
                                    {{ $reconciliation->status->label() }}
                                </span>
                            @endif
                            <div class="ms-auto d-flex flex-wrap gap-2">
                                @if(!$reconciliation)
                                    @can('vendor-reconciliations-edit')
                                        <form method="POST" action="{{ route('admin.vendor-reconciliations.load-day') }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="vendor_id" value="{{ $vendorId }}">
                                            <input type="hidden" name="parent_vendor_id" value="{{ $parentVendorId }}">
                                            <input type="hidden" name="year" value="{{ $year }}">
                                            <input type="hidden" name="month" value="{{ $month }}">
                                            <input type="hidden" name="date" value="{{ $date }}">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="ph-plus me-1"></i> {{ __('Start Reconciliation') }}
                                            </button>
                                        </form>
                                    @endcan
                                @else
                                    @if($reconciliation->status === \App\Enums\VendorReconciliationStatus::Draft)
                                        @can('vendor-reconciliations-edit')
                                            <form method="POST" action="{{ route('admin.vendor-reconciliations.refresh', $reconciliation->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-secondary btn-sm">
                                                    <i class="ph-arrows-clockwise me-1"></i> {{ __('Refresh from System') }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.vendor-reconciliations.approve', $reconciliation->id) }}" class="d-inline"
                                                  onsubmit="return confirm('{{ __('Approve this reconciliation?') }}')">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="ph-check me-1"></i> {{ __('Approve') }}
                                                </button>
                                            </form>
                                        @endcan
                                    @elseif($reconciliation->status === \App\Enums\VendorReconciliationStatus::Approved)
                                        @can('vendor-reconciliations-edit')
                                            <form method="POST" action="{{ route('admin.vendor-reconciliations.archive', $reconciliation->id) }}" class="d-inline"
                                                  onsubmit="return confirm('{{ __('Archive this reconciliation?') }}')">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary btn-sm">
                                                    <i class="ph-archive me-1"></i> {{ __('Archive') }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.vendor-reconciliations.reopen', $reconciliation->id) }}" class="d-inline"
                                                  onsubmit="return confirm('{{ __('Return to draft for editing?') }}')">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-warning btn-sm">
                                                    <i class="ph-pencil-simple me-1"></i> {{ __('Edit Again') }}
                                                </button>
                                            </form>
                                        @endcan
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="card-body">
                            @if(session('success') && session('message'))
                                <div class="alert alert-success alert-dismissible fade show">
                                    {{ session('message') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if($reconciliation)
                                @php $isDraft = $reconciliation->status === \App\Enums\VendorReconciliationStatus::Draft; @endphp
                                <form method="POST"
                                      action="{{ $isDraft ? route('admin.vendor-reconciliations.update', $reconciliation->id) : '#' }}"
                                      id="reconciliationForm">
                                    @if($isDraft)
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="parent_vendor_id" value="{{ $parentVendorId }}">
                                    @endif

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm reconciliation-table mb-0">
                                            <thead class="table-light">
                                            <tr>
                                                <th style="width: 40%">{{ __('Field') }}</th>
                                                <th class="text-end">{{ __('Amount (₺)') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @php
                                                $yOran = old('y_komisyon_oran', $reconciliation->y_komisyon_oran ?? \App\Services\VendorReconciliationService::DEFAULT_COMMISSION_RATE);
                                                $tOran = old('t_komisyon_oran', $reconciliation->t_komisyon_oran ?? \App\Services\VendorReconciliationService::DEFAULT_COMMISSION_RATE);
                                                $amountFields = [
                                                    'devir' => __('Carryover (Devir)'),
                                                    'yatirim' => __('Deposit (Yatırım)'),
                                                    'man_yatirim' => __('Manual Deposit'),
                                                    'cekim' => __('Withdrawal (Çekim)'),
                                                    'man_cekim' => __('Manual Withdrawal'),
                                                    'cekim_iptal' => __('Withdrawal Cancelled (Çekim İptal)'),
                                                    'teslimat' => __('Settlement (Teslimat)'),
                                                ];
                                            @endphp
                                            @foreach($amountFields as $field => $label)
                                                <tr>
                                                    <td class="fw-semibold">{{ $label }}</td>
                                                    <td>
                                                        <input type="number"
                                                               step="0.01"
                                                               name="{{ $field }}"
                                                               class="form-control form-control-sm text-end reconciliation-amount @error($field) is-invalid @enderror"
                                                               value="{{ old($field, $reconciliation->$field ?? 0) }}"
                                                               @disabled(!$isDraft)
                                                               data-field="{{ $field }}">
                                                        @error($field)
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <tr class="table-light">
                                                <td class="fw-semibold">
                                                    {{ __('Deposit Commission') }}
                                                    <div class="text-muted fs-sm fw-normal">{{ __('(Yatırım + Man. Yatırım) × oran') }}</div>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number"
                                                               step="0.01"
                                                               min="0"
                                                               max="100"
                                                               name="y_komisyon_oran"
                                                               id="y_komisyon_oran"
                                                               class="form-control text-end reconciliation-rate @error('y_komisyon_oran') is-invalid @enderror"
                                                               value="{{ $yOran }}"
                                                               @disabled(!$isDraft)>
                                                        <span class="input-group-text">%</span>
                                                        <input type="text"
                                                               id="y_komisyon_display"
                                                               class="form-control text-end bg-light"
                                                               value="{{ number_format(old('y_komisyon', $reconciliation->y_komisyon ?? 0), 2, '.', '') }}"
                                                               readonly
                                                               tabindex="-1">
                                                        <span class="input-group-text">₺</span>
                                                    </div>
                                                    @error('y_komisyon_oran')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                    @enderror
                                                </td>
                                            </tr>
                                            <tr class="table-light">
                                                <td class="fw-semibold">
                                                    {{ __('Settlement Commission') }}
                                                    <div class="text-muted fs-sm fw-normal">{{ __('Teslimat × oran') }}</div>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number"
                                                               step="0.01"
                                                               min="0"
                                                               max="100"
                                                               name="t_komisyon_oran"
                                                               id="t_komisyon_oran"
                                                               class="form-control text-end reconciliation-rate @error('t_komisyon_oran') is-invalid @enderror"
                                                               value="{{ $tOran }}"
                                                               @disabled(!$isDraft)>
                                                        <span class="input-group-text">%</span>
                                                        <input type="text"
                                                               id="t_komisyon_display"
                                                               class="form-control text-end bg-light"
                                                               value="{{ number_format(old('t_komisyon', $reconciliation->t_komisyon ?? 0), 2, '.', '') }}"
                                                               readonly
                                                               tabindex="-1">
                                                        <span class="input-group-text">₺</span>
                                                    </div>
                                                    @error('t_komisyon_oran')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                    @enderror
                                                </td>
                                            </tr>
                                            @if(!$isDraft)
                                                <tr class="d-none">
                                                    <td colspan="2">
                                                        <span>{{ __('Deposit commission rate') }}: {{ number_format($reconciliation->y_komisyon_oran ?? 4, 2) }}%</span>
                                                        <span class="ms-3">{{ __('Settlement commission rate') }}: {{ number_format($reconciliation->t_komisyon_oran ?? 4, 2) }}%</span>
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr class="table-primary">
                                                <td class="fw-bold">{{ __('Remaining (Kalan)') }}</td>
                                                <td>
                                                    <input type="text"
                                                           id="kalan_display"
                                                           class="form-control form-control-sm text-end fw-bold"
                                                           value="{{ number_format(old('kalan', $reconciliation->kalan ?? 0), 2, '.', '') }}"
                                                           readonly>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-3">
                                        <label class="form-label">{{ __('Notes') }}</label>
                                        <textarea name="notes" class="form-control" rows="2" @disabled(!$isDraft)>{{ old('notes', $reconciliation->notes) }}</textarea>
                                    </div>

                                    @if($reconciliation->approved_at)
                                        <p class="text-muted fs-sm mt-3 mb-0">
                                            {{ __('Approved at') }}: {{ $reconciliation->approved_at->format('d.m.Y H:i') }}
                                            @if($reconciliation->approver)
                                                — {{ $reconciliation->approver->name }}
                                            @endif
                                        </p>
                                    @endif
                                    @if($reconciliation->archived_at)
                                        <p class="text-muted fs-sm mb-0">
                                            {{ __('Archived at') }}: {{ $reconciliation->archived_at->format('d.m.Y H:i') }}
                                        </p>
                                    @endif

                                    @if($isDraft)
                                        @can('vendor-reconciliations-edit')
                                            <div class="text-end mt-3">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="ph-floppy-disk me-1"></i> {{ __('Save Draft') }}
                                                </button>
                                            </div>
                                        @endcan
                                    @endif
                                </form>

                                <p class="text-muted fs-sm mt-3 mb-0">
                                    {{ __('Formula') }}: {{ __('Kalan = Devir + Yatırım + Man.Yatırım − Çekim − Man.Çekim − Y.Komisyon − Teslimat − T.Komisyon') }}<br>
                                    {{ __('Y.Komisyon') }} = ({{ __('Yatırım') }} + {{ __('Manual Deposit') }}) × %{{ __('Deposit commission rate') }}<br>
                                    {{ __('T.Komisyon') }} = {{ __('Settlement (Teslimat)') }} × %{{ __('Settlement commission rate') }}
                                </p>
                            @else
                                <p class="text-muted mb-0">{{ __('No reconciliation for this day. Click "Start Reconciliation" to create a draft.') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const parentSelect = document.getElementById('parent_vendor_filter');
            const vendorSelect = document.getElementById('vendor_filter');

            if (parentSelect && vendorSelect) {
                parentSelect.addEventListener('change', function () {
                    const parentId = this.value;
                    if (!parentId) {
                        vendorSelect.innerHTML = '<option value="">{{ __("Select") }}</option>';
                        vendorSelect.disabled = true;
                        return;
                    }
                    fetch('{{ route('admin.vendor-users.get-child-vendors', ':id') }}'.replace(':id', parentId))
                        .then(r => r.json())
                        .then(data => {
                            let html = '<option value="">{{ __("Select") }}</option>';
                            html += '<option value="' + parentId + '">{{ __("Parent (self)") }}</option>';
                            (data.vendors || []).forEach(v => {
                                html += '<option value="' + v.id + '">' + v.name + '</option>';
                            });
                            vendorSelect.innerHTML = html;
                            vendorSelect.disabled = false;
                        });
                });
            }

            const kalanDisplay = document.getElementById('kalan_display');
            const yKomisyonDisplay = document.getElementById('y_komisyon_display');
            const tKomisyonDisplay = document.getElementById('t_komisyon_display');

            function num(name) {
                const el = document.querySelector('[name="' + name + '"]');
                return parseFloat(el?.value) || 0;
            }

            function calcYKomisyon() {
                const base = num('yatirim') + num('man_yatirim');
                const oran = num('y_komisyon_oran');
                return Math.round(base * oran / 100 * 100) / 100;
            }

            function calcTKomisyon() {
                const base = num('teslimat');
                const oran = num('t_komisyon_oran');
                return Math.round(base * oran / 100 * 100) / 100;
            }

            function recalculateAll() {
                if (yKomisyonDisplay) {
                    yKomisyonDisplay.value = calcYKomisyon().toFixed(2);
                }
                if (tKomisyonDisplay) {
                    tKomisyonDisplay.value = calcTKomisyon().toFixed(2);
                }
                if (!kalanDisplay) return;

                const yKom = parseFloat(yKomisyonDisplay?.value) || 0;
                const tKom = parseFloat(tKomisyonDisplay?.value) || 0;
                const kalan = num('devir') + num('yatirim') + num('man_yatirim') + num('cekim_iptal')
                    - num('cekim') - num('man_cekim') - yKom - num('teslimat') - tKom;
                kalanDisplay.value = (Math.round(kalan * 100) / 100).toFixed(2);
            }

            document.querySelectorAll('.reconciliation-amount, .reconciliation-rate').forEach(el => {
                el.addEventListener('input', recalculateAll);
            });
            recalculateAll();
        });
    </script>
@endpush

@push('styles')
    <style>
        .reconciliation-day-list .list-group-item.active {
            z-index: 1;
        }
        .reconciliation-table input:disabled {
            background-color: var(--bs-secondary-bg);
        }
    </style>
@endpush
