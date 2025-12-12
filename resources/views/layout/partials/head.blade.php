
@if (!Route::is(['layout-horizontal','layout-detached','layout-modern','layout-two-column','layout-hovered','layout-boxed','layout-rtl','layout-dark',
'under-maintenance', 'coming-soon','error-404','error-500','two-step-verification-3','two-step-verification-2','two-step-verification','email-verification-3','email-verification-2','email-verification','reset-password-3','reset-password-2','reset-password','forgot-password-3','forgot-password-2','forgot-password','register-3','register-2','register','signin-3','signin-2','signin','success','success-2','success-3','lock-screen']))
    <script src="{{URL::asset('build/js/theme-script.js')}}"></script>	
@endif

@if (!Route::is(['layout-rtl']))
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ url('build/css/bootstrap.min.css') }}">
@endif

@if (Route::is(['layout-rtl']))
	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="{{ url('build/css/bootstrap.rtl.min.css')}}">
@endif

    <!-- Datetimepicker CSS -->
    <link rel="stylesheet" href="{{ url('build/css/bootstrap-datetimepicker.min.css') }}">

    <!-- Daterangepicker -->
    <link rel="stylesheet" href="{{ url('build/plugins/daterangepicker/daterangepicker.css') }}">

    <!-- animation CSS -->
    <link rel="stylesheet" href="{{ url('build/css/animate.css') }}">

    <!-- Select2 CSS -->
    <link rel="stylesheet" href="{{ url('build/plugins/select2/css/select2.min.css') }}">

@if (Route::is(['icon-tabler', 'projects', 'search-list', 'social-feed']))
    <!-- Tabler Icon CSS -->
    <link rel="stylesheet" href="{{ url('build/plugins/icons/tabler-icons/tabler-icons.css') }}">
 @endif

    <link rel="stylesheet" href="{{ url('build/plugins/tabler-icons/tabler-icons.min.css')}}">

 @if (Route::is(['icon-bootstrap']))
    <!-- Bootstrap Icon CSS -->
    <link rel="stylesheet" href="{{ url('build/plugins/icons/bootstrap/bootstrap-icons.min.css') }}">
 @endif

 @if (Route::is(['icon-remix']))
    <!-- Remix Icon CSS -->
    <link rel="stylesheet" href="{{ url('build/plugins/icons/remix/fonts/remixicon.css') }}">
 @endif

 @if (Route::is(['form-pickers']))
    <!-- Form Date PIckers CSS -->
    <link rel="stylesheet" href="{{ url('build/plugins/flatpickr/flatpickr.css') }}">
    <link rel="stylesheet" href="{{ url('build/plugins/bootstrap-datepicker/bootstrap-datepicker.css') }}">
    <link rel="stylesheet" href="{{ url('build/plugins/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ url('build/plugins/jquery-timepicker/jquery-timepicker.css') }}">
    <link rel="stylesheet" href="{{ url('build/plugins/pickr/pickr-themes.css') }}">
 @endif

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="{{ url('build/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ url('build/plugins/fontawesome/css/all.min.css') }}">

    <!-- Feathericon CSS -->
    <link rel="stylesheet" href="{{ url('build/css/feather.css') }}">

    <!-- Fancybox -->
    <link rel="stylesheet" href="{{url('build/plugins/fancybox/jquery.fancybox.min.css')}}">

    <!-- Summernote CSS -->
    <link rel="stylesheet" href="{{ url('build/plugins/summernote/summernote-bs4.min.css') }}">

    <!-- Bootstrap Tagsinput CSS -->
    <link rel="stylesheet" href="{{ url('build/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css') }}">

    <!-- Datatable CSS -->
    <link rel="stylesheet" href="{{ url('build/css/dataTables.bootstrap5.min.css') }}">

    <!-- Mobile CSS-->
    <link rel="stylesheet" href="{{ url('build/plugins/intltelinput/css/intlTelInput.css') }}">
    <link rel="stylesheet" href="{{ url('build/plugins/intltelinput/css/demo.css') }}">

    <link rel="stylesheet" href="{{ url('build/css/plyr.css') }}">

 <!-- Owl Carousel -->
 <link rel="stylesheet" href="{{ url('build/css/owl.carousel.min.css') }}">
 <link rel="stylesheet" href="{{ url('build/plugins/owlcarousel/owl.theme.default.min.css') }}">

@if (Route::is(['pos-5','product-details']))
    <!-- Owl Carousel CSS -->
    <link rel="stylesheet" href="{{ url('build/plugins/owlcarousel/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ url('build/plugins/owlcarousel/owl.theme.default.min.css') }}">
