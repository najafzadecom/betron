@extends('admin.layouts.app')
@section('title', $title)

@push('scripts')
    <script src="{{ asset('admin/assets/js/vendor/uploaders/fileinput/fileinput.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/vendor/uploaders/fileinput/plugins/sortable.min.js') }}"></script>
    <script>
        $(document).ready(function () {
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

            $('#withdrawal_bank_select').select2({
                placeholder: "{{ __('Select bank') }}",
                width: '100%'
            });

            $('#user_select').select2({
                placeholder: "{{ __('Select user') }}",
                width: '100%'
            });

            $('#vendor_select').select2({
                placeholder: "{{ __('Select vendor') }}",
                allowClear: true,
                width: '100%'
            });

            $('#vendor_user_creator_select').select2({
                placeholder: "{{ __('Select creator') }}",
                allowClear: true,
                width: '100%'
            });

            $('#vendor_user_manager_select').select2({
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

            // Initialize file uploader
            const uploadUrl = $('#wallet-files-input').data('upload-url');
            const isEditMode = uploadUrl !== undefined;

            // Delete URL template (only for edit mode)
            @if(isset($item) && $item->id)
            const deleteUrlTemplate = '{{ route('admin.wallets.files.delete', ['wallet' => $item->id, 'file' => '__FILE_ID__']) }}';
            @else
            const deleteUrlTemplate = null;
            @endif

            // Prepare initial preview data (only for edit mode)
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

            // File input configuration
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
                fileActionSettings: {
                    removeIcon: '<i class="ph-trash"></i>',
                    removeClass: '',
                    zoomIcon: '<i class="ph-magnifying-glass-plus"></i>',
                    zoomClass: '',
                    downloadIcon: '<i class="ph-download"></i>',
                    downloadClass: '',
                    indicatorNew: '<i class="ph-file-plus text-success"></i>',
                    indicatorSuccess: '<i class="ph-check file-icon-large text-success"></i>',
                    indicatorError: '<i class="ph-x text-danger"></i>',
                    indicatorLoading: '<i class="ph-spinner spinner text-muted"></i>',
                },
                layoutTemplates: {
                    icon: '<i class="ph-check"></i>'
                },
                previewZoomButtonClasses: {
                    rotate: 'btn btn-light btn-icon btn-sm',
                    toggleheader: 'btn btn-light btn-icon btn-header-toggle btn-sm',
                    fullscreen: 'btn btn-light btn-icon btn-sm',
                    borderless: 'btn btn-light btn-icon btn-sm',
                    close: 'btn btn-light btn-icon btn-sm'
                },
                previewZoomButtonIcons: {
                    prev: '<i class="ph-arrow-left"></i>',
                    next: '<i class="ph-arrow-right"></i>',
                    rotate: '<i class="ph-arrow-clockwise"></i>',
                    toggleheader: '<i class="ph-arrows-down-up"></i>',
                    fullscreen: '<i class="ph-corners-out"></i>',
                    borderless: '<i class="ph-frame-corners"></i>',
                    close: '<i class="ph-x"></i>'
                }
            };

            if (isEditMode) {
                fileInputConfig.uploadUrl = uploadUrl;
                fileInputConfig.uploadAsync = true; // Auto-upload when file is selected
                fileInputConfig.showUpload = false; // Hide upload button - files auto-upload
                fileInputConfig.autoReplace = false; // Allow multiple files to accumulate
                fileInputConfig.uploadExtraData = {
                    _token: $('meta[name="csrf-token"]').attr('content')
                };
                fileInputConfig.deleteExtraData = {
                    _method: "DELETE",
                    _token: $('meta[name="csrf-token"]').attr('content')
                };
            } else {
                // For create mode, keep autoReplace false to accumulate files
                fileInputConfig.autoReplace = false;
            }

            $('#wallet-files-input').fileinput(fileInputConfig);

            // For create mode, ensure files are included in form submit
            if (!isEditMode) {
                $('form').on('submit', function (e) {
                    const fileInput = $('#wallet-files-input');
                    const files = fileInput.fileinput('getFilesCount');
                    if (files > 0) {
                        fileInput.prop('disabled', false);
                    }
                });
            }

            if (isEditMode) {
                // Manually trigger upload when files are selected
                $('#wallet-files-input').on('filebatchselected', function (event, files) {
                    const fileInput = $(this);
                    // Small delay to ensure files are processed
                    setTimeout(function () {
                        fileInput.fileinput('upload');
                    }, 200);
                }).on('fileuploaded', function (event, data, previewId, index) {
                    // Handle successful upload
                    if (data.response && data.response.file) {
                        const fileId = data.response.file.id;
                        const fileUrl = data.response.file.url;
                        const deleteUrl = deleteUrlTemplate.replace('__FILE_ID__', fileId);

                        const fileInput = $('#wallet-files-input');
                        const config = fileInput.fileinput('getPreview');
                        if (config && config[index]) {
                            config[index].url = deleteUrl;
                            config[index].key = fileId;
                            config[index].downloadUrl = fileUrl;
                            config[index].download = true;
                        }
                    }
                }).on('fileuploaderror', function (event, data, msg) {
                    console.error('Upload error:', msg);
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
                            <input
                                type="text"
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="{{ __('Account Name') }}"
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
                        <label class="col-lg-3 col-form-label">{{ __('IBAN') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="text"
                                name="iban"
                                class="form-control @error('iban') is-invalid @enderror"
                                placeholder="{{ __('IBAN') }}"
                                value="{{ old('iban', $item->iban ?? '') }}"
                            />
                            @error('iban')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Bank') }}:</label>
                        <div class="col-lg-9">
                            <select
                                id="bank_select"
                                name="bank_id"
                                class="form-control @error('bank_id') is-invalid @enderror"
                                data-placeholder="{{ __('Select bank') }}"
                            >
                                <option value="">{{ __('Select bank') }}</option>
                                @forelse($banks ?? [] as $bank)
                                    <option
                                        @selected(old('bank_id', $item->bank_id ?? '') == $bank->id)
                                        value="{{ $bank->id }}">
                                        {{ $bank->name }}
                                    </option>
                                @empty
                                    <option value="">{{ __('No banks found') }}</option>
                                @endforelse
                            </select>
                            @error('bank_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Vendor') }}:</label>
                        <div class="col-lg-9">
                            <select
                                id="vendor_select"
                                name="vendor_id"
                                class="form-control @error('vendor_id') is-invalid @enderror"
                                data-placeholder="{{ __('Select vendor') }}"
                            >
                                <option value="">{{ __('Select vendor (optional)') }}</option>
                                @forelse($vendors ?? [] as $vendor)
                                    <option
                                        @selected(old('vendor_id', $item->vendor_id ?? '') == $vendor->id)
                                        value="{{ $vendor->id }}">
                                        {{ $vendor->name }} ({{ $vendor->company_name ?? $vendor->email }})
                                    </option>
                                @empty
                                    <option value="">{{ __('No vendors found') }}</option>
                                @endforelse
                            </select>
                            @error('vendor_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Phone number') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="text"
                                name="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                placeholder="{{ __('Phone') }}"
                                value="{{ old('phone', $item->phone ?? '') }}"
                            />
                            @error('phone')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Mobile Banking Password') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="text"
                                name="mobile_banking_password"
                                class="form-control @error('mobile_banking_password') is-invalid @enderror"
                                placeholder="{{ __('Mobile Banking Password') }}"
                                value="{{ old('mobile_banking_password', $item->mobile_banking_password ?? '') }}"
                            />
                            @error('mobile_banking_password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Linked Card') }}:</label>
                        <div class="col-lg-9">
                            <select
                                name="linked_card"
                                class="form-control @error('linked_card') is-invalid @enderror"
                            >
                                <option
                                    value="0" @selected(old('linked_card', $item->linked_card ?? 0) == 0)>{{ __('No') }}</option>
                                <option
                                    value="1" @selected(old('linked_card', $item->linked_card ?? '') == 1)>{{ __('Yes') }}</option>
                            </select>
                            @error('linked_card')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>


                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Employee') }}:</label>
                        <div class="col-lg-9">
                            <select
                                multiple="multiple"
                                id="user_select"
                                name="user_ids[]"
                                class="form-control @error('user_ids') is-invalid @enderror"
                                data-placeholder="{{ __('Select user') }}"
                            >
                                @forelse($users ?? [] as $user)
                                    <option
                                        @selected(in_array($user->id, old('user_ids', isset($item) ? $item?->users?->pluck('id')->toArray() : [])))
                                        value="{{ $user->id }}">
                                        {{ $user->name }}
                                    </option>
                                @empty
                                    <option value="">{{ __('No users found') }}</option>
                                @endforelse
                            </select>
                            @error('user_ids')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Wallet Creator (Vendor User)') }}:</label>
                        <div class="col-lg-9">
                            <select
                                id="vendor_user_creator_select"
                                name="created_by_vendor_user_id"
                                class="form-control @error('created_by_vendor_user_id') is-invalid @enderror"
                                data-placeholder="{{ __('Select creator') }}"
                            >
                                <option value="">{{ __('Select creator') }}</option>
                                @forelse($vendorUsers ?? [] as $vendorUser)
                                    <option
                                        @selected(old('created_by_vendor_user_id', $item->created_by_vendor_user_id ?? '') == $vendorUser->id)
                                        value="{{ $vendorUser->id }}">
                                        {{ $vendorUser->name }} ({{ $vendorUser->email }})
                                        - {{ $vendorUser->vendor->name ?? 'N/A' }}
                                    </option>
                                @empty
                                    <option value="">{{ __('No vendor users found') }}</option>
                                @endforelse
                            </select>
                            <small
                                class="form-text text-muted">{{ __('The vendor user who created this wallet') }}</small>
                            @error('created_by_vendor_user_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Wallet Managers (Vendor Users)') }}:</label>
                        <div class="col-lg-9">
                            <select
                                multiple="multiple"
                                id="vendor_user_manager_select"
                                name="manager_ids[]"
                                class="form-control @error('manager_ids') is-invalid @enderror"
                                data-placeholder="{{ __('Select managers') }}"
                            >
                                @forelse($vendorUsers ?? [] as $vendorUser)
                                    <option
                                        @selected(in_array($vendorUser->id, old('manager_ids', isset($item) ? $item?->managers?->pluck('id')->toArray() : [])))
                                        value="{{ $vendorUser->id }}">
                                        {{ $vendorUser->name }} ({{ $vendorUser->email }})
                                        - {{ $vendorUser->vendor->name ?? 'N/A' }}
                                    </option>
                                @empty
                                    <option value="">{{ __('No vendor users found') }}</option>
                                @endforelse
                            </select>
                            <small
                                class="form-text text-muted">{{ __('Select vendor users who can manage this wallet') }}</small>
                            @error('manager_ids')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Transaction Banks') }}:</label>
                        <div class="col-lg-9">
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="select_all_transaction_banks">
                                <label class="form-check-label" for="select_all_transaction_banks">{{ __('Select All') }}</label>
                            </div>
                            <select
                                multiple="multiple"
                                id="transaction_bank_select"
                                name="transaction_banks[]"
                                class="form-control @error('withdrawal_banks') is-invalid @enderror"
                                data-placeholder="{{ __('Select banks') }}"
                            >
                                @forelse($banks ?? [] as $bank)
                                    <option
                                        @selected(in_array($bank->id, old('transaction_banks', isset($item) ? $item?->transactionBanks?->pluck('id')->toArray() : [])))
                                        value="{{ $bank->id }}">
                                        {{ $bank->name }}
                                    </option>
                                @empty
                                    <option value="">{{ __('No banks found') }}</option>
                                @endforelse
                            </select>
                            @error('transaction_banks')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Maximum Amount') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="number"
                                step="0.01"
                                name="maximum_amount"
                                class="form-control @error('maximum_amount') is-invalid @enderror"
                                placeholder="{{ __('Maximum Amount') }}"
                                value="{{ old('maximum_amount', $item->maximum_amount ?? '') }}"
                            />
                            @error('maximum_amount')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Single Deposit Min Amount') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="number"
                                step="0.01"
                                name="single_deposit_min_amount"
                                class="form-control @error('single_deposit_min_amount') is-invalid @enderror"
                                placeholder="{{ __('Single Deposit Min Amount') }}"
                                value="{{ old('single_deposit_min_amount', $item->single_deposit_min_amount ?? '') }}"
                            />
                            @error('single_deposit_min_amount')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Single Deposit Max Amount') }}:</label>
                        <div class="col-lg-9">
                            <input
                                type="number"
                                step="0.01"
                                name="single_deposit_max_amount"
                                class="form-control @error('single_deposit_max_amount') is-invalid @enderror"
                                placeholder="{{ __('Single Deposit Max Amount') }}"
                                value="{{ old('single_deposit_max_amount', $item->single_deposit_max_amount ?? '') }}"
                            />
                            @error('single_deposit_max_amount')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Currency') }}:</label>
                        <div class="col-lg-9">
                            <select
                                name="currency"
                                class="form-control @error('currency') is-invalid @enderror"
                            >
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->value }}" @selected(old('currency', ($item->currency ?? null)?->value ?? $currencies[0]->value) == $currency->value)>
                                        {{ $currency->name() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('currency')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Status') }}:</label>
                        <div class="col-lg-9">
                            @php
                                $currentStatus = old('status', ($item->status ?? 0) instanceof \App\Enums\WalletStatus ? ($item->status)->value : ($item->status ?? 0));
                            @endphp

                            <select name="status" class="form-control @error('status') is-invalid @enderror">
                                @foreach($wallet_statuses as $status)
                                    <option value="{{ $status->value }}" @selected($currentStatus == $status->value)>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>

                            @error('status')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Description') }}:</label>
                        <div class="col-lg-9">
                            <textarea
                                name="description"
                                rows="4"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="{{ __('Description') }}"
                            >{{ old('description', $item->description ?? '') }}</textarea>
                            @error('description')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label">{{ __('Files') }}:</label>
                        <div class="col-lg-9">
                            <input
                                id="wallet-files-input"
                                name="file"
                                type="file"
                                class="file-input"
                                multiple
                                @if(isset($item) && $item->id)
                                    data-upload-url="{{ route('admin.wallets.files.upload', $item->id) }}"
                                @endif
                            >
                        </div>
                    </div>

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
