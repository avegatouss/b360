    <!-- jQuery -->
    <script src="{{ URL::asset('build/js/jquery-3.7.1.min.js') }}"></script>

    <!-- Feather Icon JS -->
    <script src="{{ URL::asset('build/js/feather.min.js') }}"></script>

    <!-- Slimscroll JS -->
    <script src="{{ URL::asset('build/js/jquery.slimscroll.min.js') }}"></script>

    <!-- Bootstrap Core JS -->
    <script src="{{ URL::asset('build/js/bootstrap.bundle.min.js') }}"></script>

@if (Route::is(['admin-dashboard','barcode','calendar','chart-apex','companies','email-reply','dashboard','email','file-manager','form-elements','icon-feather','icon-flag','icon-fontawesome','icon-ionic','icon-material','icon-pe7','icon-remix','icon-simpleline', 'icon-tabler','icon-themify', 'icon-typicon','icon-weather','index','layout-boxed','layout-dark','layout-detached','layout-horizontal','layout-hovered','layout-rtl','layout-two-column','notes','pos','pos-2','pos-3','pos-4','pos-5','qrcode','sales-dashboard','todo-list','todo']))
    <!-- Chart JS -->
    <script src="{{ URL::asset('build/plugins/apexchart/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/apexchart/chart-data.js') }}"></script>
@endif

    <!-- Sweetalert 2 -->
    <script src="{{ URL::asset('build/plugins/sweetalert/sweetalert2.all.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/sweetalert/sweetalerts.min.js') }}"></script> 

@if(Route::is(['chat','video-call', 'ui-swiperjs']))
    <!-- Swiper JS -->
    <script src="{{ URL::asset('build/plugins/swiper/swiper-bundle.min.js') }}"></script>
@endif

@if(Route::is(['chat','email-reply','social-feed','search-list']))
    <!-- FancyBox JS -->
    <script src="{{ URL::asset('build/plugins/fancybox/jquery.fancybox.min.js') }}"></script>
@endif

    <!-- Select2 JS -->
    <script src="{{ URL::asset('build/plugins/select2/js/select2.min.js') }}"></script>

    <!-- Datetimepicker JS -->
    <script src="{{ URL::asset('build/js/moment.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/daterangepicker/daterangepicker.js') }}"></script>


    <!-- Datetimepicker CSS -->
    <script src="{{ URL::asset('build/plugins/moment/moment.min.js') }}"></script>

@if (Route::is(['form-pickers']))
    <script src="{{ URL::asset('build/plugins/flatpickr/flatpickr.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/jquery-timepicker/jquery-timepicker.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/pickr/pickr.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/@simonwep/pickr/pickr.min.j') }}"></script>
    <script src="{{ URL::asset('build/js/forms-pickers.js') }}"></script>
@endif

@if (Route::is(['maps-vector']))
    <!-- JSVector Maps MapsJS -->
    <script src="{{ URL::asset('build/plugins/jsvectormap/js/jsvectormap.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/jsvectormap/maps/world-merc.js') }}"></script>
    <script src="{{ URL::asset('build/js/us-merc-en.js') }}"></script>
    <script src="{{ URL::asset('build/js/russia.js') }}"></script>
    <script src="{{ URL::asset('build/js/spain.js') }}"></script>
    <script src="{{ URL::asset('build/js/canada.js') }}"></script>
    <script src="{{ URL::asset('build/js/jsvectormap.js') }}"></script>
@endif

@if (Route::is(['maps-leaflet']))
    <!-- Leaflet Maps JS -->
    <script src="{{ URL::asset('build/plugins/leaflet/leaflet.js') }}"></script>
    <script src="{{ URL::asset('build/js/leaflet.js') }}"></script>
@endif

@if (Route::is(['ui-sortable']))
    <!-- Sortable JS -->
    <script src="{{ URL::asset('build/plugins/sortablejs/Sortable.min.js') }}"></script>
@endif

@if (Route::is(['pos-5','product-details']))
    <!-- Owl JS -->
    <script src="{{URL::asset('build/plugins/owlcarousel/owl.carousel.min.js')}}"></script>
@endif

@if (Route::is(['add-product','all-blog','blog-tag','domain','edit-product','email-reply','localization-settings','otp-settings','packages','product-list','purchase-transaction','reviews','subscription','varriant-attributes']))
    <!-- Bootstrap Tagsinput JS -->
    <script src="{{ URL::asset('build/plugins/bootstrap-tagsinput/bootstrap-tagsinput.js') }}"></script>
