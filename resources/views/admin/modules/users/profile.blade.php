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

                @if(session()->has('message'))
                    <div class="alert {{ session('success') ? 'alert-success' : 'alert-danger' }} border-0 alert-dismissible fade show">
                        {{ session('message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
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
                                value="{{ $item->email ?? '' }}"
                                disabled="disabled"
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
                                value="{{ $item->username ?? '' }}"
                                disabled="disabled"
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
                        <label class="col-lg-3 col-form-label">{{ __('Password') }}:</label>
                        <div class="col-lg-9">
                            <div class="input-group">
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
                            <div class="input-group">
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
                    <div class="text-end">
                        <button type="button" class="btn btn-danger" onclick="history.back()"><i class="ph-arrow-left me-2"></i> {{ __('Back') }} </button>
                        <button type="submit" class="btn btn-primary">{{ __('Submit') }} <i class="ph-paper-plane-tilt ms-2"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
