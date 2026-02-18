@extends('vendor.layouts.app')
@section('title', $title)

@push('scripts')
    <script src="{{ asset('admin/assets/js/vendor/uploaders/fileinput/fileinput.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/vendor/uploaders/fileinput/plugins/sortable.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#bank_select').select2({
                placeholder: "{{ __('Select bank') }}",
                allowClear: true,
                width: '100%'
            });

            $('#transaction_bank_select').select2({
                placeholder: "{{ __('Select bank') }}",
                width: '100%'
            });

            $('#manager_select').select2({
                placeholder: "{{ __('Select managers') }}",
                width: '100%'
            });

            // Select All Transaction Banks functionality
            $('#select_all_transaction_banks').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#transaction_bank_select option').prop('selected', true);
                } else {
                    $('#transaction_bank_select option').prop('selected', false);
                }
                $('#transaction_bank_select').trigger('change');
            });

            // Update checkbox state when selection changes
            $('#transaction_bank_select').on('change', function() {
                const totalOptions = $('#transaction_bank_select option').length;
                const selectedOptions = $('#transaction_bank_select option:selected').length;
                $('#select_all_transaction_banks').prop('checked', totalOptions === selectedOptions && totalOptions > 0);
            });

            // Initialize checkbox state on page load
            const totalTransactionBankOptions = $('#transaction_bank_select option').length;
            const selectedTransactionBankOptions = $('#transaction_bank_select option:selected').length;
            $('#select_all_transaction_banks').prop('checked', totalTransactionBankOptions === selectedTransactionBankOptions && totalTransactionBankOptions > 0);

            // File uploader setup
            const uploadUrl = $('#wallet-files-input').data('upload-url');
            const isEditMode = uploadUrl !== undefined;

            @if(isset($item) && $item->id)
                const deleteUrlTemplate = '{{ route('vendor.wallets.files.delete', ['wallet' => $item->id, 'file' => '__FILE_ID__']) }}';
            @else
                const deleteUrlTemplate = null;
            @endif

            const initialPreview = [];
            const initialPreviewConfig = [];

            @if(isset($item) && $item->id && isset($files) && $files->count() > 0)
                @foreach($files as $file)
                    initialPreview.push("{{ Storage::url($file->file_path) }}");
                    initialPreviewConfig.push({
                        caption: "{{ $file->original_name }}",
                        size: {{ $file->file_size ?? 0 }},
                        key: {{ $file->id }},
                        url: deleteUrlTemplate.replace('__FILE_ID__', {{ $file->id }}),
                        downloadUrl: "{{ Storage::url($file->file_path) }}",
                        download: true
                    });
                @endforeach
            @endif

            const fileInputConfig = {
                maxFileCount: 20,
                dropZoneTitle: '{{ __('Drag & drop files here...') }}',
                removeTitle: '{{ __('Remove file') }}',
                browseLabel: '{{ __('Browse') }}',
                browseIcon: '<i class="ph-file-plus me-2"></i>',
                uploadIcon: '<i class="ph-file-arrow-up me-2"></i>',
                removeIcon: '<i class="ph-x fs-base me-2"></i>',
                uploadClass: 'btn btn-light',
                removeClass: 'btn btn-light',
                showRemove: false,
                showBrowse: true,
                showCaption: false,
                showPreview: true,
                showDownload: true,
                initialPreview: initialPreview,
                initialPreviewConfig: initialPreviewConfig,
                initialPreviewAsData: isEditMode,
                overwriteInitial: false,
                showUploadedThumbs: true,
                showRotate: false,
                showUpload: false,
                autoReplace: false,
            };

            if (isEditMode) {
                fileInputConfig.uploadUrl = uploadUrl;
                fileInputConfig.uploadAsync = true;
                fileInputConfig.showUpload = false;
                fileInputConfig.autoReplace = false;
                fileInputConfig.uploadExtraData = {
                    _token: $('meta[name="csrf-token"]').attr('content')
                };
                fileInputConfig.deleteExtraData = {
                    _method: "DELETE",
                    _token: $('meta[name="csrf-token"]').attr('content')
                };
            }

            $('#wallet-files-input').fileinput(fileInputConfig);

            if (isEditMode) {
                $('#wallet-files-input').on('filebatchselected', function(event, files) {
                    $(this).fileinput('upload');
                });
            }
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

                <form action="{{ $action }}" method="POST" enctype="multipart/form-data">
                    @method($method)
                    @csrf
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Account Name') }}:</label>
                        <div class="col-lg-9">
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   placeholder="{{ __('Account Name') }}" value="{{ old('name', $item->name ?? '') }}"/>
                            @error('name')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('IBAN') }}:</label>
                        <div class="col-lg-9">
                            <input type="text" name="iban" class="form-control @error('iban') is-invalid @enderror"
                                   placeholder="{{ __('IBAN') }}" value="{{ old('iban', $item->iban ?? '') }}"/>
                            @error('iban')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Bank') }}:</label>
                        <div class="col-lg-9">
                            <select id="bank_select" name="bank_id" class="form-control @error('bank_id') is-invalid @enderror">
                                <option value="">{{ __('Select bank') }}</option>
                                @forelse($banks ?? [] as $bank)
                                    <option @selected(old('bank_id', $item->bank_id ?? '') == $bank->id) value="{{ $bank->id }}">
                                        {{ $bank->name }}
                                    </option>
                                @empty
                                    <option value="">{{ __('No banks found') }}</option>
                                @endforelse
                            </select>
                            @error('bank_id')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Phone number') }}:</label>
                        <div class="col-lg-9">
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                   placeholder="{{ __('Phone') }}" value="{{ old('phone', $item->phone ?? '') }}"/>
                            @error('phone')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Mobile Banking Password') }}:</label>
                        <div class="col-lg-9">
                            <input type="text" name="mobile_banking_password" class="form-control @error('mobile_banking_password') is-invalid @enderror"
                                   placeholder="{{ __('Mobile Banking Password') }}" value="{{ old('mobile_banking_password', $item->mobile_banking_password ?? '') }}"/>
                            @error('mobile_banking_password')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Linked Card') }}:</label>
                        <div class="col-lg-9">
                            <select name="linked_card" class="form-control @error('linked_card') is-invalid @enderror">
                                <option value="0" @selected(old('linked_card', $item->linked_card ?? 0) == 0)>{{ __('No') }}</option>
                                <option value="1" @selected(old('linked_card', $item->linked_card ?? '') == 1)>{{ __('Yes') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Transaction Banks') }}:</label>
                        <div class="col-lg-9">
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="select_all_transaction_banks">
                                <label class="form-check-label" for="select_all_transaction_banks">{{ __('Select All') }}</label>
                            </div>
                            <select multiple="multiple" id="transaction_bank_select" name="transaction_banks[]" class="form-control">
                                @forelse($banks ?? [] as $bank)
                                    <option @selected(in_array($bank->id, old('transaction_banks', isset($item) ? $item?->transactionBanks?->pluck('id')->toArray() : []))) value="{{ $bank->id }}">
                                        {{ $bank->name }}
                                    </option>
                                @empty
                                @endforelse
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Maximum Amount') }}:</label>
                        <div class="col-lg-9">
                            <input type="number" step="0.01" name="maximum_amount" class="form-control"
                                   value="{{ old('maximum_amount', $item->maximum_amount ?? '') }}"/>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Single Deposit Min Amount') }}:</label>
                        <div class="col-lg-9">
                            <input type="number" step="0.01" name="single_deposit_min_amount" class="form-control"
                                   value="{{ old('single_deposit_min_amount', $item->single_deposit_min_amount ?? '') }}"/>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Single Deposit Max Amount') }}:</label>
                        <div class="col-lg-9">
                            <input type="number" step="0.01" name="single_deposit_max_amount" class="form-control"
                                   value="{{ old('single_deposit_max_amount', $item->single_deposit_max_amount ?? '') }}"/>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Currency') }}:</label>
                        <div class="col-lg-9">
                            <select name="currency" class="form-control">
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->value }}" @selected(old('currency', ($item?->currency?->value ?? $currencies[0]->value)) == $currency->value)>
                                        {{ $currency->name() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Status') }}:</label>
                        <div class="col-lg-9">
                            @php
                                $currentStatus = old('status', ($item?->status instanceof \App\Enums\WalletStatus ? $item->status->value : ($item?->status ?? \App\Enums\WalletStatus::Inactive->value)));
                            @endphp
                            <select name="status" class="form-control">
                                @foreach($wallet_statuses as $status)
                                    <option value="{{ $status->value }}" @selected($currentStatus == $status->value)>{{ __($status->label()) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Wallet Managers') }}:</label>
                        <div class="col-lg-9">
                            <select multiple="multiple" id="manager_select" name="manager_ids[]" class="form-control">
                                @forelse($vendorUsers ?? [] as $vendorUser)
                                    <option @selected(in_array($vendorUser->id, old('manager_ids', isset($item) ? $item?->managers?->pluck('id')->toArray() : []))) value="{{ $vendorUser->id }}">
                                        {{ $vendorUser->name }} ({{ $vendorUser->email }})
                                    </option>
                                @empty
                                    <option value="">{{ __('No vendor users found') }}</option>
                                @endforelse
                            </select>
                            <small class="form-text text-muted">{{ __('Select vendor users who can manage this wallet') }}</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Description') }}:</label>
                        <div class="col-lg-9">
                            <textarea name="description" rows="4" class="form-control">{{ old('description', $item->description ?? '') }}</textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Files') }}:</label>
                        <div class="col-lg-9">
                            <input id="wallet-files-input" name="file" type="file" class="file-input" multiple
                                @if(isset($item) && $item->id)
                                    data-upload-url="{{ route('vendor.wallets.files.upload', $item->id) }}"
                                @endif
                            >
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
