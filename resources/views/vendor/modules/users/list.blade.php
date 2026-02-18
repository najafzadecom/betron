@extends('vendor.layouts.app')
@section('title', $title)
@section('content')
    <div class="content">
        <div class="card">
            <div class="card-header d-flex">
                <h5 class="mb-0">{{ $module }}</h5>
                <div class="ms-auto">
                    @can('vendor-users-create')
                        <a href="{{ route('vendor.users.create') }}" class="btn btn-primary btn-sm">
                            <i class="ph-plus me-1"></i> {{ __('Create') }}
                        </a>
                    @endcan
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
                                <label class="form-label">{{ __('Role') }}</label>
                                <select name="role_id" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}"{{ request('role_id') == $role->id ? ' selected' : '' }}>{{ $role->name }}</option>
                                    @endforeach
                                </select>
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
                        {!! sortableTableHeader('name', 'Name', 'vendor_users') !!}
                        {!! sortableTableHeader('email', 'E-mail', 'vendor_users') !!}
                        <th>{{ __('Roles') }}</th>
                        {!! sortableTableHeader('status', 'Status', 'vendor_users') !!}
                        <th class="text-center" style="width: 20px;">
                            <i class="ph-dots-three"></i>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->email }}</td>
                            <td>{!! $item->coloredRoleNames !!}</td>
                            <td>
                                <x-badge :title="$item->status ? __('Active') : __('Inactive')" :color="$item->status ? 'bg-success' : 'bg-danger'"/>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" class="text-body" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                        <i class="ph-list"></i>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end">
                                        <div class="dropdown-header">{{ __('Options') }}</div>
                                        @can('vendor-users-show')
                                            <a href="#" class="dropdown-item"
                                               data-url="{{ route('vendor.users.show', $item->id) }}"
                                               data-bs-toggle="modal" data-bs-target="#show_modal">
                                                <i class="ph-eye me-2"></i>
                                                {{ __('View') }}
                                            </a>
                                        @endcan
                                        @can('vendor-users-edit')
                                            <a href="{{ route('vendor.users.edit', $item->id) }}"
                                               class="dropdown-item">
                                                <i class="ph-pen me-2"></i>
                                                {{ __('Edit') }}
                                            </a>
                                        @endcan
                                        @can('vendor-users-delete')
                                            <a href="#" class="dropdown-item text-danger"
                                               data-delete-url="{{ route('vendor.users.destroy', $item->id) }}"
                                               data-item-name="{{ $item->name }}">
                                                <i class="ph-trash me-2"></i>
                                                {{ __('Delete') }}
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">{{ __('Data not found') }}</td>
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
                    <h5 class="modal-title">{{ __('User Details') }}</h5>
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
                        <div class="col-5 fw-semibold text-muted">{{ __('Phone') }}:</div>
                        <div class="col-7 text-end" id="phone">-</div>
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
                        document.getElementById('phone').innerText = data.phone ?? '-';
                        document.getElementById('status').innerText = data.status ? '{{ __("Active") }}' : '{{ __("Inactive") }}';
                    })
                    .catch(error => {
                        console.error('Error fetching user data:', error);
                    });
            });
        });

        function clearFilters() {
            window.location.href = '{{ route('vendor.users.index') }}';
        }
    </script>
@endpush