@endif

@if (Route::is(['sales-dashboard']))
    <!-- Map CSS -->
    <link rel="stylesheet" href="{{ url('build/plugins/jvectormap/jquery-jvectormap-2.0.5.css') }}">
@endif

@if (Route::is(['maps-leaflet']))
    <!-- Leaflet Maps CSS -->
    <link rel="stylesheet" href="{{ url('build/plugins/leaflet/leaflet.css') }}">
@endif

@if (Route::is(['maps-vector']))
    <!-- Jsvector Maps -->
    <link rel="stylesheet" href="{{ url('build/plugins/jsvectormap/css/jsvectormap.min.css') }}">
@endif

 <!-- Boxicons CSS -->
 <link rel="stylesheet" href="{{ url('build/plugins/boxicons/css/boxicons.min.css') }}">

@if (Route::is(['ui-stickynote', 'ui-timeline']))
    <!-- Sticky CSS -->
    <link rel="stylesheet" href="{{ url('build/plugins/stickynote/sticky.css') }}">
@endif

@if (Route::is(['ui-scrollbar']))
    <link rel="stylesheet" href="{{ url('build/plugins/scrollbar/scroll.min.css') }}">
@endif

@if (Route::is(['ui-toasts']))
    <!-- Toatr CSS -->
    <link rel="stylesheet" href="{{ url('build/plugins/toastr/toatr.css') }}">
@endif

@if (Route::is(['ui-lightbox']))
    <!-- Lightbox CSS -->
    <link rel="stylesheet" href="{{ url('build/plugins/lightbox/glightbox.min.css') }}">
@endif

@if (Route::is(['ui-clipboard', 'ui-drag-drop']))
    <!-- Dragula CSS -->
    <link rel="stylesheet" href="{{ url('build/plugins/dragula/css/dragula.min.css') }}">
@endif

@if (Route::is(['icon-feather']))
     <!-- Feather CSS -->
     <link rel="stylesheet" href="{{ url('build/plugins/icons/feather/feather.css') }}">
@endif

@if (Route::is(['icon-flag']))
     <!-- Pe7 CSS -->
     <link rel="stylesheet" href="{{ url('build/plugins/icons/flags/flags.css') }}">
@endif

@if (Route::is(['icon-ionic']))
     <!-- Ionic CSS -->
     <link rel="stylesheet" href="{{ url('build/plugins/icons/ionic/ionicons.css') }}">
@endif

@if (Route::is(['icon-material']))
     <!-- Material CSS -->
     <link rel="stylesheet" href="{{ url('build/plugins/material/materialdesignicons.css') }}">
@endif

@if (Route::is(['icon-pe7']))
     <!-- Pe7 CSS -->
     <link rel="stylesheet" href="{{ url('build/plugins/icons/pe7/pe-icon-7.css') }}">
@endif

@if (Route::is(['icon-simpleline']))
     <!-- Simpleline CSS -->
     <link rel="stylesheet" href="{{ url('build/plugins/simpleline/simple-line-icons.css') }}">
@endif

@if (Route::is(['icon-themify']))
     <!-- Themify CSS -->
     <link rel="stylesheet" href="{{ url('build/plugins/icons/themify/themify.css') }}">
@endif

@if (Route::is(['icon-typicon']))
     <!-- Pe7 CSS -->
     <link rel="stylesheet" href="{{ url('build/plugins/icons/typicons/typicons.css') }}">
@endif

@if (Route::is(['icon-weather']))
     <!-- Pe7 CSS -->
     <link rel="stylesheet" href="{{ url('build/plugins/icons/weather/weathericons.css') }}">
@endif

@if (Route::is(['ui-rangeslider']))
     <!-- Rangeslider CSS -->
     <link rel="stylesheet" href="{{ url('build/plugins/ion-rangeslider/css/ion.rangeSlider.min.css') }}">
@endif

@if (Route::is(['form-wizard']))
     <!-- Wizard CSS -->
     <link rel="stylesheet" href="{{ url('build/plugins/twitter-bootstrap-wizard/form-wizard.css') }}">
@endif

@if (Route::is(['ui-swiperjs', 'chat', 'video-call']))
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="{{ url('build/plugins/swiper/swiper-bundle.min.css') }}">
@endif
 
    <!-- Color Picker Css -->
    <link rel="stylesheet" href="{{ url('build/plugins/@simonwep/pickr/themes/nano.min.css')}}">

    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ url('build/css/style.css') }}">
