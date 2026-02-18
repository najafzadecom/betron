<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ env('APP_NAME') }} @yield('title')</title>

    <!-- Global stylesheets -->
    <link href="{{ asset('admin/assets/fonts/inter/inter.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('admin/assets/icons/phosphor/styles.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('admin/assets/icons/icomoon/styles.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('admin/assets/css/all.min.css') }}" id="stylesheet" rel="stylesheet" type="text/css">
    <link href="{{ asset('admin/assets/css/custom.css') }}?v={{ time() }}" id="stylesheet" rel="stylesheet" type="text/css">
    <!-- /global stylesheets -->

    <!-- Core JS files -->
    <script src="{{ asset('admin/assets/js/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('admin/assets/demo/demo_configurator.js') }}"></script>
    <script src="{{ asset('admin/assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <!-- /core JS files -->

    <!-- Theme JS files -->
    <script src="{{ asset('admin/assets/js/vendor/tables/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/vendor/visualization/d3/d3.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/vendor/visualization/d3/d3_tooltip.js') }}"></script>
    <script src="{{ asset('admin/assets/js/vendor/notifications/sweet_alert.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/vendor/forms/selects/select2.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/vendor/ui/moment/moment.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/vendor/pickers/daterangepicker.js') }}"></script>
    <script src="{{ asset('admin/assets/js/app.js') }}?time={{ time() }}"></script>
    <script>
        // Daterange picker translations
        window.daterangeTranslations = {
            cancelLabel: '{{ __("Clear") }}',
            applyLabel: '{{ __("Apply") }}',
            fromLabel: '{{ __("From") }}',
            toLabel: '{{ __("To") }}',
            customRangeLabel: '{{ __("Custom") }}',
            format: 'YYYY-MM-DD',
            daysOfWeek: ['{{ __("Su") }}', '{{ __("Mo") }}', '{{ __("Tu") }}', '{{ __("We") }}', '{{ __("Th") }}', '{{ __("Fr") }}', '{{ __("Sa") }}'],
            monthNames: ['{{ __("January") }}', '{{ __("February") }}', '{{ __("March") }}', '{{ __("April") }}', '{{ __("May") }}', '{{ __("June") }}', '{{ __("July") }}', '{{ __("August") }}', '{{ __("September") }}', '{{ __("October") }}', '{{ __("November") }}', '{{ __("December") }}'],
            ranges: {
                '{{ __("Today") }}': [moment(), moment()],
                '{{ __("Yesterday") }}': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '{{ __("Last 7 Days") }}': [moment().subtract(6, 'days'), moment()],
                '{{ __("Last 30 Days") }}': [moment().subtract(29, 'days'), moment()],
                '{{ __("This Month") }}': [moment().startOf('month'), moment().endOf('month')],
                '{{ __("Last Month") }}': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        };
        
        // Delete confirmation translations
        window.deleteTranslations = {
            title: '{{ __("Are you sure?") }}',
            textTemplate: '{{ __("Are you sure you want to delete :item? This action cannot be undone.") }}',
            confirmButtonText: '{{ __("Yes, delete!") }}',
            cancelButtonText: '{{ __("No, cancel") }}',
            deletingTitle: '{{ __("Deleting...") }}',
            deletingText: '{{ __("Please wait") }}',
            successTitle: '{{ __("Successfully deleted!") }}',
            successText: '{{ __("Element successfully deleted") }}',
            errorTitle: '{{ __("Error!") }}',
            errorText: '{{ __("An error occurred while deleting") }}',
            okButton: '{{ __("OK") }}'
        };
    </script>
    <script src="{{ asset('admin/assets/js/custom.js') }}?v={{ time() }}"></script>
    <!-- /theme JS files -->
</head>
<body>
@include('admin.parts.navbar')

<div class="page-content">
    @include('admin.parts.sidebar')
    <div class="content-wrapper">
        <div class="content-inner">
            @include('admin.parts.header')
            @yield('content')
            @include('admin.parts.footer')
        </div>
        <div class="btn-to-top" style="">
            <button class="btn btn-secondary btn-icon rounded-pill" type="button"><i class="ph-arrow-up"></i></button>
        </div>
    </div>
</div>


@stack('scripts')
</body>
</html>
