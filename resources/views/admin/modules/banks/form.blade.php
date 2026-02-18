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
                        <label class="col-lg-3 col-form-label">{{ __('Bank Name') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="text"
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="{{ __('Bank Name') }}"
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
                        <label class="col-lg-3 col-form-label">{{ __('Bank Logo') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="file"
                                name="image"
                                class="form-control @error('image') is-invalid @enderror"
                                accept="image/*"
                            />
                            <div class="form-text">{{ __('Bank logo image (JPEG, PNG, GIF, WebP, max 2MB)') }}</div>

                            @if(isset($item) && $item->image_url)
                                <div class="mt-2">
                                    <label class="form-label">{{ __('Current Image') }}:</label><br>
                                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
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
                        <label class="col-lg-3 col-form-label">{{ __('Priority') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="number"
                                name="priority"
                                class="form-control @error('priority') is-invalid @enderror"
                                placeholder="{{ __('Priority') }}"
                                value="{{ old('priority', $item->priority ?? 0) }}"
                                min="0"
                                max="255"
                                required
                            />
                            <div class="form-text">{{ __('Sort order priority (0-255)') }}</div>
                            @error('priority')
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
                            <div class="form-text">{{ __('Enable or disable this bank') }}</div>
                            @error('status')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Transaction Status') }}:</label>
                        <div class="col-lg-9">
                            <div class="form-check form-switch">
                                <input type="hidden" name="transaction_status" value="0">
                                <input
                                    class="form-check-input @error('transaction_status') is-invalid @enderror"
                                    type="checkbox"
                                    name="transaction_status"
                                    id="transaction_status"
                                    value="1"
                                    {{ old('transaction_status', isset($item) ? ($item->transaction_status ? 1 : 0) : 0) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="transaction_status">
                                    {{ __('Active') }}
                                </label>
                            </div>
                            <div class="form-text">{{ __('Enable or disable this bank for transaction') }}</div>
                            @error('transaction_status')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Withdrawal Status') }}:</label>
                        <div class="col-lg-9">
                            <div class="form-check form-switch">
                                <input type="hidden" name="withdrawal_status" value="0">
                                <input
                                    class="form-check-input @error('withdrawal_status') is-invalid @enderror"
                                    type="checkbox"
                                    name="withdrawal_status"
                                    id="withdrawal_status"
                                    value="1"
                                    {{ old('withdrawal_status', isset($item) ? ($item->withdrawal_status ? 1 : 0) : 0) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="withdrawal_status">
                                    {{ __('Active') }}
                                </label>
                            </div>
                            <div class="form-text">{{ __('Enable or disable this bank for withdrawal') }}</div>
                            @error('withdrawal_status')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="text-end">
                        <a href="{{ route('admin.banks.index') }}" class="btn btn-light">
                            <i class="ph-arrow-left me-2"></i>
                            {{ __('Back to List') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ph-floppy-disk me-2"></i>
                            {{ __('Save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
