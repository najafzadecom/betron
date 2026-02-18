@extends('vendor.layouts.app')
@section('title', __('Change Password'))

@section('content')
    <div class="content">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Change Password') }}</h5>
            </div>

            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success border-0 alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        <div class="alert alert-danger border-0 alert-dismissible fade show">
                            {{ $error }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endforeach
                @endif

                <form action="{{ route('vendor.profile.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Current Password') }} *:</label>
                        <div class="col-lg-9">
                            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror"
                                   placeholder="{{ __('Current Password') }}" required/>
                            @error('current_password')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('New Password') }} *:</label>
                        <div class="col-lg-9">
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                   placeholder="{{ __('New Password') }}" required/>
                            @error('password')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Confirm New Password') }} *:</label>
                        <div class="col-lg-9">
                            <input type="password" name="password_confirmation" class="form-control"
                                   placeholder="{{ __('Confirm New Password') }}" required/>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            {{ __('Update Password') }} <i class="ph-paper-plane-tilt ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