@endif

    <!-- Datatable JS -->
    <script src="{{ URL::asset('build/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/dataTables.bootstrap5.min.js') }}"></script>

    <!-- Summernote JS -->
    <script src="{{ URL::asset('build/plugins/summernote/summernote-bs4.min.js') }}"></script>

@if (Route::is(['billers','blog-comments','chat','contacts','customers','expired-products','security-settings','warehouse']))
    <!-- Mobile Input -->
    <script src="{{ URL::asset('build/plugins/intltelinput/js/intlTelInput.js') }}"></script>
@endif

@if (Route::is(['file-manager']))
    <script src="{{ URL::asset('build/js/plyr-js.js') }}"></script>
@endif

    <!-- Owl Carousel -->
    <script src="{{ URL::asset('build/js/owl.carousel.min.js') }}"></script>

    <!-- Sticky-sidebar -->
    <script src="{{ URL::asset('build/plugins/theia-sticky-sidebar/ResizeSensor.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js') }}"></script>

    <!-- Theiastickysidebar JS -->
    <script src="{{ URL::asset('build/plugins/theia-sticky-sidebar/theia-sticky-sidebar.min.js')}}"></script>
    <script src="{{ URL::asset('build/plugins/theia-sticky-sidebar/ResizeSensor.min.js')}}"></script>


@if (Route::is(['sales-dashboard']))
    <!-- Map JS -->
    <script src="{{ URL::asset('build/plugins/jvectormap/jquery-jvectormap-2.0.5.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/jvectormap/jquery-jvectormap-world-mill.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/jvectormap/jquery-jvectormap-ru-mill.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/jvectormap/jquery-jvectormap-us-aea.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/jvectormap/jquery-jvectormap-uk_countries-mill.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/jvectormap/jquery-jvectormap-in-mill.js') }}"></script>
    <script src="{{ URL::asset('build/js/jvectormap.js') }}"></script>
@endif

@if (Route::is('kanban-view'))
    <!-- Full Calendar JS -->
    <script src="{{ URL::asset('build/js/jquery-ui.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/fullcalendar/fullcalendar.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/fullcalendar/jquery.fullcalendar.js') }}"></script>
@endif
@if (Route::is('calendar'))
   <!-- Fullcalendar JS -->
   <script src="{{ URL::asset('build/plugins/fullcalendar/index.global.min.js')}}"></script>
   <script src="{{ URL::asset('build/plugins/fullcalendar/calendar-data.js')}}"></script>
@endif

@if (Route::is( 'ui-drag-drop','ui-clipboard','ui-stickynote','projects','search-list'))
    <script src="{{ URL::asset('build/js/jquery.ui.touch-punch.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/jquery-ui.min.js') }}"></script>
@endif

@if (Route::is(['ui-clipboard']))
    <!-- Clipboard JS -->
    <script src="{{ URL::asset('build/plugins/clipboard/clipboard.min.js') }}"></script>
@endif

@if (Route::is(['ui-drag-drop']))
    <!-- Dragula JS -->
    <script src="{{ URL::asset('build/plugins/dragula/js/dragula.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/dragula/js/drag-drop.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/dragula/js/draggable-cards.js') }}"></script>
@endif

@if (Route::is(['ui-rating']))
    <!-- Rater JS -->
    <script src="{{ URL::asset('build/plugins/rater-js/index.js') }}"></script>
    <!-- Internal Ratings JS -->
    <script src="{{ URL::asset('build/js/ratings.js') }}"></script>
@endif

@if (Route::is(['ui-counter']))
    <!-- Stickynote JS -->
    <script src="{{ URL::asset('build/plugins/countup/jquery.counterup.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/countup/jquery.waypoints.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/countup/jquery.missofis-countdown.js') }}"></script>
 @endif

 @if (Route::is(['ui-text-editor']))
    <!-- Summernote JS -->
    <script src="{{ URL::asset('build/plugins/summernote/summernote-bs4.min.js') }}"></script>
@endif

@if (Route::is(['ui-rangeslider']))
    <!-- Rangeslider JS -->
    <script src="{{ URL::asset('build/plugins/ion-rangeslider/js/ion.rangeSlider.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/ion-rangeslider/js/custom-rangeslider.js') }}"></script>
