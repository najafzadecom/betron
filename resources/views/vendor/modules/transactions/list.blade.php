@extends('vendor.layouts.app')
@section('title', $title)
@section('content')
    <div class="content">
        <div class="card">
            <div class="card-header d-flex">
                <h5 class="mb-0">{{ $module }}</h5>
                <div class="ms-auto">
                    <a href="{{ route('vendor.transactions.export', request()->query()) }}" class="btn btn-success btn-sm">
                        <i class="ph-file-xls me-1"></i> {{ __('Export') }}
                    </a>
                </div>
            </div>

            <div class="card-body">
                <form action="" method="GET" id="searchForm">
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('General Search') }}</label>
                                <input type="text" name="search" class="form-control" placeholder="{{ __('Search...') }}" value="{{ request('search') }}">
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

                        @if($isParentVendor ?? false)
                            <div class="col-12 col-md-6 col-lg-2">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Vendor') }}</label>
                                    <select name="vendor_id" class="form-select">
                                        <option value="">{{ __('All Vendors') }}</option>
                                        @foreach($accessibleVendors ?? [] as $accessibleVendor)
                                            <option value="{{ $accessibleVendor->id }}"{{ request('vendor_id') == $accessibleVendor->id ? ' selected' : '' }}>
                                                {{ $accessibleVendor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Wallet') }}</label>
                                <select name="wallet_id" class="form-select">
                                    <option value="">{{ __('All Wallets') }}</option>
                                    @foreach($wallets as $wallet)
                                        <option value="{{ $wallet->id }}"{{ request('wallet_id') == $wallet->id ? ' selected' : '' }}>
                                            {{ $wallet->name }}
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
                                    @foreach(\App\Enums\TransactionStatus::cases() as $status)
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
                        {!! sortableTableHeader('order_id', 'Order ID', 'transactions') !!}
                        {!! sortableTableHeader('first_name', 'Sender', 'transactions') !!}
                        @if($isParentVendor ?? false)
                            <th>{{ __('Vendor') }}</th>
                        @endif
                        {!! sortableTableHeader('amount', 'Amount', 'transactions') !!}
                        {!! sortableTableHeader('status', 'Status', 'transactions') !!}
                        <th>{{ __('Paid Status') }}</th>
                        <th>{{ __('Wallet') }}</th>
                        {!! sortableTableHeader('created_at', 'Created At', 'transactions') !!}
                        {!! sortableTableHeader('accepted_at', 'Accepted At', 'transactions') !!}
                        <th></th>
                        <th class="text-center" style="width: 20px;">
                            <i class="ph-dots-three"></i>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $item->order_id }}</td>
                            <td>{{ $item->first_name }} {{ $item->last_name }}</td>
                            @if($isParentVendor ?? false)
                                <td>{{ $item->wallet?->vendor?->name ?? '-' }}</td>
                            @endif
                            <td>
                                <span class="badge bg-success bg-opacity-10 text-success">{{ $item->currency->code() }} {{ number_format($item->amount, 2) }}</span>
                            </td>
                            <td>{!! $item->status_html !!}</td>
                            <td><x-paid-status :paid="$item->paid_status" /></td>
                            <td>{!! $item->receiver  !!}</td>
                            <td>{{ $item->created_at->isoFormat('DD MMM YYYY HH:mm') }}</td>
                            <td>{{ $item->accepted_at?->isoFormat('DD MMM YYYY HH:mm') ?? '' }}</td>
                            <td>
                                @if($item->payment_method->value == 'manual' && $item->status->value == 1)
                                    <button class="btn btn-outline-success btn-sm approve-btn" data-id="{{ $item->id }}" data-type="transaction" data-amount="{{ $item->amount }}" data-currency="{{ $item->currency->code() }}">{{ __('Approve') }}</button>
                                    <button class="btn btn-outline-danger btn-sm cancel-btn" data-id="{{ $item->id }}" data-type="transaction">{{ __('Cancel') }}</button>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" class="text-body" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                        <i class="ph-list"></i>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end">
                                        <div class="dropdown-header">{{ __('Options') }}</div>
                                        <a href="#" class="dropdown-item"
                                           data-url="{{ route('vendor.transactions.show', $item->id) }}"
                                           data-bs-toggle="modal" data-bs-target="#show_modal">
                                            <i class="ph-eye me-2"></i>
                                            {{ __('Show transaction') }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($isParentVendor ?? false) ? 11 : 10 }}">{{ __('Data not found') }}</td>
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
                        <div class="col-5 fw-semibold text-muted">{{ __('ID') }}:</div>
                        <div class="col-7 text-end" id="id">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Order ID') }}:</div>
                        <div class="col-7 text-end" id="order-id">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Sender') }}:</div>
                        <div class="col-7 text-end" id="sender">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Phone') }}:</div>
                        <div class="col-7 text-end" id="phone">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Amount') }}:</div>
                        <div class="col-7 text-end" id="amount">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Fee') }}:</div>
                        <div class="col-7 text-end" id="fee-amount">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Currency') }}:</div>
                        <div class="col-7 text-end" id="currency">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Status') }}:</div>
                        <div class="col-7 text-end" id="status_html">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Client IP') }}:</div>
                        <div class="col-7 text-end" id="client-ip">-</div>
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
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Approve button handler
            document.querySelectorAll('.approve-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');

                    // Initialize SweetAlert2
                    const swalInit = Swal.mixin({
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-success',
                            cancelButton: 'btn btn-light',
                            input: 'form-control'
                        }
                    });

                    swalInit.fire({
                        title: '{{ __("Approve Transaction") }}',
                        text: '{{ __("Are you sure you want to approve this transaction?") }}',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: '{{ __("Approve") }}',
                        cancelButtonText: '{{ __("Cancel") }}'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show loading
                            swalInit.fire({
                                title: '{{ __("Processing...") }}',
                                text: '{{ __("Please wait") }}',
                                icon: 'info',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                showConfirmButton: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            fetch(`{{ url('vendor/transactions') }}/${id}/approve`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.message) {
                                    swalInit.fire({
                                        title: '{{ __("Success") }}',
                                        text: data.message,
                                        icon: 'success',
                                        confirmButtonText: '{{ __("OK") }}'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else if (data.error) {
                                    swalInit.fire({
                                        title: '{{ __("Error") }}',
                                        text: data.error || data.message || '{{ __("An error occurred") }}',
                                        icon: 'error',
                                        confirmButtonText: '{{ __("OK") }}'
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                swalInit.fire({
                                    title: '{{ __("Error") }}',
                                    text: '{{ __("An error occurred") }}',
                                    icon: 'error',
                                    confirmButtonText: '{{ __("OK") }}'
                                });
                            });
                        }
                    });
                });
            });

            // Cancel button handler
            document.querySelectorAll('.cancel-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const type = this.getAttribute('data-type');

                    if (confirm('{{ __("Are you sure you want to cancel this transaction?") }}')) {
                        fetch(`{{ url('vendor/transactions') }}/${id}/cancel`, {
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
                        document.getElementById('order-id').innerText = data.order_id ?? '-';
                        document.getElementById('sender').innerText = (data.first_name ?? '') + ' ' + (data.last_name ?? '');
                        document.getElementById('phone').innerText = data.phone ?? '-';
                        document.getElementById('amount').innerText = data.amount ?? '-';
                        document.getElementById('fee-amount').innerText = data.fee_amount ?? '-';
                        document.getElementById('currency').innerText = data.currency ?? '-';
                        document.getElementById('client-ip').innerText = data.client_ip ?? '-';

                        if (data.status_html) {
                            document.getElementById('status_html').innerHTML = data.status_html;
                        }

                        if (data.created_at) {
                            document.getElementById('created-at').innerText = new Date(data.created_at).toLocaleString();
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching transaction data:', error);
                    });
            });
        });
    </script>
@endpush
