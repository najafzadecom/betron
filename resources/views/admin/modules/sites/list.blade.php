@extends('admin.layouts.app')
@section('title', $title)
@section('content')
    <div class="content">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <h5 class="mb-0">{{ $module }}</h5>
                <div class="ms-auto d-flex gap-2">
                    <x-buttons.create title="{{ __('Create') }}" url="{{ route('admin.sites.create') }}"
                                      permission="sites-create"/>
                </div>
            </div>

            <div class="card-body">
                <form action="" method="GET" id="searchForm">
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Site Name') }}</label>
                                <input type="text" name="name" class="form-control"
                                       placeholder="{{ __('Site Name') }}"
                                       value="{{ request('name') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select name="status" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    <option
                                        value="1" {{ request('status') == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option
                                        value="0" {{ request('status') == '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
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
                <table class="table text-nowrap">
                    <thead>
                    <tr>
                        {!! sortableTableHeader('id', 'ID', 'sites') !!}
                        {!! sortableTableHeader('name', 'Site Name', 'sites') !!}
                        <th>{{ __('Site Logo') }}</th>
                        {!! sortableTableHeader('url', 'Site Url', 'sites') !!}
                        {!! sortableTableHeader('status', 'Status', 'sites') !!}
                        {!! sortableTableHeader('created_at', 'Created At', 'sites') !!}
                        {!! sortableTableHeader('updated_at', 'Updated At', 'sites') !!}
                        <th class="text-center" style="width: 20px;">
                            <i class="ph-dots-three"></i>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
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
                                <span class="badge bg-info">{{ $item->url }}</span>
                            </td>
                            <td>
                                {!! $item->status_html !!}
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
                                        @can('sites-show')
                                            <a href="#" class="dropdown-item"
                                               data-url="{{ route('admin.sites.show', $item->id) }}"
                                               data-bs-toggle="modal"
                                               data-bs-target="#show_modal">
                                                <i class="ph-eye me-2"></i>
                                                {{ __('Show site') }}
                                            </a>
                                        @endcan
                                        @can('sites-edit')
                                            <a href="{{ route('admin.sites.edit', $item->id) }}" class="dropdown-item">
                                                <i class="ph-pen me-2"></i>
                                                {{ __('Edit site') }}
                                            </a>
                                        @endcan
                                        @can('sites-delete')
                                            <a href="#" class="dropdown-item text-danger"
                                               data-delete-url="{{ route('admin.sites.destroy', $item->id) }}"
                                               data-item-name="{{ $item->name }}">
                                                <i class="ph-trash me-2"></i>
                                                {{ __('Delete site') }}
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">{{ __('No sites found') }}</td>
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
                    <h5 class="modal-title">{{ __('Show site') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('ID') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="id">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Site Name') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="name">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Site Logo') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="image-url">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Site Url') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="url">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Description') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="description">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Token') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="token">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Status') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="status-html">-</div>
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
