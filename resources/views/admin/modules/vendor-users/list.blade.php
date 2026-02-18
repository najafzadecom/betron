@extends('admin.layouts.app')
@section('title', $title)
@section('content')
    <div class="content">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <h5 class="mb-0">{{ $module }}</h5>
                <div class="ms-auto d-flex gap-2">
                    <x-buttons.create title="{{ __('Create') }}" url="{{ route('admin.vendor-users.create') }}" permission="vendor-users-create"/>
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
                                <label class="form-label">{{ __('Name') }}</label>
                                <input type="text" name="name" class="form-control"
                                       placeholder="{{ __('Name') }}"
                                       value="{{ request('name') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Email') }}</label>
                                <input type="text" name="email" class="form-control"
                                       placeholder="{{ __('Email address') }}"
                                       value="{{ request('email') }}">
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
                                <label class="form-label">{{ __('Parent Vendor') }}</label>
                                <select id="parent_vendor_filter" name="parent_vendor_id" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @if(isset($topLevelVendors))
                                        @foreach($topLevelVendors as $vendor)
                                            <option value="{{ $vendor->id }}"{{ request('parent_vendor_id') == $vendor->id ? ' selected' : '' }}>
                                                {{ $vendor->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <small class="text-muted">{{ __('Shows users of all vendors under selected parent vendor') }}</small>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Child Vendor') }}</label>
                                <select id="vendor_filter" name="vendor_id" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @if(!request('parent_vendor_id'))
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}"{{ request('vendor_id') == $vendor->id ? ' selected' : '' }}>
                                                {{ $vendor->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Role') }}</label>
                                <select id="role_filter" name="role_id" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}"{{ request('role_id') == $role->id ? ' selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
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
                <table class="table table-xs text-nowrap">
                    <thead>
                    <tr>
                        {!! sortableTableHeader('id', 'ID', 'vendor_users') !!}
                        {!! sortableTableHeader('name', 'Name', 'vendor_users') !!}
                        {!! sortableTableHeader('email', 'E-mail', 'vendor_users') !!}
                        <th>{{ __('Vendor') }}</th>
                        <th>{{ __('Parent Vendor') }}</th>
                        <th>{{ __('Role') }}</th>
                        {!! sortableTableHeader('status', 'Status', 'vendor_users') !!}
                        {!! sortableTableHeader('created_at', 'Created At', 'vendor_users') !!}
                        {!! sortableTableHeader('updated_at', 'Updated At', 'vendor_users') !!}
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
                                <a target="_blank" href="mailto:{{ $item->email }}">{{ $item->email }}</a>
                            </td>
                            <td>
                                @if($item->vendor)
                                    <a href="{{ route('admin.vendors.edit', $item->vendor->id) }}">
                                        {{ $item->vendor->name }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($item->vendor && $item->vendor->parent)
                                    <a href="{{ route('admin.vendors.edit', $item->vendor->parent->id) }}">
                                        {{ $item->vendor->parent->name }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                {!! $item->coloredRoleNames !!}
                            </td>
                            <td>
                                {!! $item->status_html !!}
                            </td>
                            <td>{{ $item->created_at->isoFormat('DD MMM YYYY HH:mm') }}</td>
                            <td>{{ $item->updated_at->isoFormat('DD MMM YYYY HH:mm') }}</td>
                            <td>
                                @canany(['vendor-users-show', 'vendor-users-edit', 'vendor-users-delete'])
                                <div class="dropdown">
                                    <a href="#" class="text-body" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                        <i class="ph-list"></i>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end" data-popper-placement="top-start"
                                         data-popper-reference-hidden="">
                                        <div class="dropdown-header">{{ __('Options') }}</div>
                                        @can('vendor-users-show')
                                        <a href="#" class="dropdown-item"
                                           data-url="{{ route('admin.vendor-users.show', $item->id) }}" data-bs-toggle="modal"
                                           data-bs-target="#show_modal">
                                            <i class="ph-eye me-2"></i>
                                            {{ __('Show') }}
                                        </a>
                                        @endcan
                                        @can('vendor-users-edit')
                                        <a href="{{ route('admin.vendor-users.edit', $item->id) }}" class="dropdown-item">
                                            <i class="ph-pen me-2"></i>
                                            {{ __('Edit') }}
                                        </a>
                                        @endcan
                                        @can('vendor-users-delete')
                                        <a href="#" class="dropdown-item text-danger"
                                           data-delete-url="{{ route('admin.vendor-users.destroy', $item->id) }}"
                                           data-item-name="{{ $item->name }}">
                                            <i class="ph-trash me-2"></i>
                                            {{ __('Delete') }}
                                        </a>
                                        @endcan
                                    </div>
                                </div>
                                @endcanany
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">{{ __('Data not found') }}</td>
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
                            <option value="{{ $limit }}"{{ request('limit', 25) == $limit ? ' selected' : '' }}>{{ $limit }}</option>
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
                    <h5 class="modal-title">{{ __('Vendor User Details') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('ID') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="id">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Name') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="name">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('E-mail') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="email">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Phone') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="phone">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Vendor') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="vendor">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-5 fw-semibold text-muted">{{ __('Status') }}:</div>
                        <div class="col-12 col-sm-7 text-sm-end" id="status">-</div>
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
        // Module-specific functionality for vendor users
        document.addEventListener('DOMContentLoaded', function () {

            // Parent vendor filter change handler
            const parentVendorFilter = document.getElementById('parent_vendor_filter');
            const vendorFilter = document.getElementById('vendor_filter');
            const allVendors = @json($vendors->map(fn($v) => ['id' => $v->id, 'name' => $v->name])->toArray());

            if (parentVendorFilter && vendorFilter) {
                parentVendorFilter.addEventListener('change', function() {
                    const parentId = this.value;

                    if (parentId) {
                        // Show loading state
                        vendorFilter.disabled = true;
                        vendorFilter.innerHTML = '<option value="">{{ __("Loading...") }}</option>';

                        // Fetch child vendors via AJAX
                        fetch('{{ route("admin.vendor-users.get-child-vendors", ":id") }}'.replace(':id', parentId))
                            .then(response => response.json())
                            .then(data => {
                                // Clear and populate vendor filter with child vendors
                                vendorFilter.innerHTML = '<option value="">{{ __("All") }}</option>';

                                data.vendors.forEach(function(vendor) {
                                    const option = document.createElement('option');
                                    option.value = vendor.id;
                                    option.textContent = vendor.name;
                                    vendorFilter.appendChild(option);
                                });

                                vendorFilter.disabled = false;
                            })
                            .catch(error => {
                                console.error('Error fetching child vendors:', error);
                                vendorFilter.innerHTML = '<option value="">{{ __("All") }}</option>';
                                vendorFilter.disabled = false;
                            });
                    } else {
                        // Reset to all vendors
                        vendorFilter.innerHTML = '<option value="">{{ __("All") }}</option>';
                        const selectedVendorId = {{ request('vendor_id', 'null') }};
                        allVendors.forEach(function(vendor) {
                            const option = document.createElement('option');
                            option.value = vendor.id;
                            option.textContent = vendor.name;
                            if (selectedVendorId && vendor.id == selectedVendorId) {
                                option.selected = true;
                            }
                            vendorFilter.appendChild(option);
                        });
                    }
                });

                // Trigger change on page load if parent vendor is selected
                @if(request('parent_vendor_id'))
                    const initialParentId = {{ request('parent_vendor_id') }};
                    if (initialParentId) {
                        parentVendorFilter.value = initialParentId;
                        parentVendorFilter.dispatchEvent(new Event('change'));
                    }
                @endif
            }

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
                        document.getElementById('vendor').innerText = data.vendor?.name ?? '-';
                        document.getElementById('status').innerText = data.status ? 'Active' : 'Inactive';
                        document.getElementById('created-at').innerText = data.created_at ?? '-';
                        document.getElementById('updated-at').innerText = data.updated_at ?? '-';
                    })
                    .catch(error => {
                        console.error('Error fetching vendor user data:', error);
                    });
            });
        });

        function clearFilters() {
            window.location.href = '{{ route('admin.vendor-users.index') }}';
        }

        function toggleAdvancedFilters() {
            const advancedFilters = document.getElementById('advancedFilters');
            if (advancedFilters.style.display === 'none') {
                advancedFilters.style.display = 'block';
            } else {
                advancedFilters.style.display = 'none';
            }
        }

        function changeLimit(limit) {
            const url = new URL(window.location.href);
            url.searchParams.set('limit', limit);
            window.location.href = url.toString();
        }
    </script>
@endpush
