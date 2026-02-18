@extends('vendor.layouts.app')
@section('title', $title)

@section('content')
    <div class="content">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ $title }}</h5>
            </div>

            <div class="card-body">
                @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        <div class="alert alert-danger border-0 alert-dismissible fade show">
                            {{ $error }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endforeach
                @endif

                <form action="{{ $action }}" method="POST">
                    @method($method)
                    @csrf

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Role Name') }}:</label>
                        <div class="col-lg-9">
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   placeholder="{{ __('Role Name') }}" value="{{ old('name', $item->name ?? '') }}" required/>
                            @error('name')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Color') }}:</label>
                        <div class="col-lg-9">
                            <select name="color" class="form-control @error('color') is-invalid @enderror" id="colorSelect">
                                <option value="">{{ __('Select Color') }}</option>
                                <option value="bg-primary" {{ old('color', $item->color ?? '') == 'bg-primary' ? 'selected' : '' }} data-color="primary">
                                    🔵 {{ __('Primary') }}
                                </option>
                                <option value="bg-secondary" {{ old('color', $item->color ?? '') == 'bg-secondary' ? 'selected' : '' }} data-color="secondary">
                                    ⚪ {{ __('Secondary') }}
                                </option>
                                <option value="bg-success" {{ old('color', $item->color ?? '') == 'bg-success' ? 'selected' : '' }} data-color="success">
                                    🟢 {{ __('Success') }}
                                </option>
                                <option value="bg-danger" {{ old('color', $item->color ?? '') == 'bg-danger' ? 'selected' : '' }} data-color="danger">
                                    🔴 {{ __('Danger') }}
                                </option>
                                <option value="bg-warning" {{ old('color', $item->color ?? '') == 'bg-warning' ? 'selected' : '' }} data-color="warning">
                                    🟡 {{ __('Warning') }}
                                </option>
                                <option value="bg-info" {{ old('color', $item->color ?? '') == 'bg-info' ? 'selected' : '' }} data-color="info">
                                    🔵 {{ __('Info') }}
                                </option>
                                <option value="bg-light" {{ old('color', $item->color ?? '') == 'bg-light' ? 'selected' : '' }} data-color="light">
                                    ⚪ {{ __('Light') }}
                                </option>
                                <option value="bg-dark" {{ old('color', $item->color ?? '') == 'bg-dark' ? 'selected' : '' }} data-color="dark">
                                    ⚫ {{ __('Dark') }}
                                </option>
                            </select>
                            @error('color')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Status') }}:</label>
                        <div class="col-lg-9">
                            <select name="status" class="form-control">
                                <option value="1" {{ old('status', $item->status ?? 1) == 1 ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="0" {{ old('status', $item->status ?? 1) == 0 ? 'selected' : '' }}>{{ __('Deactive') }}</option>
                            </select>
                            @error('status')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    @if(isset($permissions) && $permissions->count() > 0)
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Permissions') }}:</label>
                        <div class="col-lg-9">
                            <div class="mb-3">
                                <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="selectAllPermissions()">
                                    {{ __('Select All') }}
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllPermissions()">
                                    {{ __('Deselect All') }}
                                </button>
                            </div>
                            @php
                                $selectedPermissions = old('permissions', $rolePermissions ?? []);

                                $groupedPermissions = [];
                                foreach($permissions as $permission) {
                                    $parts = explode('-', $permission->name);
                                    $group = $parts[0] ?? 'other';
                                    if (!isset($groupedPermissions[$group])) {
                                        $groupedPermissions[$group] = [];
                                    }
                                    $groupedPermissions[$group][] = $permission;
                                }

                                $groupNames = [
                                    'users' => __('Users'),
                                    'roles' => __('Roles'),
                                    'permissions' => __('Permissions'),
                                    'merchants' => __('Merchants'),
                                    'wallets' => __('Wallets'),
                                    'transactions' => __('Transactions'),
                                    'providers' => __('Providers'),
                                    'banks' => __('Banks'),
                                    'withdrawals' => __('Withdrawals'),
                                    'activity-logs' => __('Activity Logs'),
                                    'other' => __('Other'),
                                    'settings' => __('Settings'),
                                    'statistics' => __('Statistics'),
                                    'activity' => __('Activity Logs'),
                                    'vendor' => __('Vendor'),
                                    'sites' => __('Sites')
                                ];
                            @endphp

                            @foreach($groupedPermissions as $groupKey => $groupPermissions)
                            <div class="card mb-3">
                                <div class="card-header bg-light py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <i class="ph-folder me-2"></i>
                                            {{ $groupNames[$groupKey] ?? ucfirst($groupKey) }}
                                        </h6>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                                    onclick="selectGroupPermissions('{{ $groupKey }}')">
                                                {{ __('Select All') }}
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    onclick="deselectGroupPermissions('{{ $groupKey }}')">
                                                {{ __('Deselect All') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body py-2">
                                    <div class="row">
                                        @foreach($groupPermissions as $permission)
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input
                                                    type="checkbox"
                                                    name="permissions[]"
                                                    value="{{ $permission->id }}"
                                                    class="form-check-input permission-checkbox group-{{ $groupKey }}"
                                                    id="permission_{{ $permission->id }}"
                                                    {{ in_array($permission->id, $selectedPermissions) ? 'checked' : '' }}
                                                />
                                                <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                    {{ __($permission->name) }}
                                                </label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            @error('permissions')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                            @error('permissions.*')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    @endif

                    <div class="text-end">
                        <button type="button" class="btn btn-danger" onclick="history.back()">
                            <i class="ph-arrow-left me-2"></i> {{ __('Back') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ __('Save') }} <i class="ph-paper-plane-tilt ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function selectAllPermissions() {
            document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
                checkbox.checked = true;
            });
        }

        function deselectAllPermissions() {
            document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
                checkbox.checked = false;
            });
        }

        function selectGroupPermissions(groupKey) {
            document.querySelectorAll('.group-' + groupKey).forEach(function(checkbox) {
                checkbox.checked = true;
            });
        }

        function deselectGroupPermissions(groupKey) {
            document.querySelectorAll('.group-' + groupKey).forEach(function(checkbox) {
                checkbox.checked = false;
            });
        }
    </script>
@endsection
