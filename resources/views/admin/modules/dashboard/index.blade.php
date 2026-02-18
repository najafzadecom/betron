@extends('admin.layouts.app')
@section('title', $title)
@section('content')
    <div class="content">
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h5 class="mb-0">{{ __('Incoming Transactions') }}</h5>
                        <span class="badge bg-success ms-auto">{{ $transactions->count() }}</span>
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
                        <span class="badge bg-danger ms-auto">{{ $withdrawals->count() }}</span>
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

        @if(auth()->user()->hasRole('Merchant'))
        <!-- Statistics -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card card-body">
                    <div class="row text-center">
                        <div class="col-3 mb-3">
                            <p><i class="ph-arrow-down bg-success bg-opacity-10 text-success lh-1 rounded-pill p-2"></i>
                            </p>
                            <h5 class="mb-0">{{ $transactions_count }}</h5>
                            <span class="text-muted fs-sm">{{ __('Incoming Transactions') }}</span>
                        </div>

                        <div class="col-3 mb-3">
                            <p><i class="ph-arrow-up bg-danger bg-opacity-10 text-danger lh-1 rounded-pill p-2"></i></p>
                            <h5 class="mb-0">{{ $withdrawals_count }}</h5>
                            <span class="text-muted fs-sm">{{ __('Outgoing Transactions') }}</span>
                        </div>

                        <div class="col-3 mb-3">
                            <p>
                                <i class="ph-currency-circle-dollar bg-primary bg-opacity-10 text-primary lh-1 rounded-pill p-2"></i>
                            </p>
                            <h5 class="mb-0">₺{{ number_format($siteStatistics->pay_in_total, 2) }}</h5>
                            <span class="text-muted fs-sm">{{ __('Incoming Amount') }}</span>
                        </div>

                        <div class="col-3 mb-3">
                            <p>
                                <i class="ph-currency-circle-dollar bg-primary bg-opacity-10 text-primary lh-1 rounded-pill p-2"></i>
                            </p>
                            <h5 class="mb-0">₺{{ number_format($siteStatistics->pay_in_fee_total, 2) }}</h5>
                            <span class="text-muted fs-sm">{{ __('Incoming Fee Amount') }}</span>
                        </div>

                        <div class="col-3 mb-3">
                            <p>
                                <i class="ph-currency-circle-dollar bg-primary bg-opacity-10 text-primary lh-1 rounded-pill p-2"></i>
                            </p>
                            <h5 class="mb-0">₺{{ number_format($siteStatistics->pay_in_grand_total, 2) }}</h5>
                            <span class="text-muted fs-sm">{{ __('Incoming Grand Total Amount') }}</span>
                        </div>

                        <div class="col-3 mb-3">
                            <p>
                                <i class="ph-currency-circle-dollar bg-warning bg-opacity-10 text-warning lh-1 rounded-pill p-2"></i>
                            </p>
                            <h5 class="mb-0">₺{{ number_format($siteStatistics->pay_out_total, 2) }}</h5>
                            <span class="text-muted fs-sm">{{ __('Outgoing Amount') }}</span>
                        </div>

                        <div class="col-3 mb-3">
                            <p>
                                <i class="ph-currency-circle-dollar bg-warning bg-opacity-10 text-warning lh-1 rounded-pill p-2"></i>
                            </p>
                            <h5 class="mb-0">₺{{ number_format($siteStatistics->pay_out_fee_total, 2) }}</h5>
                            <span class="text-muted fs-sm">{{ __('Outgoing Fee Amount') }}</span>
                        </div>

                        <div class="col-3 mb-3">
                            <p>
                                <i class="ph-currency-circle-dollar bg-warning bg-opacity-10 text-warning lh-1 rounded-pill p-2"></i>
                            </p>
                            <h5 class="mb-0">₺{{ number_format($siteStatistics->pay_out_grand_total, 2) }}</h5>
                            <span class="text-muted fs-sm">{{ __('Outgoing Grand Total Amount') }}</span>
                        </div>

                        <div class="col-3 mb-3">
                            <p>
                                <i class="ph-currency-circle-dollar bg-warning bg-opacity-10 text-warning lh-1 rounded-pill p-2"></i>
                            </p>
                            <h5 class="mb-0">₺{{ number_format($siteStatistics->total, 2) }}</h5>
                            <span class="text-muted fs-sm">{{ __('Total Amount') }}</span>
                        </div>

                        <div class="col-3 mb-3">
                            <p>
                                <i class="ph-percent bg-warning bg-opacity-10 text-warning lh-1 rounded-pill p-2"></i>
                            </p>
                            <h5 class="mb-0">{{ number_format($site->settlement_fee, 2) }} %</h5>
                            <span class="text-muted fs-sm">{{ __('Settlement Fee') }}</span>
                        </div>

                        <div class="col-3 mb-3">
                            <p>
                                <i class="ph-currency-circle-dollar bg-warning bg-opacity-10 text-warning lh-1 rounded-pill p-2"></i>
                            </p>
                            <h5 class="mb-0">₺{{ number_format(($siteStatistics->total*$site->settlement_fee/100), 2) }}</h5>
                            <span class="text-muted fs-sm">{{ __('Settlement Fee Amount') }}</span>
                        </div>

                        <div class="col-3 mb-3">
                            <p>
                                <i class="ph-currency-circle-dollar bg-warning bg-opacity-10 text-warning lh-1 rounded-pill p-2"></i>
                            </p>
                            <h5 class="mb-0">₺{{ number_format($siteStatistics->total+($siteStatistics->total*$site->settlement_fee/100), 2) }}</h5>
                            <span class="text-muted fs-sm">{{ __('Total with Settlement Fee Amount') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
        const DashboardDataTables = (function () {
            function _setup() {
                if (typeof $.fn.DataTable === 'undefined') {
                    console.warn('Xəbərdarlıq - datatables.min.js yüklənməyib.');
                    return;
                }

                $.extend($.fn.dataTable.defaults, {
                    autoWidth: false,
                    dom: '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
                    language: {
                        search: '<span class="me-3">Axtar:</span> <div class="form-control-feedback form-control-feedback-end flex-fill">_INPUT_<div class="form-control-feedback-icon"><i class="ph-magnifying-glass opacity-50"></i></div></div>',
                        searchPlaceholder: 'Filter üçün yazın...',
                        lengthMenu: '<span class="me-3">Göstər:</span> _MENU_',
                        paginate: {
                            'first': 'İlk',
                            'last': 'Son',
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
