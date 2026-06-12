<!doctype html>
<html class="no-js" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
     <!-- CSRF Token -->
     <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-style-mode" content="1">
    <!-- Place favicon.ico in the root directory -->
    <link rel="shortcut icon" type="image/x-icon" href="{{asset('assets/images/favicon.ico')}}">

    <!-- CSS here -->
    <link rel="stylesheet" href="{{asset('assets/css/vendor/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/vendor/animate.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/apexcharts.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/jquery-jvectormap-2.0.5.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/swiper-bundle.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/vendor/magnific-popup.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/vendor/icomoon.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/vendor/fontawesome-pro.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/vendor/rating.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/vendor/dropzone.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/dropify.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/vendor/spacing.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/datatables.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/buttons.bootstrap5.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/jquery.dataTables.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/jquery.timepicker.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/tagify.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/flatpickr.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/jquery-ui.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/fullcalendar.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/ion.rangeSlider.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/simplebar.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/waves.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/nano.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/main.css')}}">

</head>
<body class="body-area">

    <div class="container-xxl">
        <!-- register area start-->
        <div class="authentication-wrapper basic-authentication">
            @yield('content')
            </div>
        <!-- register area end-->
    </div>

    <!-- Back to top start -->
    <div class="progress-wrap">
        <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
            <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
        </svg>
    </div>
    <!-- Back to top end -->

    <!-- JS here -->
    <script src="{{asset('assets/js/vendor/jquery-3.7.0.js')}}"></script>
    <script src="{{asset('assets/js/vendor/isotope.pkgd.js')}}"></script>
    <script src="{{asset('assets/js/vendor/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('assets/js/vendor/magnific-popup.min.js')}}"></script>
    <script src="{{asset('assets/js/vendor/ajax-form.js')}}"></script>
    <script src="{{asset('assets/js/vendor/jquery.repeater.js')}}"></script>
    <script src="{{asset('assets/js/plugins/waypoints.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/dayjs.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/loader.js')}}"></script>
    <script src="{{asset('assets/js/plugins/jsvectormap.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/world-merc.js')}}"></script>
    <script src="{{asset('assets/js/plugins/swiper-bundle.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/popper.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/simplebar.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/simplebar-active.js')}}"></script>
    <script src="{{asset('assets/js/plugins/backtotop.js')}}"></script>
    <script src="{{asset('assets/js/plugins/smooth-scrollbar.js')}}"></script>
    <script src="{{asset('assets/js/plugins/cleave.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/datatables.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/dataTables.bootstrap5.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/dataTables.buttons.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/jszip.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/pdfmake.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/vfs_fonts.js')}}"></script>
    <script src="{{asset('assets/js/plugins/buttons.html5.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/buttons.print.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/buttons.colVis.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/steps-form.js')}}"></script>
    <script src="{{asset('assets/js/plugins/dropify.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/dropzone.js')}}"></script>
    <script src="{{asset('assets/js/plugins/tinymce.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/custom.js')}}"></script>
    <script src="{{asset('assets/js/plugins/typeahead.bundle.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/bloodhound.js')}}"></script>
    <script src="{{asset('assets/js/plugins/select2.full.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/jquery.timepicker.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/flatpickr.js')}}"></script>
    <script src="{{asset('assets/js/plugins/tagify.js')}}"></script>
    <script src="{{asset('assets/js/plugins/jquery-ui.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/sweetalert2.all.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/apexcharts.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/fullcalendar.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/ion.rangeSlider.min.js')}}"></script>
    <script src="{{asset('assets/js/vendor/custom-tagify.js')}}"></script>
    <script src="{{asset('assets/js/vendor/height-equal.js')}}"></script>
    <script src="{{asset('assets/js/vendor/custom-chart.js')}}"></script>
    <script src="{{asset('assets/js/vendor/rangeslider-script.js')}}"></script>
    <script src="{{asset('assets/js/vendor/jquery.barrating.js')}}"></script>
    <script src="{{asset('assets/js/vendor/rating-script.js')}}"></script>
    <script src="{{asset('assets/js/main.js')}}"></script>
    <script src="{{asset('assets/js/vendor/sidebar.js')}}"></script>

</body>

</html>