@extends('vendor.layouts.app')
@section('title', $title)
@section('content')
    <div class="content">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <h5 class="mb-0">{{ $module }}</h5>
                <div class="ms-auto d-flex gap-2">
                    <a href="{{ route('vendor.vendors.deposit-transactions.all') }}" class="btn btn-info btn-sm">
                        <i class="ph-list-dashes me-1"></i> {{ __('All Deposit Transactions') }}
                    </a>
                    @if(isset($canCreateVendor) && $canCreateVendor)
                        <a href="{{ route('vendor.vendors.create') }}" class="btn btn-primary btn-sm">
                            <i class="ph-plus me-1"></i> {{ __('Create') }}
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <form action="" method="GET" id="searchForm">
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('General Search') }}</label>
                                <input type="text" name="search" class="form-control"
                                       placeholder="{{ __('Search...') }}"
                                       value="{{ request('search') }}">
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
                                <label class="form-label">{{ __('E-mail') }}</label>
                                <input type="text" name="email" class="form-control" placeholder="{{ __('E-mail') }}" value="{{ request('email') }}">
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
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('E-mail') }}</th>
                        <th>{{ __('Wallets') }}</th>
                        <th>{{ __('Status') }}</th>
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
                        <th>{{ __('Deposit Amount') }}</th>
                        <th>{{ __('Transaction Fee') }}</th>
                        <th>{{ __('Withdrawal Fee') }}</th>
                        <th>{{ __('Settlement Fee') }}</th>
                        <th>{{ __('Created At') }}</th>
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
                                    <form action="{{ route('vendor.vendors.login-as', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="{{ __('Login as Vendor') }}" onclick="return confirm('{{ __('Are you sure you want to login as this vendor?') }}')">
                                            <i class="ph-sign-in"></i>
                                        </button>
                                    </form>
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
                            <td>{{ $item->deposit_amount }} ₺</td>
                            <td>{{ $item->transaction_fee }} %</td>
                            <td>{{ $item->withdrawal_fee }} %</td>
                            <td>{{ $item->settlement_fee }} %</td>
                            <td>{{ $item->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" class="text-body" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                        <i class="ph-list"></i>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end">
                                        <div class="dropdown-header">{{ __('Options') }}</div>
                                        <a href="#" class="dropdown-item"
                                           data-url="{{ route('vendor.vendors.show', $item->id) }}"
                                           data-bs-toggle="modal" data-bs-target="#show_modal">
                                            <i class="ph-eye me-2"></i>
                                            {{ __('View') }}
                                        </a>
                                        <a href="{{ route('vendor.vendors.edit', $item->id) }}" class="dropdown-item">
                                            <i class="ph-pen me-2"></i>
                                            {{ __('Edit') }}
                                        </a>
                                        <form action="{{ route('vendor.vendors.login-as', $item->id) }}" method="POST" class="d-inline">
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
                                        <a href="{{ route('vendor.vendors.deposit-transactions', $item->id) }}" class="dropdown-item">
                                            <i class="ph-list-dashes me-2"></i>
                                            {{ __('Deposit Transactions') }}
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a href="#" class="dropdown-item text-danger"
                                           data-delete-url="{{ route('vendor.vendors.destroy', $item->id) }}"
                                           data-item-name="{{ $item->name }}">
                                            <i class="ph-trash me-2"></i>
                                            {{ __('Delete') }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="text-center">{{ __('Data not found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer">
            <div class="row mb-3 align-items-center">
                <div class="col-md-6 col-sm-12 mb-2 mb-md-0 d-flex align-items-center">
                    <label for="limit" class="me-2 mb-0">{{ __('Display') }}:</label>
                    <select id="limit" name="limit" class="form-select w-auto" onchange="changeLimit(this.value)">
                        @foreach(config('pagination.per_pages') as $limit)
                            <option value="{{ $limit }}"{{ request('limit', 25) == $limit ? ' selected' : '' }}>{{ $limit }}</option>
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

    <div id="show_modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Vendor Details') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('ID') }}:</div>
                        <div class="col-7 text-end" id="id">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Name') }}:</div>
                        <div class="col-7 text-end" id="name">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('E-mail') }}:</div>
                        <div class="col-7 text-end" id="email">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Deposit Amount') }}:</div>
                        <div class="col-7 text-end" id="deposit_amount">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Transaction Fee') }}:</div>
                        <div class="col-7 text-end" id="transaction_fee">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Withdrawal Fee') }}:</div>
                        <div class="col-7 text-end" id="withdrawal_fee">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Settlement Fee') }}:</div>
                        <div class="col-7 text-end" id="settlement_fee">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Status') }}:</div>
                        <div class="col-7 text-end" id="status">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Deposit Enabled') }}:</div>
                        <div class="col-7 text-end" id="deposit_enabled">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Withdrawal Enabled') }}:</div>
                        <div class="col-7 text-end" id="withdrawal_enabled">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Created At') }}:</div>
                        <div class="col-7 text-end" id="created-at">-</div>
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
            if (!showModal) return;

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
                        document.getElementById('transaction_fee').innerText = (data.transaction_fee ?? 0) + ' %';
                        document.getElementById('withdrawal_fee').innerText = (data.withdrawal_fee ?? 0) + ' %';
                        document.getElementById('settlement_fee').innerText = (data.settlement_fee ?? 0) + ' %';

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
        });

        function clearFilters() {
            window.location.href = '{{ route('vendor.vendors.index') }}';
        }

        function changeLimit(limit) {
            const url = new URL(window.location.href);
            url.searchParams.set('limit', limit);
            window.location.href = url.toString();
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
                document.getElementById('add_deposit_form').action = '{{ route('vendor.vendors.add-deposit', ':id') }}'.replace(':id', vendorId);
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
                document.getElementById('subtract_deposit_form').action = '{{ route('vendor.vendors.subtract-deposit', ':id') }}'.replace(':id', vendorId);
                document.getElementById('subtract_deposit_form').querySelector('input[name="amount"]').value = '';
                document.getElementById('subtract_deposit_form').querySelector('textarea[name="note"]').value = '';
            });
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
            fetch('{{ route('vendor.vendors.bulk-update-status') }}', {
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

