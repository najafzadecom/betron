@extends('admin.layouts.app')
@section('title', $title)
@section('content')
    <div class="content">
        <div class="card">
            <div class="card-header d-flex">
                <h5 class="mb-0">{{ $module }}</h5>
                <div class="ms-auto">
                    <!-- Activity logs için create button olmayacaq -->
                </div>
            </div>

            <div class="card-body">
                <form action="" method="GET" id="searchForm">
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
                                <label class="form-label">{{ __('Subject Type') }}</label>
                                <select name="subject_type" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach(\App\Services\ActivityLogService::make()->getUniqueSubjectTypes() as $type)
                                        <option value="{{ $type['value'] }}"{{ request('subject_type') == $type['value'] ? ' selected' : '' }}>
                                            {{ $type['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Description') }}</label>
                                <select name="description" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach(\App\Services\ActivityLogService::make()->getUniqueDescriptions() as $desc)
                                        <option value="{{ $desc['value'] }}"{{ request('description') == $desc['value'] ? ' selected' : '' }}>
                                            {{ $desc['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Causer') }}</label>
                                <input type="text" name="causer" class="form-control"
                                       placeholder="{{ __('User name or email') }}"
                                       value="{{ request('causer') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Subject ID') }}</label>
                                <input type="text" name="subject_id" class="form-control"
                                       placeholder="{{ __('Subject ID') }}"
                                       value="{{ request('subject_id') }}">
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

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Log Name') }}</label>
                                <input type="text" name="log_name" class="form-control"
                                       placeholder="{{ __('Log name') }}"
                                       value="{{ request('log_name') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Event') }}</label>
                                <input type="text" name="event" class="form-control"
                                       placeholder="{{ __('Event name') }}"
                                       value="{{ request('event') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Batch UUID') }}</label>
                                <input type="text" name="batch_uuid" class="form-control"
                                       placeholder="{{ __('Batch UUID') }}"
                                       value="{{ request('batch_uuid') }}">
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
                        {!! sortableTableHeader('id', 'ID', 'activity-logs') !!}
                        {!! sortableTableHeader('description', 'Description', 'activity-logs') !!}
                        {!! sortableTableHeader('subject_type', 'Subject Type', 'activity-logs') !!}
                        {!! sortableTableHeader('subject_id', 'Subject ID', 'activity-logs') !!}
                        {!! sortableTableHeader('causer_id', 'Causer', 'activity-logs') !!}
                        {!! sortableTableHeader('created_at', 'Created At', 'activity-logs') !!}
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
                                <span class="badge
                                    @switch($item->description)
                                        @case('created') bg-success @break
                                        @case('updated') bg-warning @break
                                        @case('deleted') bg-danger @break
                                        @case('restored') bg-info @break
                                        @default bg-dark
                                    @endswitch
                                    bg-opacity-10
                                    @switch($item->description)
                                        @case('created') text-success @break
                                        @case('updated') text-warning @break
                                        @case('deleted') text-danger @break
                                        @case('restored') text-info @break
                                        @default text-dark
                                    @endswitch
                                ">
                                    {{ $item->description_display }}
                                </span>
                            </td>
                            <td>
                                @if($item->subject_type)
                                    <span class="badge bg-primary bg-opacity-10 text-primary">{{ __($item->subject_type_display) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item->subject_id)
                                    <span class="fw-semibold">#{{ $item->subject_id }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <span class="fw-semibold">{{ $item->causer_name }}</span>
                                    @if($item->causer && $item->causer->email)
                                        <div class="text-muted small">{{ $item->causer->email }}</div>
                                    @endif
                                </div>
                            </td>
                                                            <td>{{ $item->created_at->isoFormat('DD MMM YYYY HH:mm') }}</td>
                            <td>
                                @canany(['activity-logs.show'])
                                <div class="dropdown">
                                    <a href="#" class="text-body" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                        <i class="ph-list"></i>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end" data-popper-placement="top-start"
                                         data-popper-reference-hidden="">
                                        <div class="dropdown-header">{{ __('Options') }}</div>
                                        @can('activity-logs.show')
                                        <a href="#" class="dropdown-item"
                                           data-url="{{ route('admin.activity-logs.show', $item->id) }}"
                                           data-bs-toggle="modal" data-bs-target="#show_modal">
                                            <i class="ph-eye me-2"></i>
                                            {{ __('Show activity log') }}
                                        </a>
                                        @endcan
                                    </div>
                                </div>
                                @endcanany
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">{{ __('Data not found') }}</td>
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
                            <option value="{{ $limit }}"{{ request('limit', 25) == $limit ? ' selected' : '' }}>{{ $limit }}</option>
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Show activity log') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('ID') }}:</div>
                        <div class="col-8 text-end" id="id">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('Log Name') }}:</div>
                        <div class="col-8 text-end" id="log-name">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('Description') }}:</div>
                        <div class="col-8 text-end" id="description">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('Subject Type') }}:</div>
                        <div class="col-8 text-end" id="subject-type">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('Subject ID') }}:</div>
                        <div class="col-8 text-end" id="subject-id">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('Causer Type') }}:</div>
                        <div class="col-8 text-end" id="causer-type">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('Causer ID') }}:</div>
                        <div class="col-8 text-end" id="causer-id">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('Causer Name') }}:</div>
                        <div class="col-8 text-end" id="causer-name">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('Event') }}:</div>
                        <div class="col-8 text-end" id="event">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('Batch UUID') }}:</div>
                        <div class="col-8 text-end" id="batch-uuid">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('Created At') }}:</div>
                        <div class="col-8 text-end" id="created-at">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('Updated At') }}:</div>
                        <div class="col-8 text-end" id="updated-at">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-semibold text-muted mb-2">{{ __('Properties') }}:</h6>
                            <div id="properties" class="border p-3 bg-light">-</div>
                        </div>
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
        // Change limit
        function changeLimit(limit) {
            const url = new URL(window.location);
            url.searchParams.set('limit', limit);
            url.searchParams.delete('page'); // Reset to first page
            window.location.href = url.toString();
        }

        // Module-specific functionality for activity logs
        document.addEventListener('DOMContentLoaded', function () {
            // Custom modal field mapping for activity logs
            const showModal = document.getElementById('show_modal');

            showModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const url = button.getAttribute('data-url');

                fetch(url)
                    .then(response => response.json())
                    .then(responseData => {
                        const data = responseData.item;

                        // Set basic fields using the common modal handler pattern
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

                        // Set properties with formatting
                        const propertiesDiv = document.getElementById('properties');
                        if (data.properties && Object.keys(data.properties).length > 0) {
                            propertiesDiv.innerHTML = '<pre>' + JSON.stringify(data.properties, null, 2) + '</pre>';
                        } else {
                            propertiesDiv.innerText = '-';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                    });
            });
        });
    </script>
@endpush
