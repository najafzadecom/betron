@extends('admin.layouts.app')
@section('title', $title)
@section('content')
    <div class="content">
        <div class="card">
            <div class="card-header d-flex">
                <h5 class="mb-0">{{ $module }}</h5>
                <div class="ms-auto">
                    <x-buttons.create title="{{ __('Create') }}" url="{{ route('admin.blacklists.create') }}"
                                      permission="blacklists-create"/>
                </div>
            </div>

            <div class="card-body">
                <form action="" method="GET" id="searchForm">
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('User ID') }}</label>
                                <input type="number" name="user_id" class="form-control"
                                       placeholder="{{ __('User ID') }}"
                                       value="{{ request('user_id') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('IP Address') }}</label>
                                <input type="text" name="ip_address" class="form-control"
                                       placeholder="{{ __('IP Address') }}"
                                       value="{{ request('ip_address') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Type') }}</label>
                                <select name="type" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    <option
                                        value="user_id" {{ request('type') == 'user_id' ? 'selected' : '' }}>{{ __('User ID') }}</option>
                                    <option
                                        value="ip_address" {{ request('type') == 'ip_address' ? 'selected' : '' }}>{{ __('IP Address') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Site') }}</label>
                                <select name="site_id" class="form-select">
                                    <option value="">{{ __('All Sites') }}</option>
                                    @foreach($sites as $site)
                                        <option
                                            value="{{ $site->id }}"{{ request('site_id') == $site->id ? ' selected' : '' }}>
                                            {{ $site->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select name="is_active" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    <option
                                        value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option
                                        value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Reason') }}</label>
                                <input type="text" name="reason" class="form-control"
                                       placeholder="{{ __('Reason') }}"
                                       value="{{ request('reason') }}">
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
                <table class="table text-nowrap">
                    <thead>
                    <tr>
                        {!! sortableTableHeader('id', 'ID', 'blacklists') !!}
                        {!! sortableTableHeader('type', 'Type', 'blacklists') !!}
                        <th>{{ __('User ID') }}</th>
                        <th>{{ __('IP Address') }}</th>
                        <th>{{ __('Reason') }}</th>
                        {!! sortableTableHeader('site', 'Site', 'blacklists') !!}
                        {!! sortableTableHeader('is_active', 'Status', 'blacklists') !!}
                        {!! sortableTableHeader('created_at', 'Created At', 'blacklists') !!}
                        {!! sortableTableHeader('updated_at', 'Updated At', 'blacklists') !!}
                        <th class="text-center" style="width: 20px;">
                            <i class="ph-dots-three"></i>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>
                                @if($item->type == 'user_id')
                                    <span class="badge bg-primary">{{ __('User ID') }}</span>
                                @else
                                    <span class="badge bg-info">{{ __('IP Address') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($item->type == 'user_id' && $item->user_id)
                                    <span class="badge bg-secondary">{{ $item->user_id }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item->type == 'ip_address' && $item->ip_address)
                                    <code>{{ $item->ip_address }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item->reason)
                                    <span title="{{ $item->reason }}">{{ Str::limit($item->reason, 30) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $item->site?->name }}</td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge bg-success">{{ __('Active') }}</span>
                                @else
                                    <span class="badge bg-danger">{{ __('Inactive') }}</span>
                                @endif
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
                                        @can('blacklists-show')
                                            <a href="#" class="dropdown-item"
                                               data-url="{{ route('admin.blacklists.show', $item->id) }}"
                                               data-bs-toggle="modal"
                                               data-bs-target="#show_modal">
                                                <i class="ph-eye me-2"></i>
                                                {{ __('Show blacklist') }}
                                            </a>
                                        @endcan
                                        @can('blacklists-edit')
                                            <a href="{{ route('admin.blacklists.edit', $item->id) }}"
                                               class="dropdown-item">
                                                <i class="ph-pen me-2"></i>
                                                {{ __('Edit blacklist') }}
                                            </a>
                                        @endcan
                                        @can('blacklists-edit')
                                            <a href="#" class="dropdown-item"
                                               onclick="toggleStatus({{ $item->id }})">
                                                <i class="ph-power me-2"></i>
                                                {{ $item->is_active ? __('Deactivate') : __('Activate') }}
                                            </a>
                                        @endcan
                                        @can('blacklists-delete')
                                            <a href="#" class="dropdown-item text-danger"
                                               data-delete-url="{{ route('admin.blacklists.destroy', $item->id) }}"
                                               data-item-name="blacklist entry">
                                                <i class="ph-trash me-2"></i>
                                                {{ __('Delete blacklist') }}
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">{{ __('No blacklists found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                <div class="row mb-3 align-items-center">
                    <div class="col-md-6 d-flex align-items-center">
                        <label for="limit" class="me-2 mb-0">{{ __('Display') }}:</label>
                        <select id="limit" name="limit" class="form-select w-auto" onchange="changeLimit(this.value)">
                            @foreach(config('pagination.per_pages') as $limit)
                                <option
                                    value="{{ $limit }}"{{ request('limit', 25) == $limit ? ' selected' : '' }}>{{ $limit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
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
                    <h5 class="modal-title">{{ __('Show blacklist') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('ID') }}:</div>
                        <div class="col-7 text-end" id="id">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Type') }}:</div>
                        <div class="col-7 text-end" id="type">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('User ID') }}:</div>
                        <div class="col-7 text-end" id="user-id">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('IP Address') }}:</div>
                        <div class="col-7 text-end" id="ip-address">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Reason') }}:</div>
                        <div class="col-7 text-end" id="reason">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold text-muted">{{ __('Status') }}:</div>
                        <div class="col-7 text-end" id="is-active">-</div>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>

        // Toggle status function
        function toggleStatus(id) {
            if (confirm('{{ __("Are you sure you want to change the status?") }}')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/manage/blacklists/toggle-status/${id}`;

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                document.body.appendChild(form);
                form.submit();
            }
        }

        // Module-specific functionality for blacklists
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize daterange pickers
            initializeDateRangePickers();
        });
    </script>
@endpush
