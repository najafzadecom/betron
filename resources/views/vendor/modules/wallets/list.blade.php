@extends('vendor.layouts.app')
@section('title', $title)
@section('content')
    <div class="content">
        <div class="card">
            <div class="card-header d-flex">
                <h5 class="mb-0">{{ $module }}</h5>
                <div class="ms-auto">
                    <a href="{{ route('vendor.wallets.create') }}" class="btn btn-primary btn-sm">
                        <i class="ph-plus me-1"></i> {{ __('Create') }}
                    </a>
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
                                <label class="form-label">{{ __('Bank') }}</label>
                                <select name="bank_id" class="form-select">
                                    <option value="">{{ __('All Banks') }}</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}"{{ request('bank_id') == $bank->id ? ' selected' : '' }}>
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

                        <div class="col-12 col-md-6 col-lg-4 d-flex align-items-end">
                            <div class="mb-3 w-100">
                                <div class="d-flex gap-2 flex-column flex-md-row">
                                    <button type="submit" class="btn btn-primary w-100 w-md-auto">
                                        <i class="ph-magnifying-glass me-1"></i> {{ __('Search') }}
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary w-100 w-md-auto" onclick="clearFilters()">
                                        <i class="ph-x me-1"></i> {{ __('Clear Filters') }}
                                    </button>
                                    <div class="dropdown w-100 w-md-auto">
                                        <button class="btn btn-warning w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            {{ __('Bulk Status') }}
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="bulkUpdateStatus('active')">{{ __('Make All Active') }}</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="bulkUpdateStatus('inactive')">{{ __('Make All Inactive') }}</a></li>
                                        </ul>
                                    </div>
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
                        {!! sortableTableHeader('id', 'ID', 'wallets') !!}
                        @if($isParentVendor ?? false)
                            {!! sortableTableHeader('vendor_id', 'Vendor', 'wallets') !!}
                        @endif
                        {!! sortableTableHeader('name', 'Account Name', 'wallets') !!}
                        {!! sortableTableHeader('iban', 'IBAN', 'wallets') !!}
                        <th>{{ __('Bank') }}</th>
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
                            @if($isParentVendor ?? false)
                                <td>{{ $item->vendor?->name }}</td>
                            @endif
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->iban }}</td>
                            <td>{{ $item->bank?->name }}</td>
                            <td>
                                <x-badge :title="$item->status->label()" :color="$item->status->color()"/>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" class="text-body" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                        <i class="ph-list"></i>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end">
                                        <div class="dropdown-header">{{ __('Options') }}</div>
                                        <a href="#" class="dropdown-item"
                                           data-url="{{ route('vendor.wallets.show', $item->id) }}"
                                           data-bs-toggle="modal" data-bs-target="#show_modal">
                                            <i class="ph-eye me-2"></i>
                                            {{ __('Show wallet') }}
                                        </a>
                                        <a href="{{ route('vendor.wallets.edit', $item->id) }}"
                                           class="dropdown-item">
                                            <i class="ph-pen me-2"></i>
                                            {{ __('Edit wallet') }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($isParentVendor ?? false) ? 9 : 8 }}">{{ __('Data not found') }}</td>
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
                    <h5 class="modal-title">{{ __('Show wallet') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('ID') }}:</div>
                        <div class="col-7 text-end" id="id">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Account Name') }}:</div>
                        <div class="col-7 text-end" id="name">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('IBAN') }}:</div>
                        <div class="col-7 text-end" id="iban">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Currency') }}:</div>
                        <div class="col-7 text-end" id="currency">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Status') }}:</div>
                        <div class="col-7 text-end" id="status">-</div>
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
        function bulkUpdateStatus(action) {
            if (!confirm('{{ __("Are you sure?") }}')) return;

            const searchForm = document.getElementById('searchForm');
            const formData = new FormData(searchForm);
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("vendor.wallets.bulk-update-status") }}';
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = action;
            form.appendChild(actionInput);

            for (const [key, value] of formData) {
                if (value) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                }
            }

            document.body.appendChild(form);
            form.submit();
        }

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
                        document.getElementById('iban').innerText = data.iban ?? '-';
                        // document.getElementById('total-amount').innerText = data.total_amount ?? '-';
                        // document.getElementById('blocked-amount').innerText = data.blocked_amount ?? '-';
                        document.getElementById('currency').innerText = data.currency ?? '-';
                    })
                    .catch(error => {
                        console.error('Error fetching wallet data:', error);
                    });
            });
        });
    </script>
@endpush
