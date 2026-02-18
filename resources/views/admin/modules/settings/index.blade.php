@extends('admin.layouts.app')
@section('title', $title)
@section('content')
    <div class="content">
        <div class="card">
            <div class="card-header d-flex">
                <h5 class="mb-0">{{ $module }}</h5>
                <div class="ms-auto">
                    <button type="button" class="btn btn-primary" id="saveAllSettings">
                        <i class="ph-floppy-disk me-2"></i>{{ __('Save All Settings') }}
                    </button>
                </div>
            </div>

            <div class="card-body">
                <form id="settingsForm" action="{{ route('admin.settings.bulk-update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs nav-tabs-highlight nav-justified" role="tablist">
                        @foreach($groups as $groupKey => $groupName)
                            @if(isset($settingsByGroup[$groupKey]) && count($settingsByGroup[$groupKey]) > 0)
                                <li class="nav-item" role="presentation">
                                    <a href="#tab-{{ $groupKey }}"
                                       class="nav-link {{ $loop->first ? 'active' : '' }}"
                                       data-bs-toggle="tab"
                                       role="tab">
                                        <i class="ph-{{ $groupIcons[$groupKey] ?? 'gear' }} me-2"></i>
                                        {{ $groupName }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content">
                        @foreach($groups as $groupKey => $groupName)
                            @if(isset($settingsByGroup[$groupKey]) && count($settingsByGroup[$groupKey]) > 0)
                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                     id="tab-{{ $groupKey }}"
                                     role="tabpanel">

                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <div class="card border-0 shadow-sm">
                                                <div class="card-header">
                                                    <h6 class="mb-0">
                                                        <i class="ph-{{ $groupIcons[$groupKey] ?? 'gear' }} me-2"></i>
                                                        {{ $groupName }} {{ __('Settings') }}
                                                    </h6>
                                                    <p class="text-muted small mb-0 mt-1">
                                                        {{ $groupDescriptions[$groupKey] ?? __('Configure settings for this group') }}
                                                    </p>
                                                </div>

                                                <div class="card-body">
                                                    <div class="row">
                                                        @foreach($settingsByGroup[$groupKey] as $setting)
                                                            <div class="col-lg-6 mb-4">
                                                                <div class="setting-item p-3 border rounded bg-white">
                                                                    <label class="form-label fw-semibold">
                                                                        {{ $setting->name }}
                                                                        @if(in_array($setting->type, ['text', 'email', 'number']))
                                                                            <span class="text-danger">*</span>
                                                                        @endif
                                                                    </label>

                                                                    @if($setting->description ?? false)
                                                                        <p class="text-muted small mb-2">{{ $setting->description }}</p>
                                                                    @endif

                                                                    <div class="setting-input">
                                                                        @switch($setting->type)
                                                                            @case('text')
                                                                            @case('email')
                                                                            @case('url')
                                                                                <input type="{{ $setting->type }}"
                                                                                       name="settings[{{ $setting->key }}]"
                                                                                       class="form-control @error('settings.'.$setting->key) is-invalid @enderror"
                                                                                       value="{{ old('settings.'.$setting->key, $setting->value) }}"
                                                                                       placeholder="{{ __('Enter') }} {{ strtolower($setting->name) }}">
                                                                                @break

                                                                            @case('number')
                                                                                <input type="number"
                                                                                       name="settings[{{ $setting->key }}]"
                                                                                       class="form-control @error('settings.'.$setting->key) is-invalid @enderror"
                                                                                       value="{{ old('settings.'.$setting->key, $setting->value) }}"
                                                                                       placeholder="{{ __('Enter') }} {{ strtolower($setting->name) }}">
                                                                                @break

                                                                            @case('textarea')
                                                                                <textarea name="settings[{{ $setting->key }}]"
                                                                                          class="form-control @error('settings.'.$setting->key) is-invalid @enderror"
                                                                                          rows="3"
                                                                                          placeholder="{{ __('Enter') }} {{ strtolower($setting->name) }}">{{ old('settings.'.$setting->key, $setting->value) }}</textarea>
                                                                                @break

                                                                            @case('boolean')
                                                                                <div class="form-check form-switch">
                                                                                    <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                                                                    <input type="checkbox"
                                                                                           name="settings[{{ $setting->key }}]"
                                                                                           value="1"
                                                                                           class="form-check-input"
                                                                                           id="setting_{{ $setting->key }}"
                                                                                           {{ old('settings.'.$setting->key, $setting->value) ? 'checked' : '' }}>
                                                                                    <label class="form-check-label" for="setting_{{ $setting->key }}">
                                                                                        {{ __('Enable') }} {{ strtolower($setting->name) }}
                                                                                    </label>
                                                                                </div>
                                                                                @break

                                                                            @case('select')
                                                                                @php
                                                                                    $options = is_array($setting->options) ? $setting->options : (is_string($setting->options) ? explode("\n", trim($setting->options)) : []);
                                                                                @endphp
                                                                                <select name="settings[{{ $setting->key }}]"
                                                                                        class="form-control @error('settings.'.$setting->key) is-invalid @enderror">
                                                                                    <option value="">{{ __('Select') }} {{ strtolower($setting->name) }}</option>
                                                                                    @foreach($options as $option)
                                                                                        @if(is_string($option))
                                                                                            <option value="{{ trim($option) }}"
                                                                                                    {{ old('settings.'.$setting->key, $setting->value) == trim($option) ? 'selected' : '' }}>
                                                                                                {{ trim($option) }}
                                                                                            </option>
                                                                                        @else
                                                                                            <option value="{{ $option }}"
                                                                                                    {{ old('settings.'.$setting->key, $setting->value) == $option ? 'selected' : '' }}>
                                                                                                {{ $option }}
                                                                                            </option>
                                                                                        @endif
                                                                                    @endforeach
                                                                                </select>
                                                                                @break

                                                                            @case('file')
                                                                                <input type="file"
                                                                                       name="settings[{{ $setting->key }}]"
                                                                                       class="form-control @error('settings.'.$setting->key) is-invalid @enderror"
                                                                                       accept="image/*,application/pdf,.doc,.docx">
                                                                                @if($setting->value)
                                                                                    <div class="mt-2">
                                                                                        <small class="text-muted">
                                                                                            {{ __('Current file') }}:
                                                                                            <a href="{{ Storage::url($setting->value) }}" target="_blank" class="text-primary">
                                                                                                {{ basename($setting->value) }}
                                                                                            </a>
                                                                                        </small>
                                                                                    </div>
                                                                                @endif
                                                                                @break

                                                                            @case('json')
                                                                                <textarea name="settings[{{ $setting->key }}]"
                                                                                          class="form-control @error('settings.'.$setting->key) is-invalid @enderror"
                                                                                          rows="4"
                                                                                          placeholder="{{ __('Enter valid JSON') }}">{{ old('settings.'.$setting->key, is_array($setting->value) ? json_encode($setting->value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $setting->value) }}</textarea>
                                                                                <div class="form-text">{{ __('Enter valid JSON format') }}</div>
                                                                                @break

                                                                            @default
                                                                                <input type="text"
                                                                                       name="settings[{{ $setting->key }}]"
                                                                                       class="form-control @error('settings.'.$setting->key) is-invalid @enderror"
                                                                                       value="{{ old('settings.'.$setting->key, $setting->value) }}"
                                                                                       placeholder="{{ __('Enter') }} {{ strtolower($setting->name) }}">
                                                                        @endswitch

                                                                        @error('settings.'.$setting->key)
                                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>

                                                                    <div class="setting-meta mt-2">
                                                                        <small class="text-muted">
                                                                            <i class="ph-key me-1"></i>{{ $setting->key }}
                                                                            <span class="ms-2">
                                                                                <i class="ph-tag me-1"></i>{{ ucfirst($setting->type) }}
                                                                            </span>
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .nav-tabs-highlight .nav-link {
            border: 1px solid transparent;
            border-radius: 0.375rem 0.375rem 0 0;
            margin-bottom: -1px;
            transition: all 0.15s ease-in-out;
        }

        .nav-tabs-highlight .nav-link:hover {
            background-color: #f8f9fa;
            border-color: #e9ecef #e9ecef #dee2e6;
        }

        .nav-tabs-highlight .nav-link.active {
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
            color: #495057;
        }

        .setting-item {
            transition: all 0.2s ease-in-out;
            border: 1px solid #e9ecef !important;
        }

        .setting-item:hover {
            border-color: #007bff !important;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 123, 255, 0.075);
        }

        .setting-meta {
            border-top: 1px solid #f8f9fa;
            padding-top: 0.5rem;
        }

        .tab-content {
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 0.375rem 0.375rem;
        }

        .form-check-input:checked {
            background-color: #198754;
            border-color: #198754;
        }

        .badge {
            font-size: 0.75em;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const saveAllButton = document.getElementById('saveAllSettings');
            const settingsForm = document.getElementById('settingsForm');

            // Save all settings
            if (saveAllButton && settingsForm) {
                saveAllButton.addEventListener('click', function() {
                    // Validate JSON fields before submission
                    const jsonTextareas = settingsForm.querySelectorAll('textarea[name*="settings"]:not([name*="textarea"])');
                    let hasErrors = false;

                    jsonTextareas.forEach(function(textarea) {
                        const settingKey = textarea.name.match(/settings\[(.*?)\]/)[1];
                        const settingType = textarea.closest('.setting-item').querySelector('.setting-meta').textContent;

                        if (settingType.includes('Json') && textarea.value.trim()) {
                            try {
                                JSON.parse(textarea.value);
                                textarea.classList.remove('is-invalid');
                            } catch (error) {
                                textarea.classList.add('is-invalid');
                                hasErrors = true;
                            }
                        }
                    });

                    if (hasErrors) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('{{ __("Error!") }}', '{{ __("Please fix JSON format errors") }}', 'error');
                        } else {
                            alert('{{ __("Please fix JSON format errors") }}');
                        }
                        return;
                    }

                    // Show loading state
                    saveAllButton.disabled = true;
                    saveAllButton.innerHTML = '<i class="ph-spinner ph-spin me-2"></i>{{ __("Saving...") }}';

                    // Submit form
                    settingsForm.submit();
                });
            }

            // Auto-save on input change (optional)
            const settingInputs = settingsForm.querySelectorAll('input, textarea, select');
            settingInputs.forEach(function(input) {
                input.addEventListener('change', function() {
                    // Add visual feedback for changed settings
                    const settingItem = this.closest('.setting-item');
                    if (settingItem) {
                        settingItem.style.borderColor = '#ffc107';
                        settingItem.style.backgroundColor = '#fff8e1';
                    }
                });
            });

            // Tab change handler
            const tabLinks = document.querySelectorAll('[data-bs-toggle="tab"]');
            tabLinks.forEach(function(tabLink) {
                tabLink.addEventListener('shown.bs.tab', function(event) {
                    // Optional: Save current tab state to localStorage
                    localStorage.setItem('activeSettingsTab', event.target.getAttribute('href'));
                });
            });

            // Restore last active tab
            const lastActiveTab = localStorage.getItem('activeSettingsTab');
            if (lastActiveTab) {
                const tabElement = document.querySelector(`[href="${lastActiveTab}"]`);
                if (tabElement) {
                    const tab = new bootstrap.Tab(tabElement);
                    tab.show();
                }
            }
        });
    </script>
@endpush
