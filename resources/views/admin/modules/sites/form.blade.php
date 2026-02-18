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

                <form action="{{ $action }}" method="POST" enctype="multipart/form-data">
                    @method($method)
                    @csrf

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Site Name') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="text"
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="{{ __('Site Name') }}"
                                value="{{ old('name', $item->name ?? '') }}"
                                required
                            />
                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Site Url') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="text"
                                name="url"
                                class="form-control @error('url') is-invalid @enderror"
                                placeholder="{{ __('Site Url') }}"
                                value="{{ old('url', $item->url ?? '') }}"
                                required
                            />
                            @error('url')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Site Logo') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="file"
                                name="logo"
                                class="form-control @error('image') is-invalid @enderror"
                                accept="image/*"
                            />
                            <div class="form-text">{{ __('Site logo image (JPEG, PNG, GIF, WebP, max 2MB)') }}</div>

                            @if(isset($item) && $item->image_url)
                                <div class="mt-2">
                                    <label class="form-label">{{ __('Current Image') }}:</label><br>
                                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="img-thumbnail"
                                         style="max-width: 150px; max-height: 150px;">
                                </div>
                            @endif

                            @error('image')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Site Description') }}:</label>
                        <div class="col-lg-9">
                            <textarea name="description"
                                      class="form-control @error('url') is-invalid @enderror">{{ old('description', $item->description ?? '') }}</textarea>
                            @error('description')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Site Token') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="text"
                                name="token"
                                class="form-control @error('token') is-invalid @enderror"
                                placeholder="{{ __('Site Token') }}"
                                value="{{ old('token', $item->token ?? '') }}"
                            />
                            @error('token')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Transaction Fee') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                name="transaction_fee"
                                class="form-control @error('transaction_fee') is-invalid @enderror"
                                placeholder="{{ __('Transaction Fee') }}"
                                value="{{ old('transaction_fee', $item->transaction_fee ?? '') }}"
                            />
                            @error('transaction_fee')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Withdrawal Fee') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                name="withdrawal_fee"
                                class="form-control @error('withdrawal_fee') is-invalid @enderror"
                                placeholder="{{ __('Withdrawal Fee') }}"
                                value="{{ old('withdrawal_fee', $item->withdrawal_fee ?? '') }}"
                            />
                            @error('withdrawal_fee')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Settlement Fee') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                name="settlement_fee"
                                class="form-control @error('settlement_fee') is-invalid @enderror"
                                placeholder="{{ __('Settlement Fee') }}"
                                value="{{ old('settlement_fee', $item->settlement_fee ?? '') }}"
                            />
                            @error('settlement_fee')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Status') }}:</label>
                        <div class="col-lg-9">
                            <div class="form-check form-switch">
                                <input type="hidden" name="status" value="0">
                                <input
                                    class="form-check-input @error('status') is-invalid @enderror"
                                    type="checkbox"
                                    name="status"
                                    id="status"
                                    value="1"
                                    {{ old('status', isset($item) ? ($item->status ? 1 : 0) : 0) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="status">
                                    {{ __('Active') }}
                                </label>
                            </div>
                            <div class="form-text">{{ __('Enable or disable this site') }}</div>
                            @error('status')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="text-end">
                        <a href="{{ route('admin.sites.index') }}" class="btn btn-light">
                            <i class="ph-arrow-left me-2"></i>
                            {{ __('Back to List') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ph-floppy-disk me-2"></i>
                            {{ __('Submit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
