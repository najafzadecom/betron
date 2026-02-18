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
                        <label class="col-lg-3 col-form-label">{{ __('Type') }}:</label>
                        <div class="col-lg-9">
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required onchange="toggleFields()">
                                <option value="">{{ __('Select Type') }}</option>
                                <option value="user_id" {{ old('type', $item->type ?? '') == 'user_id' ? 'selected' : '' }}>{{ __('User ID') }}</option>
                                <option value="ip_address" {{ old('type', $item->type ?? '') == 'ip_address' ? 'selected' : '' }}>{{ __('IP Address') }}</option>
                            </select>
                            <div class="form-text">{{ __('Choose whether to blacklist by User ID or IP Address') }}</div>
                            @error('type')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3" id="user_id_field" style="display: none;">
                        <label class="col-lg-3 col-form-label">{{ __('User ID') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="number"
                                name="user_id"
                                class="form-control @error('user_id') is-invalid @enderror"
                                placeholder="{{ __('User ID') }}"
                                value="{{ old('user_id', $item->user_id ?? '') }}"
                                min="1"
                            />
                            <div class="form-text">{{ __('Enter the user ID to be blacklisted') }}</div>
                            @error('user_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3" id="ip_address_field" style="display: none;">
                        <label class="col-lg-3 col-form-label">{{ __('IP Address') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="text"
                                name="ip_address"
                                class="form-control @error('ip_address') is-invalid @enderror"
                                placeholder="{{ __('IP Address (e.g., 192.168.1.1)') }}"
                                value="{{ old('ip_address', $item->ip_address ?? '') }}"
                                pattern="^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$"
                            />
                            <div class="form-text">{{ __('Enter a valid IP address to be blacklisted') }}</div>
                            @error('ip_address')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Site ID') }}:</label>
                        <div class="col-lg-9">
                            <select
                                id="site_select"
                                name="site_id"
                                class="form-control @error('site_id') is-invalid @enderror"
                                data-placeholder="{{ __('Select site') }}"
                            >
                                <option value="">{{ __('Select site') }}</option>
                                @forelse($sites ?? [] as $site)
                                    <option
                                        @selected(old('site_id', $item->site_id ?? '') == $site->id)
                                        value="{{ $site->id }}">
                                        {{ $site->name }}
                                    </option>
                                @empty
                                    <option value="">{{ __('No sites found') }}</option>
                                @endforelse
                            </select>
                            @error('site_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Reason') }}:</label>
                        <div class="col-lg-9">
                            <textarea
                                name="reason"
                                class="form-control @error('reason') is-invalid @enderror"
                                placeholder="{{ __('Reason for blacklisting (optional)') }}"
                                rows="3"
                                maxlength="255"
                            >{{ old('reason', $item->reason ?? '') }}</textarea>
                            <div class="form-text">{{ __('Optional reason for adding to blacklist (max 255 characters)') }}</div>
                            @error('reason')
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
                                <input type="hidden" name="is_active" value="0">
                                <input
                                    class="form-check-input @error('is_active') is-invalid @enderror"
                                    type="checkbox"
                                    name="is_active"
                                    id="is_active"
                                    value="1"
                                    {{ old('is_active', isset($item) ? ($item->is_active ? 1 : 0) : 1) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="is_active">
                                    {{ __('Active') }}
                                </label>
                            </div>
                            <div class="form-text">{{ __('Enable or disable this blacklist entry') }}</div>
                            @error('is_active')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="text-end">
                        <a href="{{ route('admin.blacklists.index') }}" class="btn btn-light">
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

@push('scripts')
    <script>
        function toggleFields() {
            const typeSelect = document.getElementById('type');
            const userIdField = document.getElementById('user_id_field');
            const ipAddressField = document.getElementById('ip_address_field');
            const userIdInput = document.querySelector('input[name="user_id"]');
            const ipAddressInput = document.querySelector('input[name="ip_address"]');

            // Hide both fields initially
            userIdField.style.display = 'none';
            ipAddressField.style.display = 'none';

            // Remove required attributes
            userIdInput.removeAttribute('required');
            ipAddressInput.removeAttribute('required');

            // Show and require based on selection
            if (typeSelect.value === 'user_id') {
                userIdField.style.display = 'flex';
                userIdInput.setAttribute('required', 'required');
                // Clear IP address field
                ipAddressInput.value = '';
            } else if (typeSelect.value === 'ip_address') {
                ipAddressField.style.display = 'flex';
                ipAddressInput.setAttribute('required', 'required');
                // Clear user ID field
                userIdInput.value = '';
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleFields();
        });
    </script>
@endpush
