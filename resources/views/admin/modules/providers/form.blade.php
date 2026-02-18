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
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   placeholder="{{ __('Name') }}"
                                   value="{{ old('name', $item->name ?? '') }}"/>
                            @error('name')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Code') }}:</label>
                        <div class="col-lg-9">
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                   placeholder="{{ __('Code') }}"
                                   value="{{ old('code', $item->code ?? '') }}"/>
                            @error('code')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Status') }}:</label>
                        <div class="col-lg-9">
                            <select name="status" class="form-control">
                                <option
                                    @selected(old('status', $item->status ?? 0) == 1) value="1">{{ __('Active') }}</option>
                                <option
                                    @selected(old('status', $item->status ?? 0) == 0 ) value="0">{{ __('Deactive') }}</option>
                            </select>
                            @error('status')<span
                                class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Type') }}:</label>
                        <div class="col-lg-9">
                            <select name="type" id="provider-type" class="form-control">
                                <option value="">{{ __('Select Type') }}</option>
                                @foreach($types as $type)
                                    <option
                                        value="{{ $type->value }}" @selected(old('type', $item->type->value ?? '') == $type->value)>{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>


                    @php
                        $type = old('type', $item->type->value ?? '');
                        $credentialsKeys = $type ? config("payment.".strtolower($type).".required_credentials", []) : [];
                    @endphp


                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Credentials') }}:</label>
                        <div class="col-lg-9">
                            <div id="credentials-wrapper">
                                @foreach($credentialsKeys as $key)
                                    <div class="row mb-2 credential-row">
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control" value="{{ $key }}" readonly>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" name="credentials[{{ $key }}]" class="form-control"
                                                   value="{{ old("credentials.$key", $item->credentials[$key] ?? '') }}">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-danger" onclick="history.back()">
                            <i class="ph-arrow-left me-2"></i> {{ __('Back') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ __('Submit') }} <i class="ph-paper-plane-tilt ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@php
    $paymentConfig = [];
    foreach ($types as $type) {
        $paymentConfig[$type->value] = config("payment.".strtolower($type->value).".required_credentials", []);
    }
@endphp

@push('scripts')
    <script>
        const providerTypeSelect = document.getElementById('provider-type');
        const credentialsWrapper = document.getElementById('credentials-wrapper');

        const paymentConfig = @json($paymentConfig);

        function renderCredentials(type) {
            credentialsWrapper.innerHTML = '';
            const keys = paymentConfig[type] || [];
            keys.forEach(key => {
                const div = document.createElement('div');
                div.classList.add('row', 'mb-2', 'credential-row');
                div.innerHTML = `
            <div class="col-lg-6">
                <input type="text" class="form-control" value="${key}" readonly>
            </div>
            <div class="col-lg-6">
                <input type="text" name="credentials[${key}]" class="form-control">
            </div>
        `;
                credentialsWrapper.appendChild(div);
            });
        }

        @empty($item)
        renderCredentials(providerTypeSelect.value);
        @endempty

        providerTypeSelect.addEventListener('change', function () {
            renderCredentials(this.value);
        });
    </script>
@endpush
