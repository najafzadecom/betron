@extends('admin.layouts.app')
@section('title', $title)
@section('content')
    <div class="content">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <h5 class="mb-0">{{ $module }}</h5>
                <div class="ms-auto d-flex gap-2">
                    <x-buttons.create title="{{ __('Create') }}" url="{{ route('admin.wallets.create') }}"
                                      permission="wallets-create"/>
                </div>
            </div>
            <div class="card-body">
                <form action="" method="GET" id="searchForm">
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('General Search') }}</label>
                                <input type="text" name="search" class="form-control"
                                       placeholder="{{ __('Search in all fields...') }}"
                                       value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Account Name') }}</label>
                                <input type="text" name="name" class="form-control"
                                       placeholder="{{ __('Account name') }}"
                                       value="{{ request('name') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('IBAN') }}</label>
                                <input type="text" name="iban" class="form-control"
                                       placeholder="{{ __('IBAN') }}"
                                       value="{{ request('iban') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Bank') }}</label>
                                <select name="bank_id" class="form-select">
                                    <option value="">{{ __('All Banks') }}</option>
                                    @foreach($banks as $bank)
                                        <option
                                            value="{{ $bank->id }}"{{ request('bank_id') == $bank->id ? ' selected' : '' }}>
                                            {{ $bank->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select name="status" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach($wallet_statuses as $status)
                                        <option value="{{ $status->value }}"{{ (string) request('status') === (string) $status->value ? ' selected' : '' }}>{{ __($status->label()) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Description') }}</label>
                                <input type="text" name="description" class="form-control"
                                       placeholder="{{ __('Description') }}"
                                       value="{{ request('description') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Currency') }}</label>
                                <select name="currency" class="form-select">
                                    <option value="">{{ __('All Currencies') }}</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->value }}" @selected(request('currency') === $currency->value)>
                                            {{ $currency->name() }}
                                        </option>
                                    @endforeach
                                </select>
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
                                <label class="form-label">{{ __('Vendor') }}</label>
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
                                <label class="form-label">{{ __('Creation Date Range') }}</label>
                                <input type="text" id="creation_date_range" name="creation_date_range"
                                       class="form-control daterange-picker"
                                       placeholder="{{ __('Select date range') }}"
                                       value="{{ request('created_from') && request('created_to') ? request('created_from') . ' - ' . request('created_to') : '' }}">
                                <input type="hidden" name="created_from" value="{{ request('created_from') }}">
                                <input type="hidden" name="created_to" value="{{ request('created_to') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Update Date Range') }}</label>
                                <input type="text" id="update_date_range" name="update_date_range"
                                       class="form-control daterange-picker"
                                       placeholder="{{ __('Select date range') }}"
                                       value="{{ request('updated_from') && request('updated_to') ? request('updated_from') . ' - ' . request('updated_to') : '' }}">
                                <input type="hidden" name="updated_from" value="{{ request('updated_from') }}">
                                <input type="hidden" name="updated_to" value="{{ request('updated_to') }}">
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
                <table class="table text-nowrap table-xs">
                    <thead>
                    <tr>
                        {!! sortableTableHeader('id', 'ID', 'wallets') !!}
                        {!! sortableTableHeader('vendor_id', 'Vendor', 'wallets') !!}
                        {!! sortableTableHeader('vendor_id', 'Parent Vendor', 'wallets') !!}
                        {!! sortableTableHeader('name', 'Account Name', 'wallets') !!}
                        {!! sortableTableHeader('iban', 'Iban Information', 'wallets') !!}
                        {!! sortableTableHeader('bank', 'Bank Name', 'wallets') !!}
                        {!! sortableTableHeader('status', 'Status', 'wallets') !!}
                        <th class="text-center" style="width: 20px;">
                            <i class="ph-dots-three"></i>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->vendor?->name }}</td>
                            <td>{{ $item->vendor?->parent?->name ?? '-' }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->iban }}</td>
                            <td>{{ $item->bank?->name }}</td>
                            <td>
                                <x-badge :title="$item->status->label()" :color="$item->status->color()"/>
                            </td>
                            <td>
                                @canany(['wallets-show', 'wallets-edit', 'wallets-delete'])
                                    <div class="dropdown">
                                        <a href="#" class="text-body" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                            <i class="ph-list"></i>
                                        </a>

                                        <div class="dropdown-menu dropdown-menu-end" data-popper-placement="top-start"
                                             data-popper-reference-hidden="">
                                            <div class="dropdown-header">{{ __('Options') }}</div>
                                            @can('wallets-show')
                                                <a href="#" class="dropdown-item"
                                                   data-url="{{ route('admin.wallets.show', $item->id) }}"
                                                   data-bs-toggle="modal" data-bs-target="#show_modal">
                                                    <i class="ph-eye me-2"></i>
                                                    {{ __('Show wallet') }}
                                                </a>
                                            @endcan
                                            @can('wallets-edit')
                                                <a href="{{ route('admin.wallets.edit', $item->id) }}"
                                                   class="dropdown-item">
                                                    <i class="ph-pen me-2"></i>
                                                    {{ __('Edit wallet') }}
                                                </a>
                                            @endcan
                                            @can('wallets-delete')
                                                <a href="#" class="dropdown-item text-danger"
                                                   data-delete-url="{{ route('admin.wallets.destroy', $item->id) }}"
                                                   data-item-name="account {{ $item->name }}">
                                                    <i class="ph-trash me-2"></i>
                                                    {{ __('Delete wallet') }}
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                @endcanany
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">{{ __('Data not found') }}</td>
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
                                <option
                                    value="{{ $limit }}"{{ request('limit', 25) == $limit ? ' selected' : '' }}>{{ $limit }}</option>
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

    <div id="show_modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Show wallet') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('ID') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="id">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Current Account') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="current-account">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Account Name') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="name">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('IBAN') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="iban">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Total Amount') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="total-amount">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Blocked Amount') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="blocked-amount">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Available Amount') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="available-amount">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Bank') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="bank">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Currency') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="currency">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Status') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="status">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Description') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="description">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Last Sync Date') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="last-sync-date">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Created At') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="created-at">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Updated At') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="updated-at">-</div>
                    </div>
                </div>


                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Module-specific functionality for wallets
        document.addEventListener('DOMContentLoaded', function () {
            // Custom modal field mapping for wallets
            const showModal = document.getElementById('show_modal');
            if (!showModal) return;

            showModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const url = button.getAttribute('data-url');

                fetch(url)
                    .then(response => response.json())
                    .then(responseData => {
                        const data = responseData.item;

                        // Use the common modal handler first
                        Object.keys(data).forEach(key => {
                            const element = document.getElementById(key.replace(/_/g, '-'));
                            if (element) {
                                if (key === 'created_at' || key === 'updated_at' || key === 'last_sync_date') {
                                    element.innerText = data[key] ? new Date(data[key]).toLocaleString() : '-';
                                } else if (key.endsWith('_display') || key.endsWith('_html')) {
                                    element.innerHTML = data[key] ?? '-';
                                } else {
                                    element.innerText = data[key] ?? '-';
                                }
                            }
                        });

                        // Handle wallet-specific fields
                        if (data.total_amount && data.currency) {
                            document.getElementById('total-amount').innerText = formatAmountWithCurrency(data.total_amount, data.currency);
                        }

                        if (data.blocked_amount && data.currency) {
                            document.getElementById('blocked-amount').innerText = formatAmountWithCurrency(data.blocked_amount, data.currency);
                        }

                        // Calculate and show available amount
                        if (data.total_amount && data.blocked_amount && data.currency) {
                            const availableAmount = parseFloat(data.total_amount) - parseFloat(data.blocked_amount);
                            document.getElementById('available-amount').innerText = formatAmountWithCurrency(availableAmount, data.currency);
                        }

                        // Handle status formatting
                        if (data.status !== undefined) {
                            const statusElement = document.getElementById('status');
                            if (data.status === 1 || data.status === true) {
                                statusElement.innerHTML = '<span class="badge bg-success bg-opacity-10 text-success">{{ __("Active") }}</span>';
                            } else {
                                statusElement.innerHTML = '<span class="badge bg-danger bg-opacity-10 text-danger">{{ __("Inactive") }}</span>';
                            }
                        }

                        // Handle description with text wrapping
                        if (data.description) {
                            const descElement = document.getElementById('description');
                            descElement.style.whiteSpace = 'normal';
                            descElement.style.wordWrap = 'break-word';
                            descElement.innerText = data.description;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching wallet data:', error);
                    });
            });

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

                        // Fetch child vendors via AJAX
                        fetch('{{ route("admin.vendor-users.get-child-vendors", ":id") }}'.replace(':id', parentId))
                            .then(response => response.json())
                            .then(data => {
                                // Clear and populate vendor filter with child vendors
                                vendorFilter.innerHTML = '<option value="">{{ __("All") }}</option>';

                                const selectedVendorId = {{ request('vendor_id') ? (int)request('vendor_id') : 'null' }};

                                data.vendors.forEach(function(vendor) {
                                    const option = document.createElement('option');
                                    option.value = vendor.id;
                                    option.textContent = vendor.name;
                                    if (selectedVendorId && vendor.id == selectedVendorId) {
                                        option.selected = true;
                                    }
                                    vendorFilter.appendChild(option);
                                });

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
                    if (initialParentId) {
                        parentVendorFilter.value = initialParentId;
                        parentVendorFilter.dispatchEvent(new Event('change'));
                    }
                @endif
            }
        });
    </script>
@endpush
