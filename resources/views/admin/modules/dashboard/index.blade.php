@extends('admin.layouts.app')
@section('title', $title)
@section('content')
    <div class="content">
        @can('vendor-reconciliations-index')
            @if(!auth()->user()->hasRole('Merchant') && \Illuminate\Support\Facades\Route::has('admin.vendor-reconciliations.index'))
                <div class="row mb-3">
                    <div class="col-12">
                        <a href="{{ route('admin.vendor-reconciliations.index') }}" class="card card-body text-decoration-none text-body d-flex align-items-center gap-3">
                            <i class="ph-scales ph-2x text-primary"></i>
                            <div>
                                <h6 class="mb-0">{{ __('Vendor Reconciliation') }}</h6>
                                <span class="text-muted fs-sm">{{ __('Daily Reconciliation') }}</span>
                            </div>
                            <i class="ph-arrow-right ms-auto"></i>
                        </a>
                    </div>
                </div>
            @endif
        @endcan
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h5 class="mb-0">{{ __('Incoming Transactions') }}</h5>
                        <span class="badge bg-success ms-auto">{{ $transactions?->count() ?? 0 }}</span>
                    </div>
                    <table id="transactions-table" class="table table-hover dataTable">
                        <thead>
                        <tr>
                            <th>{{ __('ID') }}</th>
                            <th>{{ __('Sender') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Date') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h5 class="mb-0">{{ __('Outgoing Transactions') }}</h5>
                        <span class="badge bg-danger ms-auto">{{ $withdrawals?->count() ?? 0 }}</span>
                    </div>
                    <table id="withdrawals-table" class="table table-hover">
                        <thead>
                        <tr>
                            <th>{{ __('ID') }}</th>
                            <th>{{ __('Receiver') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Date') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
        const DashboardDataTables = (function () {
            function _setup() {
                if (typeof $.fn.DataTable === 'undefined') {
                    console.warn('{{ __('Warning') }} - datatables.min.js is not loaded.');
                    return;
                }

                $.extend($.fn.dataTable.defaults, {
                    autoWidth: false,
                    dom: '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
                    language: {
                        search: '<span class="me-3">{{ __('Search') }}:</span> <div class="form-control-feedback form-control-feedback-end flex-fill">_INPUT_<div class="form-control-feedback-icon"><i class="ph-magnifying-glass opacity-50"></i></div></div>',
                        searchPlaceholder: '{{ __('Search in all fields...') }}',
                        lengthMenu: '<span class="me-3">{{ __('Show') }}:</span> _MENU_',
                        paginate: {
                            'first': '{{ __('First') }}',
                            'last': '{{ __('Last') }}',
                            'next': document.dir === "rtl" ? '&larr;' : '&rarr;',
                            'previous': document.dir === "rtl" ? '&rarr;' : '&larr;'
                        }
                    }
                });

                $('#transactions-table').DataTable({
                    processing: true,
                    serverSide: true,
                    order: [[4, 'desc']], // created_at column index
                    ajax: {
                        url: '/manage/dashboard/transactions-ajax',
                        dataSrc: 'data'
                    },
                    columns: [
                        {data: 'id', render: data => `<span class="fw-semibold">#${data}</span>`, orderable: false},
                        {
                            data: 'sender',
                            render: (data, type, row) => `<div class="d-flex align-items-center"><div class="flex-fill"><div class="fw-semibold">${data}</div></div></div>`,
                            orderable: false
                        },
                        {
                            data: 'amount',
                            render: data => `<span class="badge bg-success bg-opacity-10 text-success">${data}</span>`,
                            orderable: false
                        },
                        {data: 'status_html', orderable: false},
                        {
                            data: null,
                            render: (data, type, row) => `<div class="text-muted">${row.created_at_date}</div><span class="badge bg-light text-body">${row.created_at_time}</span>`,
                            orderable: false
                        }
                    ]
                });

                $('#withdrawals-table').DataTable({
                    processing: true,
                    serverSide: true,
                    order: [[4, 'desc']], // created_at column index
                    ajax: {
                        url: '/manage/dashboard/withdrawals-ajax',
                        dataSrc: 'data'
                    },
                    columns: [
                        {data: 'id', render: data => `<span class="fw-semibold">#${data}</span>`, orderable: false},
                        {
                            data: 'receiver',
                            render: (data, type, row) => `<div class="d-flex align-items-center"><div class="flex-fill"><div class="fw-semibold">${data}</div><small class="text-muted">${row.iban}</small></div></div>`,
                            orderable: false
                        },
                        {
                            data: 'amount',
                            render: data => `<span class="badge bg-success bg-opacity-10 text-success">${data}</span>`,
                            orderable: false
                        },
                        {data: 'status_html', orderable: false},
                        {
                            data: null,
                            render: (data, type, row) => `<div class="text-muted">${row.created_at_date}</div><span class="badge bg-light text-body">${row.created_at_time}</span>`,
                            orderable: false
                        }
                    ]
                });
            }

            return {
                init: function () {
                    _setup();
                }
            }
        })();

        document.addEventListener('DOMContentLoaded', function () {
            DashboardDataTables.init();
        });
    </script>
@endpush
