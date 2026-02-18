@extends('admin.layouts.app')
@section('title', $title)

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#roles_select').select2({
                placeholder: "{{ __('Select role') }}",
                allowClear: true,
                width: '100%'
            });

            $('#vendor_select').select2({
                placeholder: "{{ __('Select vendor') }}",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@endpush

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
                        <label class="col-lg-3 col-form-label">{{ __('Vendor') }}:</label>
                        <div class="col-lg-9">
                            <select id="vendor_select" name="vendor_id" class="form-control @error('vendor_id') is-invalid @enderror" required>
                                <option value="">{{ __('Select vendor') }}</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}"
                                        @selected(old('vendor_id', $item->vendor_id ?? '') == $vendor->id)>
                                        {{ $vendor->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('vendor_id')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Name') }}:</label>
                        <div class="col-lg-9">
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   placeholder="{{ __('Name') }}" value="{{ old('name', $item->name ?? '') }}" required/>
                            @error('name')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Email') }}:</label>
                        <div class="col-lg-9">
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   placeholder="{{ __('Email') }}" value="{{ old('email', $item->email ?? '') }}" required/>
                            @error('email')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Password') }}{{ isset($item) ? '' : ' *' }}:</label>
                        <div class="col-lg-9">
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                   placeholder="{{ __('Password') }}" {{ !isset($item) ? 'required' : '' }}/>
                            @if(isset($item))
                                <small class="text-muted">{{ __('Leave blank if you don\'t want to change') }}</small>
                            @endif
                            @error('password')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Password Confirmation') }}{{ isset($item) ? '' : ' *' }}:</label>
                        <div class="col-lg-9">
                            <input type="password" name="password_confirmation" class="form-control"
                                   placeholder="{{ __('Password Confirmation') }}" {{ !isset($item) ? 'required' : '' }}/>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Roles') }}:</label>
                        <div class="col-lg-9">
                            <select id="roles_select" name="roles[]" multiple class="form-control @error('roles') is-invalid @enderror">
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}"
                                        @selected(in_array($role->id, old('roles', isset($item) ? $item->roles->pluck('id')->toArray() : [])))>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('roles')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Status') }}:</label>
                        <div class="col-lg-9">
                            <select name="status" class="form-control">
                                <option value="1" @selected(old('status', $item->status ?? 1) == 1)>{{ __('Active') }}</option>
                                <option value="0" @selected(old('status', $item->status ?? 0) == 0)>{{ __('Inactive') }}</option>
                            </select>
                        </div>
                    </div>

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
@endsection
