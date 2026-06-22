@extends('admin.layouts.app')
@section('title', $title)
@section('content')
    <div class="content">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <h5 class="mb-0">{{ $title }} - {{ $vendor->name }}</h5>
                <div class="ms-auto d-flex gap-2">
                    <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-primary">
                        <i class="ph-arrow-left me-1"></i> {{ __('Back to Vendors') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-6 col-xl-3">
                        <div class="card card-body">
                            <div class="d-flex align-items-center">
                                <i class="ph-storefront ph-2x text-primary me-3"></i>
                                <div class="flex-fill text-end">
                                    <h4 class="mb-0">{{ $vendor->name }}</h4>
                                    <span class="text-muted">{{ __('Vendor') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card card-body">
                            <div class="d-flex align-items-center">
                                <i class="ph-currency-circle-dollar ph-2x text-success me-3"></i>
                                <div class="flex-fill text-end">
                                    <h4 class="mb-0">{{ number_format($vendor->deposit_amount ?? 0, 2) }} ₺</h4>
                                    <span class="text-muted">{{ __('Current Deposit') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card card-body">
                            <div class="d-flex align-items-center">
                                <i class="ph-list-dashes ph-2x text-info me-3"></i>
                                <div class="flex-fill text-end">
                                    <h4 class="mb-0">{{ $items->total() }}</h4>
                                    <span class="text-muted">{{ __('Total Transactions') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="" method="GET" id="searchForm">
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Type') }}</label>
                                <select name="type" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="add"{{ request('type') == 'add' ? ' selected' : '' }}>{{ __('Add Deposit') }}</option>
                                    <option value="subtract"{{ request('type') == 'subtract' ? ' selected' : '' }}>{{ __('Subtract Deposit') }}</option>
                                    <option value="transaction"{{ request('type') == 'transaction' ? ' selected' : '' }}>{{ __('Transaction Deposit') }}</option>
                                    <option value="withdrawal"{{ request('type') == 'withdrawal' ? ' selected' : '' }}>{{ __('Withdrawal Deposit') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Creation Date Range') }}</label>
                                <input type="text" id="creation_date_range" name="creation_date_range"
                                       class="form-control daterange-picker"
                                       placeholder="{{ __('Select date range') }}"
                                       value="{{ request('created_from') && request('created_to') ? request('created_from') . ' - ' . request('created_to') : '' }}">
                                <input type="hidden" name="created_from" value="{{ request('created_from') }}">
                                <input type="hidden" name="created_to" value="{{ request('created_to') }}">
                            </div>
                        </div>

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

            <div class="table-responsive">
                <table class="table table-xs text-nowrap">
                    <thead>
                    <tr>
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Previous Balance') }}</th>
                        <th>{{ __('New Balance') }}</th>
                        <th>{{ __('Note') }}</th>
                        <th>{{ __('Created By') }}</th>
                        <th>{{ __('Created At') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{!! $item->type_badge !!}</td>
                            <td>
                                @php
                                    $badgeClass = match($item->type?->value ?? $item->type) {
                                        'add' => 'bg-success text-success',
                                        'withdrawal' => 'bg-primary text-primary',
                                        'subtract', 'transaction' => 'bg-warning text-warning',
                                        default => 'bg-secondary text-secondary',
                                    };
                                    $sign = in_array($item->type?->value ?? $item->type, ['add', 'withdrawal']) ? '+' : '-';
                                @endphp
                                <span class="badge {{ $badgeClass }} bg-opacity-10">
                                    {{ $sign }}{{ number_format($item->amount, 2) }} ₺
                                </span>
                            </td>
                            <td>{{ number_format($item->previous_balance, 2) }} ₺</td>
                            <td><strong>{{ number_format($item->new_balance, 2) }} ₺</strong></td>
                            <td>{{ $item->note ?? '-' }}</td>
                            <td>{{ $item->creator->name ?? __('System') }}</td>
                            <td>{{ $item->created_at->isoFormat('DD MMM YYYY HH:mm') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">{{ __('No transactions found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                <div class="row mb-3 align-items-center">
                    <div class="col-12 col-md-6 d-flex align-items-center mb-2 mb-md-0">
                        <label for="limit" class="me-2 mb-0">{{ __('Display') }}:</label>
                        <select id="limit" name="limit" class="form-select w-auto" onchange="changeLimit(this.value)">
                            @foreach(config('pagination.per_pages') as $limit)
                                <option value="{{ $limit }}"{{ request('limit', 25) == $limit ? ' selected' : '' }}>{{ $limit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        @if(method_exists($items, 'links'))
                            {{ $items->links() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function clearFilters() {
            window.location.href = '{{ route('admin.vendors.deposit-transactions', $vendor->id) }}';
        }

        function changeLimit(limit) {
            const url = new URL(window.location.href);
            url.searchParams.set('limit', limit);
            window.location.href = url.toString();
        }
    </script>
@endpush

