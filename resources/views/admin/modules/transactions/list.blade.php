@extends('admin.layouts.app')
@section('title', $title)
@section('content')
    <div class="content">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <h5 class="mb-0">{{ $module }}</h5>
                <div class="ms-auto d-flex gap-2">
                    <x-buttons.create title="{{ __('Create') }}" url="{{ route('admin.transactions.create') }}" permission="transactions-create"/>
                    <x-buttons.export title="{{ __('Export') }}" url="{!! route('admin.transactions.export', request()->query()) !!}" permission="transactions-export"/>
                </div>
            </div>

            <div class="card-body">
                <form action="" method="GET" id="searchForm">
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('General Search') }}</label>
                                <input type="text" name="search" class="form-control" placeholder="{{ __('Search in all fields...') }}" value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Sender') }}</label>
                                <input type="text" name="sender" class="form-control" placeholder="{{ __('Sender') }}" value="{{ request('sender') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Site User ID') }}</label>
                                <input type="number" name="user_id" class="form-control" placeholder="{{ __('Site User ID') }}" value="{{ request('user_id') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Order ID') }}</label>
                                <input type="number" name="order_id" class="form-control" placeholder="{{ __('Order ID') }}" value="{{ request('order_id') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Paypap ID') }}</label>
                                <input type="text" name="uuid" class="form-control" placeholder="{{ __('Paypap ID') }}" value="{{ request('uuid') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Receiver') }}</label>
                                <input type="text" name="receiver" class="form-control" placeholder="{{ __('Receiver') }}" value="{{ request('receiver') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Currency') }}</label>
                                <select name="currency" class="form-select">
                                    <option value="">{{ __('All Currencies') }}</option>
                                    @foreach($currencies as $currency)
                                        <option value="{{ $currency->name }}"
                                            @selected(request('currency') == $currency->name)>
                                            {{ $currency->name }}
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
                                    @foreach($transaction_statuses as $status)
                                        <option value="{{ $status->value }}"
                                            @selected((string) request('status') === (string) $status->value)>
                                            {{ __($status->label()) }}
                                        </option>
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
                                        <option value="{{ $status->value }}"
                                            @selected((string) request('paid_status') === (string) $status->value)>
                                            {{ __($status->label()) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Payment Method') }}</label>
                                <select name="payment_method" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach($payment_providers as $payment_provider)
                                        <option value="{{ $payment_provider->value }}"
                                            @selected((string) request('payment_method') === (string) $payment_provider->value)>
                                            {{ __($payment_provider->label()) }}
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
                                <label class="form-label">{{ __('Amount Range') }}</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="amount_min" class="form-control" placeholder="{{ __('Min') }}" value="{{ request('amount_min') }}">
                                    <span class="input-group-text">-</span>
                                    <input type="number" step="0.01" name="amount_max" class="form-control" placeholder="{{ __('Max') }}" value="{{ request('amount_max') }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Creation Date Range') }}</label>
                                <input type="text" id="creation_date_range" name="creation_date_range" class="form-control daterange-picker" placeholder="{{ __('Select date range') }}" value="{{ request('created_from') && request('created_to') ? request('created_from') . ' - ' . request('created_to') : '' }}">
                                <input type="hidden" name="created_from" value="{{ request('created_from') }}">
                                <input type="hidden" name="created_to" value="{{ request('created_to') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Update Date Range') }}</label>
                                <input type="text" id="update_date_range" name="update_date_range" class="form-control daterange-picker" placeholder="{{ __('Select date range') }}" value="{{ request('updated_from') && request('updated_to') ? request('updated_from') . ' - ' . request('updated_to') : '' }}">
                                <input type="hidden" name="updated_from" value="{{ request('updated_from') }}">
                                <input type="hidden" name="updated_to" value="{{ request('updated_to') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Accepted Date Range') }}</label>
                                <input type="text" id="accepted_date_range" name="accepted_date_range" class="form-control daterange-picker" placeholder="{{ __('Select date range') }}" value="{{ request('accepted_from') && request('accepted_to') ? request('accepted_from') . ' - ' . request('accepted_to') : '' }}">
                                <input type="hidden" name="accepted_from" value="{{ request('accepted_from') }}">
                                <input type="hidden" name="accepted_to" value="{{ request('accepted_to') }}">
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
                        <th></th>
                        {!! sortableTableHeader('order_id', 'Order ID', 'transactions') !!}
                        {!! sortableTableHeader('first_name', 'Sender', 'transactions') !!}
                        {!! sortableTableHeader('amount', 'Amount', 'transactions') !!}
                        {!! sortableTableHeader('fee_amount', 'Fee', 'transactions') !!}
                        {!! sortableTableHeader('status', 'Status', 'transactions') !!}
                        {!! sortableTableHeader('paid_status', 'Paid Status', 'transactions') !!}
                        {!! sortableTableHeader('created_at', 'Created At', 'transactions') !!}
                        {!! sortableTableHeader('accepted_at', 'Accepted At', 'transactions') !!}
                        <th>{{ __('Receiver') }}</th>
                        {!! sortableTableHeader('user_id', 'Site User ID', 'transactions') !!}
                        {!! sortableTableHeader('site_id', 'Site', 'transactions') !!}
                        {!! sortableTableHeader('payment_method', 'Payment Method', 'transactions') !!}
                        {!! sortableTableHeader('uuid', 'Paypap ID', 'transactions') !!}
                        <th>{{ __('Üst Bayi') }}</th>
                        <th>{{ __('Alt Bayi') }}</th>
                        <th class="text-center" style="width: 20px;">
                            <i class="ph-dots-three"></i>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>
                                @if($item->payment_method == 'manual' && $item->status->value == 1)
                                    <button class="btn btn-outline-success btn-sm approve-btn" data-id="{{ $item->id }}" data-type="transaction">{{ __('Approve') }}</button>
                                    <button class="btn btn-outline-danger btn-sm cancel-btn" data-id="{{ $item->id }}" data-type="transaction">{{ __('Cancel') }}</button>
                                @endif
                            </td>
                            <td>{{ $item->order_id }}</td>
                            <td>{{ $item->sender }}</td>
                            <td>
                                <span class="badge bg-success bg-opacity-10 text-success">{{ $item->currency->code() }} {{ number_format($item->amount, 2) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-success bg-opacity-10 text-success">{{ $item->currency->code() }} {{ number_format($item->fee_amount, 2) }}</span>
                            </td>
                            <td>{!! $item->status_html !!}</td>
                            <td><x-paid-status :paid="$item->paid_status" /></td>
                            <td>{{ $item->created_at->isoFormat('DD MMM YYYY HH:mm') }}</td>
                            <td>{{ $item->accepted_at?->isoFormat('DD MMM YYYY HH:mm') ?? '' }}</td>
                            <td>{!! $item->receiver !!}</td>
                            <td>{{ $item->user_id }}</td>
                            <td>{{ $item->site_name }}</td>
                            <td> {{ $item->payment_method?->label() ?? __('Unknown') }}</td>
                            <td>{{ $item->uuid }}</td>
                            <td>
                                @if($item->vendor && $item->vendor->parent)
                                    <span class="badge bg-info bg-opacity-10 text-info">{{ $item->vendor->parent->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item->vendor)
                                    <span class="badge bg-primary bg-opacity-10 text-primary">{{ $item->vendor->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @canany(['transactions-show', 'transactions-edit', 'transactions-delete'])
                                    <div class="dropdown">
                                        <a href="#" class="text-body" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                            <i class="ph-list"></i>
                                        </a>

                                        <div class="dropdown-menu dropdown-menu-end" data-popper-placement="top-start"
                                             data-popper-reference-hidden="">
                                            <div class="dropdown-header">{{ __('Options') }}</div>
                                            @can('transactions-show')
                                                <a href="#" class="dropdown-item"
                                                   data-url="{{ route('admin.transactions.show', $item->id) }}"
                                                   data-bs-toggle="modal" data-bs-target="#show_modal">
                                                    <i class="ph-eye me-2"></i>
                                                    {{ __('Show transaction') }}
                                                </a>
                                            @endcan
                                            @can('transactions-edit')
                                                <a href="{{ route('admin.transactions.edit', $item->id) }}"
                                                   class="dropdown-item">
                                                    <i class="ph-pen me-2"></i>
                                                    {{ __('Edit transaction') }}
                                                </a>
                                            @endcan
                                            @can('transactions-delete')
                                                <a href="#" class="dropdown-item text-danger"
                                                   data-delete-url="{{ route('admin.transactions.destroy', $item->id) }}"
                                                   data-item-name="transaction #{{ $item->id }}">
                                                    <i class="ph-trash me-2"></i>
                                                    {{ __('Delete transaction') }}
                                                </a>
                                            @endcan
                                            <div class="dropdown-divider"></div>
                                            <a href="{{ route('admin.transactions.activity-logs', $item->id) }}" class="dropdown-item">
                                                <i class="ph-list-dashes me-2"></i>
                                                {{ __('Activity Logs') }}
                                            </a>
                                            @if($item->payment_method?->value === 'paypap')
                                                <a href="{{ route('admin.transactions.paypap-status', $item->id) }}" class="dropdown-item">
                                                    <i class="ph-info me-2"></i>
                                                    {{ __('Paypap Status') }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15">{{ __('Data not found') }}</td>
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
                    <h5 class="modal-title">{{ __('Show transaction') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('ID') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="id">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Sender') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="sender">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Receiver') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="receiver">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Amount') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="amount">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Currency') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="currency">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Status') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="status_html">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Bank Name') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="bank-name">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Site Name') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="site-name">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Site ID') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="user-id">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Client Ip') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="client-ip">-</div>
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
        document.addEventListener('DOMContentLoaded', function () {

            // Approve button handler
            document.querySelectorAll('.approve-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const type = this.getAttribute('data-type');

                    if (confirm('{{ __("Are you sure you want to approve this transaction?") }}')) {
                        fetch(`{{ url('manage/transactions') }}/${id}/approve`, {
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

                    if (confirm('{{ __("Are you sure you want to cancel this transaction?") }}')) {
                        fetch(`{{ url('manage/transactions') }}/${id}/cancel`, {
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
