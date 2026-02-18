@extends('admin.layouts.app')
@section('title', $title)
@section('content')
    <div class="content">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <h5 class="mb-0">{{ $module }}</h5>
                <div class="ms-auto d-flex gap-2">
                    @can('vendors-index')
                        <a href="{{ route('admin.vendors.deposit-transactions.all') }}" class="btn btn-success">
                            <i class="ph-list-dashes me-1"></i> {{ __('All Deposit Transactions') }}
                        </a>
                    @endcan
                    <x-buttons.create title="{{ __('Create') }}" url="{{ route('admin.vendors.create') }}" permission="vendors-create"/>
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
                                <label class="form-label">{{ __('Name') }}</label>
                                <input type="text" name="name" class="form-control" placeholder="{{ __('Name') }}" value="{{ request('name') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Email') }}</label>
                                <input type="text" name="email" class="form-control" placeholder="{{ __('Email') }}" value="{{ request('email') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select name="status" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="1"{{ request('status') == '1' ? ' selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="0"{{ request('status') == '0' ? ' selected' : '' }}>{{ __('Inactive') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Parent Vendor') }}</label>
                                <select name="parent_id" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @if(isset($topLevelVendors) && $topLevelVendors)
                                        @foreach($topLevelVendors as $vendor)
                                            <option value="{{ $vendor->id }}"{{ request('parent_id') == $vendor->id ? ' selected' : '' }}>
                                                {{ $vendor->name }}
                                            </option>
                                        @endforeach
                                    @endif
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
                <table class="table table-xs text-nowrap">
                    <thead>
                    <tr>
                        {!! sortableTableHeader('id', 'ID', 'vendors') !!}
                        {!! sortableTableHeader('name', 'Name', 'vendors') !!}
                        {!! sortableTableHeader('email', 'Email', 'vendors') !!}
                        <th>{{ __('Wallets') }}</th>
                        {!! sortableTableHeader('status', 'Status', 'vendors') !!}
                        <th>
                            <div>{{ __('Deposit Enabled') }}</div>
                            <div class="btn-group mt-1" role="group">
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="toggleAllVendorStatuses('deposit_enabled', 1)">
                                    {{ __('Hepsini Aç') }}
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="toggleAllVendorStatuses('deposit_enabled', 0)">
                                    {{ __('Hepsini Kapat') }}
                                </button>
                            </div>
                        </th>
                        <th>
                            <div>{{ __('Withdrawal Enabled') }}</div>
                            <div class="btn-group mt-1" role="group">
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="toggleAllVendorStatuses('withdrawal_enabled', 1)">
                                    {{ __('Hepsini Aç') }}
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="toggleAllVendorStatuses('withdrawal_enabled', 0)">
                                    {{ __('Hepsini Kapat') }}
                                </button>
                            </div>
                        </th>
                        {!! sortableTableHeader('deposit_amount', 'Deposit Amount', 'vendors') !!}
                        {!! sortableTableHeader('transaction_fee', 'Transaction Fee', 'vendors') !!}
                        {!! sortableTableHeader('withdrawal_fee', 'Withdrawal Fee', 'vendors') !!}
                        {!! sortableTableHeader('settlement_fee', 'Settlement Fee', 'vendors') !!}
                        <th class="text-center" style="width: 20px;">
                            <i class="ph-dots-three"></i>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $item)
                        <tr data-id="{{ $item->id }}" class="vendor-row">
                            <td>{{ $item->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span>{{ $item->name }}</span>
                                    @can('vendors-edit')
                                        <form action="{{ route('admin.vendors.login-as', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="{{ __('Login as Vendor') }}" onclick="return confirm('{{ __('Are you sure you want to login as this vendor?') }}')">
                                                <i class="ph-sign-in"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                            <td>{{ $item->email }}</td>
                            <td>
                                <span class="badge bg-info">{{ $item->wallets_count ?? $item->wallets()->count() }}</span>
                            </td>
                            <td>
                                @if($item->status)
                                    <span class="badge bg-success bg-opacity-10 text-success">{{ __('Active') }}</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger">{{ __('Inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($item->deposit_enabled ?? true)
                                    <span class="badge bg-success bg-opacity-10 text-success">{{ __('Enabled') }}</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger">{{ __('Disabled') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($item->withdrawal_enabled ?? true)
                                    <span class="badge bg-success bg-opacity-10 text-success">{{ __('Enabled') }}</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger">{{ __('Disabled') }}</span>
                                @endif
                            </td>
                            <td><span class="badge bg-info bg-opacity-10 text-info">{{ number_format($item->deposit_amount ?? 0, 2) }} ₺</span></td>
                            <td>{{ $item->transaction_fee }} %</td>
                            <td>{{ $item->withdrawal_fee }} %</td>
                            <td>{{ $item->settlement_fee }} %</td>
                            <td>
                                @canany(['vendors-show', 'vendors-edit', 'vendors-delete'])
                                    <div class="dropdown">
                                        <a href="#" class="text-body" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                            <i class="ph-list"></i>
                                        </a>

                                        <div class="dropdown-menu dropdown-menu-end" data-popper-placement="top-start">
                                            <div class="dropdown-header">{{ __('Options') }}</div>
                                            @can('vendors-show')
                                                <a href="#" class="dropdown-item"
                                                   data-url="{{ route('admin.vendors.show', $item->id) }}"
                                                   data-bs-toggle="modal" data-bs-target="#show_modal">
                                                    <i class="ph-eye me-2"></i>
                                                    {{ __('Show vendor') }}
                                                </a>
                                            @endcan
                                            @can('vendors-edit')
                                                <a href="{{ route('admin.vendors.edit', $item->id) }}" class="dropdown-item">
                                                    <i class="ph-pen me-2"></i>
                                                    {{ __('Edit vendor') }}
                                                </a>
                                                <form action="{{ route('admin.vendors.login-as', $item->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-primary" onclick="return confirm('{{ __('Are you sure you want to login as this vendor?') }}')">
                                                        <i class="ph-sign-in me-2"></i>
                                                        {{ __('Login as Vendor') }}
                                                    </button>
                                                </form>
                                                <div class="dropdown-divider"></div>
                                                <a href="#" class="dropdown-item text-success"
                                                   data-bs-toggle="modal" data-bs-target="#add_deposit_modal"
                                                   data-vendor-id="{{ $item->id }}"
                                                   data-vendor-name="{{ $item->name }}"
                                                   data-current-deposit="{{ $item->deposit_amount }}">
                                                    <i class="ph-plus-circle me-2"></i>
                                                    {{ __('Add Deposit') }}
                                                </a>
                                                <a href="#" class="dropdown-item text-warning"
                                                   data-bs-toggle="modal" data-bs-target="#subtract_deposit_modal"
                                                   data-vendor-id="{{ $item->id }}"
                                                   data-vendor-name="{{ $item->name }}"
                                                   data-current-deposit="{{ $item->deposit_amount }}">
                                                    <i class="ph-minus-circle me-2"></i>
                                                    {{ __('Subtract Deposit') }}
                                                </a>
                                                <a href="{{ route('admin.vendors.deposit-transactions', $item->id) }}" class="dropdown-item">
                                                    <i class="ph-list-dashes me-2"></i>
                                                    {{ __('Deposit Transactions') }}
                                                </a>
                                            @endcan
                                            @can('vendors-delete')
                                                <div class="dropdown-divider"></div>
                                                <a href="#" class="dropdown-item text-danger" data-delete-url="{{ route('admin.vendors.destroy', $item->id) }}" data-item-name="{{ $item->name }}">
                                                    <i class="ph-trash me-2"></i>
                                                    {{ __('Delete vendor') }}
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                @endcanany
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12">{{ __('Data not found') }}</td>
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

    <div id="show_modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Show vendor') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('ID') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="id">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Name') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="name">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Email') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="email">-</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Deposit Amount') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="deposit_amount">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Status') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="status">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Deposit Enabled') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="deposit_enabled">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Withdrawal Enabled') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="withdrawal_enabled">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Created At') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="created-at">-</div>
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

    <!-- Add Deposit Modal -->
    <div id="add_deposit_modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add_deposit_form" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Add Deposit') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Vendor') }}:</label>
                            <input type="text" class="form-control" id="add_deposit_vendor_name" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Current Deposit') }}:</label>
                            <input type="text" class="form-control" id="add_deposit_current" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Amount') }} <span class="text-danger">*</span>:</label>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="{{ __('Enter amount') }}" required>
                            @error('amount')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Note') }}:</label>
                            <textarea name="note" class="form-control @error('note') is-invalid @enderror" rows="3" placeholder="{{ __('Optional note') }}" maxlength="500"></textarea>
                            @error('note')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-link" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-success">
                            <i class="ph-plus-circle me-1"></i> {{ __('Add Deposit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Subtract Deposit Modal -->
    <div id="subtract_deposit_modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="subtract_deposit_form" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Subtract Deposit') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Vendor') }}:</label>
                            <input type="text" class="form-control" id="subtract_deposit_vendor_name" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Current Deposit') }}:</label>
                            <input type="text" class="form-control" id="subtract_deposit_current" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Amount') }} <span class="text-danger">*</span>:</label>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="{{ __('Enter amount') }}" required>
                            @error('amount')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Note') }}:</label>
                            <textarea name="note" class="form-control @error('note') is-invalid @enderror" rows="3" placeholder="{{ __('Optional note') }}" maxlength="500"></textarea>
                            @error('note')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-link" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="ph-minus-circle me-1"></i> {{ __('Subtract Deposit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const showModal = document.getElementById('show_modal');
            if (showModal) {
                showModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const url = button.getAttribute('data-url');

                    fetch(url)
                        .then(response => response.json())
                        .then(responseData => {
                            const data = responseData.item;

                            document.getElementById('id').innerText = data.id ?? '-';
                            document.getElementById('name').innerText = data.name ?? '-';
                            document.getElementById('email').innerText = data.email ?? '-';

                            document.getElementById('deposit_amount').innerText = (data.deposit_amount ?? 0) + ' ₺';

                            if (data.status) {
                                document.getElementById('status').innerHTML = '<span class="badge bg-success">{{ __("Active") }}</span>';
                            } else {
                                document.getElementById('status').innerHTML = '<span class="badge bg-danger">{{ __("Inactive") }}</span>';
                            }

                            if (data.deposit_enabled) {
                                document.getElementById('deposit_enabled').innerHTML = '<span class="badge bg-success">{{ __("Enabled") }}</span>';
                            } else {
                                document.getElementById('deposit_enabled').innerHTML = '<span class="badge bg-danger">{{ __("Disabled") }}</span>';
                            }

                            if (data.withdrawal_enabled) {
                                document.getElementById('withdrawal_enabled').innerHTML = '<span class="badge bg-success">{{ __("Enabled") }}</span>';
                            } else {
                                document.getElementById('withdrawal_enabled').innerHTML = '<span class="badge bg-danger">{{ __("Disabled") }}</span>';
                            }

                            if (data.created_at) {
                                document.getElementById('created-at').innerText = new Date(data.created_at).toLocaleString();
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching vendor data:', error);
                        });
                });
            }

            // Add Deposit Modal
            const addDepositModal = document.getElementById('add_deposit_modal');
            if (addDepositModal) {
                addDepositModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const vendorId = button.getAttribute('data-vendor-id');
                    const vendorName = button.getAttribute('data-vendor-name');
                    const currentDeposit = button.getAttribute('data-current-deposit');

                    document.getElementById('add_deposit_vendor_name').value = vendorName;
                    document.getElementById('add_deposit_current').value = parseFloat(currentDeposit || 0).toFixed(2) + ' ₺';
                    document.getElementById('add_deposit_form').action = '{{ route('admin.vendors.add-deposit', ':id') }}'.replace(':id', vendorId);
                    document.getElementById('add_deposit_form').querySelector('input[name="amount"]').value = '';
                    document.getElementById('add_deposit_form').querySelector('textarea[name="note"]').value = '';
                });
            }

            // Subtract Deposit Modal
            const subtractDepositModal = document.getElementById('subtract_deposit_modal');
            if (subtractDepositModal) {
                subtractDepositModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const vendorId = button.getAttribute('data-vendor-id');
                    const vendorName = button.getAttribute('data-vendor-name');
                    const currentDeposit = button.getAttribute('data-current-deposit');

                    document.getElementById('subtract_deposit_vendor_name').value = vendorName;
                    document.getElementById('subtract_deposit_current').value = parseFloat(currentDeposit || 0).toFixed(2) + ' ₺';
                    document.getElementById('subtract_deposit_form').action = '{{ route('admin.vendors.subtract-deposit', ':id') }}'.replace(':id', vendorId);
                    document.getElementById('subtract_deposit_form').querySelector('input[name="amount"]').value = '';
                    document.getElementById('subtract_deposit_form').querySelector('textarea[name="note"]').value = '';
                });
            }
        });

        function clearFilters() {
            window.location.href = '{{ route('admin.vendors.index') }}';
        }

        // Toggle all vendor statuses functionality
        function toggleAllVendorStatuses(field, value) {
            const vendorIds = [];
            document.querySelectorAll('tbody .vendor-row').forEach(row => {
                vendorIds.push(row.getAttribute('data-id'));
            });

            if (vendorIds.length === 0) {
                alert('{{ __('No vendors found to update') }}');
                return;
            }

            // Show confirmation
            const fieldNames = {
                'deposit_enabled': '{{ __('Deposit Enabled') }}',
                'withdrawal_enabled': '{{ __('Withdrawal Enabled') }}'
            };

            const statusText = value === 1 ? '{{ __('Active') }}' : '{{ __('Inactive') }}';
            const confirmMessage = `${vendorIds.length} {{ __('Vendors') }} ${fieldNames[field]} ${statusText} {{ __('olarak güncellenecek. Emin misiniz?') }}`;

            if (!confirm(confirmMessage)) {
                return;
            }

            // Send AJAX request
            fetch('{{ route('admin.vendors.bulk-update-status') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    vendor_ids: vendorIds,
                    field: field,
                    value: value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data == 200 || (data.success && data.success === true)) {
                    // Reload page after request completes
                    window.location.reload();
                } else {
                    alert(data.message || '{{ __('Error updating vendors') }}');
                }
            })
            .catch(error => {
                console.error('Error updating vendor statuses:', error);
                alert('{{ __('Error updating vendors') }}');
            });
        }
    </script>
@endpush
