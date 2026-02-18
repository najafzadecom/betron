@extends('admin.layouts.app')
@section('title', $title)
@section('content')
    <div class="content">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <h5 class="mb-0">{{ $module }}</h5>
                <div class="ms-auto d-flex gap-2">
                    <x-buttons.create title="{{ __('Create') }}" url="{{ route('admin.banks.create') }}" permission="banks-create"/>
                </div>
            </div>

            <div class="card-body">
                <form action="" method="GET" id="searchForm">
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Bank Name') }}</label>
                                <input type="text" name="name" class="form-control" placeholder="{{ __('Bank Name') }}" value="{{ request('name') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select name="status" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Transaction Status') }}</label>
                                <select name="transaction_status" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="1" {{ request('transaction_status') == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="0" {{ request('transaction_status') == '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Withdrawal Status') }}</label>
                                <select name="withdrawal_status" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="1" {{ request('withdrawal_status') == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="0" {{ request('withdrawal_status') == '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                </select>
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
                <table class="table text-nowrap" id="banks-table">
                    <thead>
                    <tr>
                        <th style="width: 30px;"><i class="ph-dots-six-vertical"></i></th>
                        <th>{{ __('Bank Name') }}</th>
                        <th>{{ __('Bank Logo') }}</th>
                        <th>
                            <div>{{ __('Status') }}</div>
                            <div class="btn-group mt-1" role="group">
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="toggleAllBankStatuses('status', 1)">
                                    {{ __('Hepsini Aç') }}
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="toggleAllBankStatuses('status', 0)">
                                    {{ __('Hepsini Kapat') }}
                                </button>
                            </div>
                        </th>
                        <th>
                            <div>{{ __('Transaction Status') }}</div>
                            <div class="btn-group mt-1" role="group">
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="toggleAllBankStatuses('transaction_status', 1)">
                                    {{ __('Hepsini Aç') }}
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="toggleAllBankStatuses('transaction_status', 0)">
                                    {{ __('Hepsini Kapat') }}
                                </button>
                            </div>
                        </th>
                        <th>
                            <div>{{ __('Withdrawal Status') }}</div>
                            <div class="btn-group mt-1" role="group">
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="toggleAllBankStatuses('withdrawal_status', 1)">
                                    {{ __('Hepsini Aç') }}
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="toggleAllBankStatuses('withdrawal_status', 0)">
                                    {{ __('Hepsini Kapat') }}
                                </button>
                            </div>
                        </th>
                        <th>{{ __('Created At') }}</th>
                        <th>{{ __('Updated At') }}</th>
                        <th class="text-center" style="width: 20px;">
                            <i class="ph-dots-three"></i>
                        </th>
                    </tr>
                    </thead>
                    <tbody id="banks-sortable">

                    @forelse($items as $item)
                        <tr data-id="{{ $item->id }}" class="bank-row">
                            <td class="dragula-handle" style="cursor: move;">
                                <i class="ph-dots-six-vertical"></i>
                            </td>
                            <td>{{ $item->name }}</td>
                            <td>
                                @if($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="img-thumbnail"
                                         style="max-width: 50px; max-height: 50px;">
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                {!! $item->status_html !!}
                            </td>

                            <td>
                                {!! $item->transaction_status_html !!}
                            </td>
                            <td>
                                {!! $item->withdrawal_status_html !!}
                            </td>
                            <td>{{ $item->created_at->isoFormat('DD MMM YYYY HH:mm') }}</td>
                            <td>{{ $item->updated_at->isoFormat('DD MMM YYYY HH:mm') }}</td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" class="text-body" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                        <i class="ph-list"></i>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end" data-popper-placement="top-start"
                                         data-popper-reference-hidden="">
                                        <div class="dropdown-header">{{ __('Options') }}</div>
                                        @can('banks-show')
                                            <a href="#" class="dropdown-item"
                                               data-url="{{ route('admin.banks.show', $item->id) }}"
                                               data-bs-toggle="modal"
                                               data-bs-target="#show_modal">
                                                <i class="ph-eye me-2"></i>
                                                {{ __('Show bank') }}
                                            </a>
                                        @endcan
                                        @can('banks-edit')
                                            <a href="{{ route('admin.banks.edit', $item->id) }}" class="dropdown-item">
                                                <i class="ph-pen me-2"></i>
                                                {{ __('Edit bank') }}
                                            </a>
                                        @endcan
                                        @can('banks-delete')
                                            <a href="#" class="dropdown-item text-danger"
                                               data-delete-url="{{ route('admin.banks.destroy', $item->id) }}"
                                               data-item-name="bank {{ $item->name }}">
                                                <i class="ph-trash me-2"></i>
                                                {{ __('Delete bank') }}
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center">{{ __('No banks found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
                 </div>
     </div>

     <div id="show_modal" class="modal fade" tabindex="-1">
         <div class="modal-dialog">
             <div class="modal-content">
                 <div class="modal-header">
                     <h5 class="modal-title">{{ __('Show bank') }}</h5>
                     <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                 </div>

                 <div class="modal-body">
                     <div class="row mb-2">
                         <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('ID') }}:</div>
                         <div class="col-12 col-sm-7 text-sm-end" id="id">-</div>
                     </div>
                     <div class="row mb-2">
                         <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Bank Name') }}:</div>
                         <div class="col-12 col-sm-7 text-sm-end" id="name">-</div>
                     </div>
                     <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Bank Logo') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="image-url">-</div>
                    </div>
                     <div class="row mb-2">
                         <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Priority') }}:</div>
                         <div class="col-12 col-sm-7 text-sm-end" id="priority">-</div>
                     </div>
                     <div class="row mb-2">
                         <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Status') }}:</div>
                         <div class="col-12 col-sm-7 text-sm-end" id="status-html">-</div>
                     </div>
                     <div class="row mb-2">
                         <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Transaction Status') }}:</div>
                         <div class="col-12 col-sm-7 text-sm-end" id="transaction-status-html">-</div>
                     </div>
                     <div class="row mb-2">
                         <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Withdrawal Status') }}:</div>
                         <div class="col-12 col-sm-7 text-sm-end" id="withdrawal-status-html">-</div>
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
                     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                 </div>
             </div>
         </div>
     </div>
 @endsection


@push('scripts')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <style>
        /* Drag and Drop Styles */
        .dragula-handle {
            cursor: move;
            color: #999;
            transition: color 0.2s;
        }

        .dragula-handle:hover {
            color: #333;
        }

        .bank-row.ui-sortable-helper {
            display: table !important;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            opacity: 0.9;
            cursor: grabbing !important;
        }

        .bank-row.ui-sortable-helper td {
            display: table-cell !important;
        }

        .bank-row.ui-sortable-placeholder {
            opacity: 0.3;
            background: #f0f0f0;
        }

        #banks-sortable .bank-row {
            transition: background-color 0.2s;
        }

        #banks-sortable .bank-row:hover {
            background-color: #f8f9fa;
        }
    </style>

    <script>
        function initializeBanksDragAndDrop() {
            if (typeof $.fn.sortable === 'undefined') {
                console.warn('Warning - jQuery UI Sortable is not loaded.');
                return;
            }

            const $container = $('#banks-sortable');
            if ($container.length === 0) {
                console.warn('Container not found');
                return;
            }

            $container.sortable({
                handle: '.dragula-handle',
                axis: 'y',
                cursor: 'move',
                placeholder: 'ui-sortable-placeholder',
                helper: function(e, tr) {
                    var $originals = tr.children();
                    var $helper = tr.clone();
                    $helper.children().each(function(index) {
                        $(this).width($originals.eq(index).width());
                    });
                    return $helper;
                },
                start: function(event, ui) {
                    ui.placeholder.height(ui.item.height());
                },
                update: function(event, ui) {
                    updateBankPriorities();
                }
            });

        }

        function updateBankPriorities() {
            const rows = document.querySelectorAll('#banks-sortable .bank-row');
            const priorities = {};

            rows.forEach((row, index) => {
                const bankId = row.getAttribute('data-id');
                const newPriority = index + 1;
                priorities[bankId] = newPriority;
            });

            fetch('{{ route('admin.banks.update-priorities') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ priorities: priorities })
            })
            .then(response => response.json())
            .then(data => {
                // Priorities updated successfully - no notification needed
            })
            .catch(error => {
                console.error('Error updating priorities:', error);
                alert('{{ __('Error updating priorities') }}');
            });
        }

        // Toggle all bank statuses functionality
        function toggleAllBankStatuses(field, value) {
            const bankIds = [];
            document.querySelectorAll('#banks-sortable .bank-row').forEach(row => {
                bankIds.push(row.getAttribute('data-id'));
            });

            if (bankIds.length === 0) {
                alert('{{ __('No banks found to update') }}');
                return;
            }

            // Show confirmation
            const fieldNames = {
                'status': '{{ __('Status') }}',
                'transaction_status': '{{ __('Transaction Status') }}',
                'withdrawal_status': '{{ __('Withdrawal Status') }}'
            };

            const statusText = value === 1 ? '{{ __('Active') }}' : '{{ __('Inactive') }}';
            const confirmMessage = `${bankIds.length} {{ __('banks') }} ${fieldNames[field]} ${statusText} {{ __('olarak güncellenecek. Emin misiniz?') }}`;

            if (!confirm(confirmMessage)) {
                return;
            }

            // Send AJAX request
            fetch('{{ route('admin.banks.bulk-update-status') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    bank_ids: bankIds,
                    field: field,
                    value: value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data == 200) {
                    // Reload page after request completes
                    window.location.reload();
                } else {
                    alert(data.message || '{{ __('Error updating banks') }}');
                }
            })
            .catch(error => {
                console.error('Error updating bank statuses:', error);
                alert('{{ __('Error updating banks') }}');
            });
        }

        // Module-specific functionality for banks
        document.addEventListener('DOMContentLoaded', function () {
            initializeBanksDragAndDrop();
        });
    </script>
@endpush
