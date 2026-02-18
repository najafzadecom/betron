@extends('admin.layouts.app')
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
                        <label class="col-lg-3 col-form-label">{{ __('Name') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="text"
                                name="name"
                                class="form-control  @error('name') is-invalid @enderror"
                                placeholder="{{ __('Name') }}"
                                value="{{ old('name', $item->name ?? '') }}"
                            />
                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('E-mail') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="email"
                                name="email"
                                class="form-control  @error('email') is-invalid @enderror"
                                placeholder="{{ __('E-mail') }}"
                                value="{{ old('email', $item->email ?? '') }}"
                            />
                            @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Username') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="text"
                                name="username"
                                class="form-control  @error('username') is-invalid @enderror"
                                placeholder="{{ __('Username') }}"
                                value="{{ old('username', $item->username ?? '') }}"
                            />
                            @error('username')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Telegram') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="text"
                                name="telegram"
                                class="form-control  @error('telegram') is-invalid @enderror"
                                placeholder="{{ __('Telegram') }}"
                                value="{{ old('telegram', $item->telegram ?? '') }}"
                            />
                            @error('telegram')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Roles') }}:</label>
                        <div class="col-lg-9">
                            <select
                                multiple="multiple"
                                name="roles[]"
                                data-placeholder="{{ __('Select roles') }}"
                                class="form-control form-control-select2-icons"
                            >
                                @forelse($roles as $role)
                                    <option
                                        @selected(in_array($role->name, old('roles', isset($item) ? $item->getRoleNames()->toArray() : []))) value="{{ $role->name }}">{{ $role->name }}</option>
                                @empty
                                    <option value="">{{ __('Role not found') }}</option>
                                @endforelse
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Status') }}:</label>
                        <div class="col-lg-9">
                            <select
                                name="status"
                                class="form-control form-control-select2 @error('status') is-invalid @enderror"
                            >
                                <option value="1" @selected(old('status', $item->status ?? '') == 1)>{{ __('Active') }}</option>
                                <option value="0" @selected(old('status', $item->status ?? '') == 0)>{{ __('Inactive') }}</option>
                            </select>
                            @error('status')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Password') }}:</label>
                        <div class="col-lg-9">
                            <div class="form-control-feedback input-group">
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="{{ __('Password') }}"
                                />
                                <span class="input-group-text cursor-pointer" onclick="togglePassword()"
                                      data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Show Password') }}"
                                      data-show-text="{{ __('Show Password') }}" data-hide-text="{{ __('Hide Password') }}">
                                    <i class="ph-eye text-muted" id="togglePassword"></i>
                                </span>
                            </div>
                            @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Password Confirmation') }}:</label>
                        <div class="col-lg-9">
                            <div class="form-control-feedback  input-group">
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    id="password_confirmation"
                                    class="form-control @error('password_confirmation') is-invalid @enderror"
                                    placeholder="{{ __('Password Confirmation') }}"
                                />
                                <span class="input-group-text cursor-pointer" onclick="togglePasswordConfirmation()"
                                      data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Show Password') }}"
                                      data-show-text="{{ __('Show Password') }}" data-hide-text="{{ __('Hide Password') }}">
                                    <i class="ph-eye text-muted" id="togglePasswordConfirmation"></i>
                                </span>
                            </div>
                            @error('password_confirmation')
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
                                    // Permission-ları qruplara ayır
                                    $groupedPermissions = [];
                                    foreach($permissions as $permission) {
                                        $parts = explode('-', $permission->name);
                                        $group = $parts[0] ?? 'other';
                                        if (!isset($groupedPermissions[$group])) {
                                            $groupedPermissions[$group] = [];
                                        }
                                        $groupedPermissions[$group][] = $permission;
                                    }

                                    // Qrup adlarını tərcümə etmək üçün mapping
                                    $groupNames = [
                                        'accounts' => __('Accounts'),
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
                        <button type="button" class="btn btn-danger" onclick="history.back()"><i
                                class="ph-arrow-left me-2"></i> {{ __('Back') }} </button>
                        <button type="submit" class="btn btn-primary">{{ __('Submit') }} <i
                                class="ph-paper-plane-tilt ms-2"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
