@extends('admin.layouts.app')
@section('title', $title)
@section('content')
    <div class="content">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <h5 class="mb-0">{{ $title }}</h5>
                <div class="ms-auto d-flex gap-2">
                    <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-primary">
                        <i class="ph-arrow-left me-1"></i> {{ __('Back to Vendors') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="" method="GET" id="searchForm">
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('General Search') }}</label>
                                <input type="text" name="search" class="form-control" placeholder="{{ __('Search in vendor name, email, note...') }}" value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Parent Vendor') }}</label>
                                <select id="parent_vendor_filter" name="parent_vendor_id" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach($topLevelVendors as $vendor)
                                        <option value="{{ $vendor->id }}"{{ request('parent_vendor_id') == $vendor->id ? ' selected' : '' }}>
                                            {{ $vendor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Child Vendor') }}</label>
                                <select id="vendor_filter" name="vendor_id" class="form-select" {{ !request('parent_vendor_id') ? 'disabled' : '' }}>
                                    <option value="">{{ __('All') }}</option>
                                    @foreach($childVendors as $vendor)
                                        <option value="{{ $vendor->id }}"{{ request('vendor_id') == $vendor->id ? ' selected' : '' }}>
                                            {{ $vendor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

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
                        <th>{{ __('Vendor') }}</th>
                        <th>{{ __('Parent Vendor') }}</th>
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
                            <td>
                                <a href="{{ route('admin.vendors.deposit-transactions', $item->vendor_id) }}" class="text-primary">
                                    {{ $item->vendor?->name ?? __('Unknown') }}
                                </a>
                                <br>
                                <small class="text-muted">{{ $item->vendor?->email ?? '' }}</small>
                            </td>
                            <td>
                                @isset($item->vendor->parent)
                                    <a href="{{ route('admin.vendors.deposit-transactions', $item->vendor?->parent?->id) }}" class="text-info">
                                        {{ $item->vendor?->parent?->name ?? __('Unknown') }}
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ $item->vendor?->parent?->email ?? '-' }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endisset
                            </td>
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
                            <td colspan="10" class="text-center">{{ __('No transactions found') }}</td>
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
        document.addEventListener('DOMContentLoaded', function () {
            // Parent vendor filter change handler
            const parentVendorFilter = document.getElementById('parent_vendor_filter');
            const vendorFilter = document.getElementById('vendor_filter');

            if (parentVendorFilter && vendorFilter) {
                parentVendorFilter.addEventListener('change', function() {
                    const parentId = this.value;

                    if (parentId) {
                        // Show loading state
                        vendorFilter.disabled = true;
                        vendorFilter.innerHTML = '<option value="">{{ __("Loading...") }}</option>';

                        // Get current selected vendor_id from URL params
                        const urlParams = new URLSearchParams(window.location.search);
                        const selectedVendorId = urlParams.get('vendor_id');

                        // Fetch child vendors via AJAX
                        fetch('{{ route("admin.vendor-users.get-child-vendors", ":id") }}'.replace(':id', parentId))
                            .then(response => response.json())
                            .then(data => {
                                // Clear and populate vendor filter with child vendors
                                vendorFilter.innerHTML = '<option value="">{{ __("All") }}</option>';

                                if (data.vendors && data.vendors.length > 0) {
                                    data.vendors.forEach(function(vendor) {
                                        const option = document.createElement('option');
                                        option.value = vendor.id;
                                        option.textContent = vendor.name;
                                        // Set selected if this vendor was previously selected
                                        if (selectedVendorId && selectedVendorId == vendor.id) {
                                            option.selected = true;
                                        }
                                        vendorFilter.appendChild(option);
                                    });
                                }

                                vendorFilter.disabled = false;
                            })
                            .catch(error => {
                                console.error('Error fetching child vendors:', error);
                                vendorFilter.innerHTML = '<option value="">{{ __("All") }}</option>';
                                vendorFilter.disabled = false;
                            });
                    } else {
                        // Reset vendor filter
                        vendorFilter.innerHTML = '<option value="">{{ __("All") }}</option>';
                        vendorFilter.disabled = true;
                    }
                });

                // Trigger change on page load if parent vendor is selected
                @if(request('parent_vendor_id'))
                    const initialParentId = {{ request('parent_vendor_id') }};
                    const initialVendorId = '{{ request('vendor_id') }}';
                    if (initialParentId) {
                        parentVendorFilter.value = initialParentId;

                        // Manually trigger the change handler with the original vendor_id
                        const parentId = initialParentId;
                        vendorFilter.disabled = true;
                        vendorFilter.innerHTML = '<option value="">{{ __("Loading...") }}</option>';

                        fetch('{{ route("admin.vendor-users.get-child-vendors", ":id") }}'.replace(':id', parentId))
                            .then(response => response.json())
                            .then(data => {
                                vendorFilter.innerHTML = '<option value="">{{ __("All") }}</option>';

                                if (data.vendors && data.vendors.length > 0) {
                                    data.vendors.forEach(function(vendor) {
                                        const option = document.createElement('option');
                                        option.value = vendor.id;
                                        option.textContent = vendor.name;
                                        // Set selected if this vendor was previously selected
                                        if (initialVendorId && initialVendorId == vendor.id) {
                                            option.selected = true;
                                        }
                                        vendorFilter.appendChild(option);
                                    });
                                }

                                vendorFilter.disabled = false;
                            })
                            .catch(error => {
                                console.error('Error fetching child vendors:', error);
                                vendorFilter.innerHTML = '<option value="">{{ __("All") }}</option>';
                                vendorFilter.disabled = false;
                            });
                    }
                @endif
            }
        });

        function clearFilters() {
            window.location.href = '{{ route('admin.vendors.deposit-transactions.all') }}';
        }

        function changeLimit(limit) {
            const url = new URL(window.location.href);
            url.searchParams.set('limit', limit);
            window.location.href = url.toString();
        }
    </script>
@endpush