@endif

@if (Route::is(['form-mask']))
    <!-- Mask JS -->
    <script src="{{ URL::asset('build/js/jquery.maskedinput.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/mask.js') }}"></script>
@endif

@if (Route::is(['ui-scrollbar']))
    <!-- Plyr JS -->
    <script src="{{ URL::asset('build/plugins/scrollbar/scrollbar.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/scrollbar/custom-scroll.js') }}"></script>
@endif

@if (Route::is(['ui-stickynote']))
    <!-- Stickynote JS -->
    <script src="{{ URL::asset('build/js/jquery-ui.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/stickynote/sticky.js') }}"></script>
@endif

@if (Route::is(['ui-toasts']))
    <!-- Mask JS -->
    <script src="{{ URL::asset('build/plugins/toastr/toastr.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/toastr/toastr.js') }}"></script>
@endif

@if (Route::is(['ui-lightbox']))
    <script src="{{ URL::asset('build/plugins/lightbox/glightbox.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/lightbox/lightbox.js') }}"></script>
@endif

@if (Route::is(['chart-c3']))
    <!-- Chart JS -->
    <script src="{{ URL::asset('build/plugins/c3-chart/d3.v5.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/c3-chart/c3.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/c3-chart/chart-data.js') }}"></script>
@endif

@if (Route::is(['chart-flot']))
    <!-- Chart JS --> 
    <script src="{{ URL::asset('build/plugins/flot/jquery.flot.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/flot/jquery.flot.pie.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/flot/chart-data.js') }}"></script>
@endif

@if (Route::is(['lightbox']))
    <!-- lightbox JS -->
    <script src="{{ URL::asset('build/plugins/lightbox/glightbox.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/lightbox/lightbox.js') }}"></script>
@endif

@if (Route::is(['chart-js','subscription','dashboard','index','layout-horizontal','layout-detached','layout-two-column','layout-dark','layout-boxed','layout-rtl','subscription']))
    <!-- Chart JS -->
    <script src="{{ URL::asset('build/plugins/chartjs/chart.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/chartjs/chart-data.js') }}"></script>
@endif

@if (Route::is(['chart-morris']))
    <!-- Chart JS -->
    <script src="{{ URL::asset('build/plugins/morris/raphael-min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/morris/morris.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/morris/chart-data.js') }}"></script>
@endif

@if (Route::is(['chart-peity','subscription','dashboard']))
    <!-- Chart JS -->
    <script src="{{ URL::asset('build/plugins/peity/jquery.peity.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/peity/chart-data.js') }}"></script>
@endif

@if (Route::is(['form-select2']))
    <script src="{{ URL::asset('build/js/custom-select2.js') }}"></script>
@endif

@if (Route::is(['form-fileupload']))
    <!-- Fileupload JS -->
    <script src="{{ URL::asset('build/plugins/fileupload/fileupload.min.js') }}"></script>
@endif

@if (Route::is(['form-wizard']))
    <!-- Wizard JS -->
    <script src="{{ URL::asset('build/plugins/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/twitter-bootstrap-wizard/prettify.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/twitter-bootstrap-wizard/form-wizard.js') }}"></script>
@endif

@if (Route::is(['ui-swiperjs']))
    <!-- Internal Swiper JS -->
    <script src="{{ URL::asset('build/js/swiper.js') }}"></script>
@endif


@if (Route::is(['pos','pos-2','pos-3','pos-4','pos-5']))
    <!-- Calculator JS -->
    <script src="{{URL::asset('build/js/calculator.js')}}"></script>
@endif

@if (Route::is(['ui-modals']))
    <!-- Modal JS -->
    <script src="{{URL::asset('build/js/modal.js')}}"></script>
@endif

    <!-- Color Picker JS -->
    <script src="{{URL::asset('build/plugins/@simonwep/pickr/pickr.es5.min.js')}}"></script>


@if (Route::is(['chat']))
    <!-- Slimscroll JS -->
    <script src="{{URL::asset('build/plugins/slimscroll/jquery.slimscroll.min.js')}}"></script>
@endif

    <!-- Custom JS -->
    <script src="{{ URL::asset('build/js/theme-colorpicker.js') }}"></script>
    <script src="{{ URL::asset('build/js/script.js') }}"></script>
