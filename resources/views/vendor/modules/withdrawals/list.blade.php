@extends('vendor.layouts.app')
@section('title', $title)
@section('content')
    <div class="content">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <h5 class="mb-0">{{ $module }}</h5>
                @if(count($childVendors) > 0)
                <div class="btn-group" role="group" aria-label="View type">
                    <input type="radio" class="btn-check" name="view_type" id="view_type_own" value="own"
                           {{ $viewType === 'own' ? 'checked' : '' }} onchange="changeViewType(this.value)">
                    <label class="btn btn-outline-primary btn-sm" for="view_type_own">
                        {{ __('My Withdrawals') }}
                    </label>

                    <input type="radio" class="btn-check" name="view_type" id="view_type_child_vendors" value="child_vendors"
                           {{ $viewType === 'child_vendors' ? 'checked' : '' }} onchange="changeViewType(this.value)">
                    <label class="btn btn-outline-primary btn-sm" for="view_type_child_vendors">
                        {{ __('Child Vendors Withdrawals') }}
                    </label>
                </div>
                @endif
                <div class="ms-auto d-flex gap-2">
                    @if(count($childVendors) > 0)
                    <button type="button" id="bulk_assign_vendor_btn" class="btn btn-primary btn-sm" style="display: none;" data-bs-toggle="modal" data-bs-target="#bulk_assign_vendor_modal">
                        <i class="ph-users me-1"></i> {{ __('Bulk Assign Vendor') }}
                    </button>
                    @endif
                    <x-buttons.export title="{{ __('Export') }}" url="{!! route('vendor.withdrawals.export', request()->query())  !!} " permission="withdrawals-export"/>
                </div>
            </div>

            <div class="card-body">
                <form action="" method="GET" id="searchForm">
                    <input type="hidden" name="view_type" id="view_type_input" value="{{ $viewType }}">
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
                                <label class="form-label">{{ __('Site User ID') }}</label>
                                <input type="number" name="user_id" class="form-control"
                                       placeholder="{{ __('Site User ID') }}"
                                       value="{{ request('user_id') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Order ID') }}</label>
                                <input type="number" name="order_id" class="form-control"
                                       placeholder="{{ __('Order ID') }}"
                                       value="{{ request('order_id') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Receiver') }}</label>
                                <input type="text" name="receiver" class="form-control"
                                       placeholder="{{ __('Receiver name') }}"
                                       value="{{ request('receiver') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Receiver IBAN') }}</label>
                                <input type="text" name="iban" class="form-control" placeholder="{{ __('IBAN') }}" value="{{ request('iban') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Currency') }}</label>
                                <select name="currency" class="form-select">
                                    <option value="">{{ __('All Currencies') }}</option>
                                    @foreach(\App\Enums\Currency::cases() as $currency)
                                        <option value="{{ $currency->name }}"{{ request('currency') == $currency->name ? ' selected' : '' }}>
                                            {{ $currency->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select name="status" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach(\App\Enums\WithdrawalStatus::cases() as $status)
                                        <option value="{{ $status->value }}" {{ (string) request('status') === (string)$status->value ? ' selected' : '' }}>{{ __($status->label()) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Paid Status') }}</label>
                                <select name="paid_status" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach($paid_statuses as $status)
                                        <option value="{{ $status->value }}" {{ (string) request('paid_status') === (string) $status->value ? ' selected' : '' }}>{{ __($status->label()) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if(count($childVendors) > 0)
                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Child Vendor') }}</label>
                                <select name="child_vendor_id" id="child_vendor_id" class="form-select" onchange="handleChildVendorChange(this.value)">
                                    <option value="">{{ __('All Child Vendors') }}</option>
                                    @foreach($childVendors as $childVendor)
                                        <option value="{{ $childVendor->id }}"{{ request('child_vendor_id') == $childVendor->id ? ' selected' : '' }}>
                                            {{ $childVendor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Wallet ID') }}</label>
                                <input type="number" name="wallet_id" class="form-control"
                                       placeholder="{{ __('Wallet ID') }}"
                                       value="{{ request('wallet_id') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Amount Range') }}</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="amount_min" class="form-control"
                                           placeholder="{{ __('Min') }}"
                                           value="{{ request('amount_min') }}">
                                    <span class="input-group-text">-</span>
                                    <input type="number" step="0.01" name="amount_max" class="form-control"
                                           placeholder="{{ __('Max') }}"
                                           value="{{ request('amount_max') }}">
                                </div>
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
                                <label class="form-label">{{ __('Accepted Date Range') }}</label>
                                <input type="text" id="accepted_date_range" name="accepted_date_range"
                                       class="form-control daterange-picker"
                                       placeholder="{{ __('Select date range') }}"
                                       value="{{ request('accepted_from') && request('accepted_to') ? request('accepted_from') . ' - ' . request('accepted_to') : '' }}">
                                <input type="hidden" name="accepted_from" value="{{ request('accepted_from') }}">
                                <input type="hidden" name="accepted_to" value="{{ request('accepted_to') }}">
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
                <table class="table text-nowrap table-xs table-striped table-hover">
                    <thead>
                    <tr>
                        @if(count($childVendors) > 0)
                        <th class="text-center" style="width: 20px;">
                            <input type="checkbox" id="select_all_checkbox" title="{{ __('Select All') }}">
                        </th>
                        @endif
                        {!! sortableTableHeader('status', 'Status', 'withdrawals') !!}
                        {!! sortableTableHeader('paid_status', 'Paid', 'withdrawals') !!}
                        {!! sortableTableHeader('first_name', 'Receiver', 'withdrawals') !!}
                        {!! sortableTableHeader('amount', 'Amount', 'withdrawals') !!}
                        {!! sortableTableHeader('created_at', 'Created At', 'withdrawals') !!}
                        {!! sortableTableHeader('accepted_at', 'Accepted At', 'withdrawals') !!}
                        <th>{{ __('Vendor') }}</th>
                        {!! sortableTableHeader('user_id', 'Site User ID', 'withdrawals') !!}
                        {!! sortableTableHeader('site_id', 'Site', 'withdrawals') !!}
                        {!! sortableTableHeader('order_id', 'Order', 'withdrawals') !!}
                        <th></th>
                        <th class="text-center" style="width: 20px;">
                            <i class="ph-dots-three"></i>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $item)
                        @php
                            // Vendor panelinde: mevcut vendor'ın kendi ve child vendor'ların withdrawal'ları seçilebilir
                            $canSelect = count($childVendors) > 0;
                        @endphp
                        <tr>
                            @if(count($childVendors) > 0)
                            <td>
                                @if($canSelect)
                                    <input type="checkbox" class="withdrawal-checkbox" value="{{ $item->id }}" data-withdrawal-id="{{ $item->id }}">
                                @endif
                            </td>
                            @endif
                            <td>{!! $item->status_html !!}</td>
                            <td><x-paid-status :paid="$item->paid_status" /></td>
                            <td>
                                {{ $item->receiver }}
                                <small class="text-muted">{{ $item->iban }}</small>
                            </td>
                            <td>
                                <span class="badge bg-indigo bg-opacity-10 text-indigo">{{ $item->currency->code() }} {{ number_format($item->amount, 2) }}</span>
                            </td>
                            <td>{{ $item->created_at->isoFormat('DD MMM YYYY HH:mm') }}</td>
                            <td>{{ $item->accepted_at?->isoFormat('DD MMM YYYY HH:mm') ?? '' }}</td>
                            <td>{{ $item->vendor?->name ?? '-' }}</td>
                            <td>{{ $item->user_id }}</td>
                            <td>{{ $item->site_name }}</td>
                            <td>{{ $item->order_id }}</td>
                            <td>
                                @if($item->payment_method?->value == 'manual' && $item->status->value == 1)
                                    <button class="btn btn-outline-success btn-sm approve-btn" data-id="{{ $item->id }}" data-type="withdrawal">{{ __('Approve') }}</button>
                                    <button class="btn btn-outline-danger btn-sm cancel-btn" data-id="{{ $item->id }}" data-type="withdrawal">{{ __('Cancel') }}</button>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" class="text-body" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                        <i class="ph-list"></i>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end" data-popper-placement="top-start"
                                         data-popper-reference-hidden="">
                                        <div class="dropdown-header">{{ __('Options') }}</div>
                                        <a href="#" class="dropdown-item"
                                           data-url="{{ route('vendor.withdrawals.show', $item->id) }}"
                                           data-bs-toggle="modal" data-bs-target="#show_modal">
                                            <i class="ph-eye me-2"></i>
                                            {{ __('Show withdrawal') }}
                                        </a>
                                        @if(count($childVendors) > 0)
                                        <div class="dropdown-divider"></div>
                                        <a href="#" class="dropdown-item"
                                           data-withdrawal-id="{{ $item->id }}"
                                           data-current-vendor-id="{{ $item->vendor_id }}"
                                           data-current-vendor-name="{{ $item->vendor?->name ?? '' }}"
                                           data-bs-toggle="modal" data-bs-target="#assign_vendor_modal">
                                            <i class="ph-user me-2"></i>
                                            {{ __('Assign Vendor') }}
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($childVendors) > 0 ? 15 : 14 }}">{{ __('Data not found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                <div class="row mb-3 align-items-center">
                    <div class="col-md-6 col-sm-12 mb-2 mb-md-0 d-flex align-items-center">
                        <label for="limit" class="me-2 mb-0">{{ __('Display') }}:</label>
                        <select id="limit" name="limit" class="form-select w-auto" onchange="changeLimit(this.value)">
                            @foreach(config('pagination.per_pages') as $limit)
                                <option
                                    value="{{ $limit }}"{{ request('limit', 25) == $limit ? ' selected' : '' }}>{{ $limit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-sm-12">
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
                    <h5 class="modal-title">{{ __('Show withdrawal') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('ID') }}:</div>
                        <div class="col-7 text-end" id="id">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Wallet ID') }}:</div>
                        <div class="col-7 text-end" id="wallet-id">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Receiver') }}:</div>
                        <div class="col-7 text-end" id="receiver">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Receiver IBAN') }}:</div>
                        <div class="col-7 text-end" id="iban">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Amount') }}:</div>
                        <div class="col-7 text-end" id="amount">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Currency') }}:</div>
                        <div class="col-7 text-end" id="currency">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Operation ID') }}:</div>
                        <div class="col-7 text-end" id="operation-id">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Status') }}:</div>
                        <div class="col-7 text-end" id="status-html">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Wallet Info') }}:</div>
                        <div class="col-7 text-end" id="wallet-info">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Created At') }}:</div>
                        <div class="col-7 text-end" id="created-at">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Updated At') }}:</div>
                        <div class="col-7 text-end" id="updated-at">-</div>
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

    @if(count($childVendors) > 0)
    <div id="assign_vendor_modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Assign Vendor') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="assign_vendor_form">
                    <div class="modal-body">
                        <input type="hidden" id="assign_vendor_withdrawal_id" name="withdrawal_id">

                        <div class="mb-3">
                            <label class="form-label">{{ __('Current Vendor') }}</label>
                            <input type="text" id="assign_vendor_current_vendor" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Sub-Vendor') }} <span class="text-danger">*</span></label>
                            <select id="assign_vendor_id" name="vendor_id" class="form-select">
                                <option value="">{{ __('Select Sub-Vendor') }}</option>
                                @foreach($childVendors as $childVendor)
                                    <option value="{{ $childVendor->id }}">{{ $childVendor->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="assign_vendor_set_processing" name="set_processing" value="1">
                                <label class="form-check-label" for="assign_vendor_set_processing">
                                    {{ __('Set status to Processing') }}
                                </label>
                            </div>
                            <small class="text-muted">{{ __('If checked, withdrawal status will be set to Processing after vendor assignment') }}</small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-link" data-bs-dismiss="modal">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ __('Assign') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if(count($childVendors) > 0)
    <div id="bulk_assign_vendor_modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Bulk Assign Vendor') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="bulk_assign_vendor_form">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Selected Withdrawals') }}</label>
                            <div id="bulk_selected_withdrawals" class="alert alert-info">
                                <span id="bulk_selected_count">0</span> {{ __('withdrawals selected') }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Sub-Vendor') }} <span class="text-danger">*</span></label>
                            <select id="bulk_assign_vendor_id" name="vendor_id" class="form-select">
                                <option value="">{{ __('Select Sub-Vendor') }}</option>
                                @foreach($childVendors as $childVendor)
                                    <option value="{{ $childVendor->id }}">{{ $childVendor->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ __('You can only assign to your sub-vendors') }}</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="bulk_assign_vendor_set_processing" name="set_processing" value="1">
                                <label class="form-check-label" for="bulk_assign_vendor_set_processing">
                                    {{ __('Set status to Processing') }}
                                </label>
                            </div>
                            <small class="text-muted">{{ __('If checked, withdrawal status will be set to Processing after vendor assignment') }}</small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-link" data-bs-dismiss="modal">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ __('Assign Selected to Sub-Vendor') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endsection

@push('scripts')
    <script>
        // Module-specific functionality for withdrawals
        document.addEventListener('DOMContentLoaded', function () {
            @if(count($childVendors) > 0)
            // Bulk assign vendor functionality
            const selectAllCheckbox = document.getElementById('select_all_checkbox');
            const withdrawalCheckboxes = document.querySelectorAll('.withdrawal-checkbox');
            const bulkAssignBtn = document.getElementById('bulk_assign_vendor_btn');
            const bulkAssignModal = document.getElementById('bulk_assign_vendor_modal');
            const bulkSelectedCount = document.getElementById('bulk_selected_count');

            // Function to update bulk assign button visibility
            function updateBulkAssignButton() {
                const selectedCount = document.querySelectorAll('.withdrawal-checkbox:checked').length;
                if (selectedCount > 0) {
                    bulkAssignBtn.style.display = 'block';
                } else {
                    bulkAssignBtn.style.display = 'none';
                }
            }

            // Function to update selected count
            function updateSelectedCount() {
                const selectedCount = document.querySelectorAll('.withdrawal-checkbox:checked').length;
                bulkSelectedCount.textContent = selectedCount;
            }

            // Select all checkbox handler
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    withdrawalCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateBulkAssignButton();
                    updateSelectedCount();
                });
            }

            // Individual checkbox handlers
            withdrawalCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateBulkAssignButton();
                    updateSelectedCount();

                    // Update select all checkbox state
                    const allChecked = Array.from(withdrawalCheckboxes).every(cb => cb.checked);
                    const someChecked = Array.from(withdrawalCheckboxes).some(cb => cb.checked);
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = allChecked;
                        selectAllCheckbox.indeterminate = someChecked && !allChecked;
                    }
                });
            });

            // Bulk assign vendor modal show handler
            if (bulkAssignModal) {
                bulkAssignModal.addEventListener('show.bs.modal', function() {
                    const selectedIds = Array.from(document.querySelectorAll('.withdrawal-checkbox:checked'))
                        .map(cb => cb.value);
                    updateSelectedCount();

                    // Reset form
                    document.getElementById('bulk_assign_vendor_id').value = '';
                    document.getElementById('bulk_assign_vendor_set_processing').checked = false;
                });
            }

            // Bulk assign vendor form submission
            const bulkAssignForm = document.getElementById('bulk_assign_vendor_form');
            if (bulkAssignForm) {
                bulkAssignForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const selectedIds = Array.from(document.querySelectorAll('.withdrawal-checkbox:checked'))
                        .map(cb => cb.value);

                    if (selectedIds.length === 0) {
                        alert('{{ __("No withdrawals selected") }}');
                        return;
                    }

                    const vendorId = document.getElementById('bulk_assign_vendor_id').value;
                    if (!vendorId) {
                        alert('{{ __("Please select a sub-vendor") }}');
                        return;
                    }

                    const setProcessing = document.getElementById('bulk_assign_vendor_set_processing').checked;

                    // Disable submit button
                    const submitButton = bulkAssignForm.querySelector('button[type="submit"]');
                    submitButton.disabled = true;
                    submitButton.textContent = '{{ __("Assigning...") }}';

                    fetch('{{ route("vendor.withdrawals.bulk-assign-vendor") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            withdrawal_ids: selectedIds,
                            vendor_id: vendorId,
                            set_processing: setProcessing ? 1 : 0
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.message) {
                            alert(data.message);
                            location.reload();
                        } else if (data.error) {
                            alert(data.error);
                            submitButton.disabled = false;
                            submitButton.textContent = '{{ __("Assign Selected to Sub-Vendor") }}';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('{{ __("An error occurred") }}');
                        submitButton.disabled = false;
                        submitButton.textContent = '{{ __("Assign Selected to Sub-Vendor") }}';
                    });
                });
            }
            @endif

            // Approve button handler
            document.querySelectorAll('.approve-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const type = this.getAttribute('data-type');

                    if (confirm('{{ __("Are you sure you want to approve this withdrawal?") }}')) {
                        fetch(`{{ url('vendor/withdrawals') }}/${id}/approve`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.message) {
                                alert(data.message);
                                location.reload();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('{{ __("An error occurred") }}');
                        });
                    }
                });
            });

            // Cancel button handler
            document.querySelectorAll('.cancel-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const type = this.getAttribute('data-type');

                    if (confirm('{{ __("Are you sure you want to cancel this withdrawal?") }}')) {
                        fetch(`{{ url('vendor/withdrawals') }}/${id}/cancel`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.message) {
                                alert(data.message);
                                location.reload();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('{{ __("An error occurred") }}');
                        });
                    }
                });
            });

            // Custom modal field mapping for withdrawals
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
                                if (key === 'created_at' || key === 'updated_at') {
                                    element.innerText = data[key] ? new Date(data[key]).toLocaleString() : '-';
                                } else if (key.endsWith('_display') || key.endsWith('_html')) {
                                    element.innerHTML = data[key] ?? '-';
                                } else {
                                    element.innerText = data[key] ?? '-';
                                }
                            }
                        });

                        // Handle withdrawal-specific fields
                        if (data.amount && data.currency) {
                            document.getElementById('amount').innerText = formatAmountWithCurrency(data.amount, data.currency);
                        }

                        // Handle wallet information
                        if (data.wallet) {
                            const walletInfo = `${data.wallet.name}<br><small>${data.wallet.iban}</small>`;
                            document.getElementById('wallet-info').innerHTML = walletInfo;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching withdrawal data:', error);
                    });
            });

            @if(count($childVendors) > 0)
            // Assign Vendor Modal Handler
            const assignVendorModal = document.getElementById('assign_vendor_modal');
            if (assignVendorModal) {
                assignVendorModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const withdrawalId = button.getAttribute('data-withdrawal-id');
                    const currentVendorId = button.getAttribute('data-current-vendor-id');
                    const currentVendorName = button.getAttribute('data-current-vendor-name');

                    // Set withdrawal ID
                    document.getElementById('assign_vendor_withdrawal_id').value = withdrawalId;

                    // Set current vendor name
                    document.getElementById('assign_vendor_current_vendor').value = currentVendorName || '-';

                    // Reset form - set vendor dropdown to empty and uncheck processing checkbox
                    document.getElementById('assign_vendor_id').value = '';
                    document.getElementById('assign_vendor_set_processing').checked = false;
                });

                // Form submission handler
                const assignVendorForm = document.getElementById('assign_vendor_form');
                if (assignVendorForm) {
                    assignVendorForm.addEventListener('submit', function(e) {
                        e.preventDefault();

                        const withdrawalId = document.getElementById('assign_vendor_withdrawal_id').value;
                        const vendorId = document.getElementById('assign_vendor_id').value;
                        const setProcessing = document.getElementById('assign_vendor_set_processing').checked;

                        if (!vendorId) {
                            alert('{{ __("Please select a sub-vendor") }}');
                            return;
                        }

                        // Disable submit button
                        const submitButton = assignVendorForm.querySelector('button[type="submit"]');
                        submitButton.disabled = true;
                        submitButton.textContent = '{{ __("Assigning...") }}';

                        fetch(`{{ url('vendor/withdrawals') }}/${withdrawalId}/assign-vendor`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                vendor_id: vendorId,
                                set_processing: setProcessing ? 1 : 0
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.message) {
                                alert(data.message);
                                location.reload();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('{{ __("An error occurred") }}');
                            submitButton.disabled = false;
                            submitButton.textContent = '{{ __("Assign") }}';
                        });
                    });
                }
            }
            @endif

            // View type change handler
            window.changeViewType = function(value) {
                document.getElementById('view_type_input').value = value;

                // If switching to "own", clear child vendor filter
                if (value === 'own') {
                    const childVendorSelect = document.getElementById('child_vendor_id');
                    if (childVendorSelect) {
                        childVendorSelect.value = '';
                    }
                }

                document.getElementById('searchForm').submit();
            };

            // Child vendor change handler
            window.handleChildVendorChange = function(value) {
                // If a child vendor is selected, automatically switch to child_vendors view
                if (value) {
                    document.getElementById('view_type_input').value = 'child_vendors';
                    // Update radio button
                    const childVendorsRadio = document.getElementById('view_type_child_vendors');
                    const ownRadio = document.getElementById('view_type_own');
                    if (childVendorsRadio) {
                        childVendorsRadio.checked = true;
                    }
                    if (ownRadio) {
                        ownRadio.checked = false;
                    }
                }
                // Submit form to apply filter
                document.getElementById('searchForm').submit();
            };
        });
    </script>
@endpush
