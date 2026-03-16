<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'B360') }}</title>

    <!-- Favicon -->
    @php $brandFavicon = setting('branding.favicon'); @endphp
    <link rel="shortcut icon" type="image/x-icon" href="{{ $brandFavicon ? asset('storage/' . $brandFavicon) : asset('build/img/favicon.png') }}">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('build/css/bootstrap.min.css') }}">

    <!-- Select2 CSS -->
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/css/select2.min.css') }}">

    <!-- Tabler Icons CSS -->
    <link rel="stylesheet" href="{{ asset('build/plugins/tabler-icons/tabler-icons.min.css') }}">

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="{{ asset('build/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/fontawesome/css/all.min.css') }}">

    <!-- Feathericon CSS -->
    <link rel="stylesheet" href="{{ asset('build/css/feather.css') }}">

    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ asset('build/css/style.css') }}">
</head>
<body class="account-page">
    @php
        $scriptVersion = file_exists(public_path('build/js/script.js')) ? filemtime(public_path('build/js/script.js')) : time();
        $themeColorpickerVersion = file_exists(public_path('build/js/theme-colorpicker.js')) ? filemtime(public_path('build/js/theme-colorpicker.js')) : $scriptVersion;
        $select2Version = file_exists(public_path('build/plugins/select2/js/select2.min.js')) ? filemtime(public_path('build/plugins/select2/js/select2.min.js')) : $scriptVersion;
    @endphp

    <div class="main-wrapper">
        {{ $slot }}
    </div>

    <!-- jQuery -->
    <script src="{{ asset('build/js/jquery-3.7.1.min.js') }}"></script>
    <!-- Feather Icon JS -->
    <script src="{{ asset('build/js/feather.min.js') }}"></script>
    <!-- Slimscroll JS -->
    <script src="{{ asset('build/js/jquery.slimscroll.min.js') }}"></script>
    <!-- Bootstrap Core JS -->
    <script src="{{ asset('build/js/bootstrap.bundle.min.js') }}"></script>
    <!-- Select2 JS -->
    <script src="{{ asset('build/plugins/select2/js/select2.min.js') }}?v={{ $select2Version }}"></script>
    @include('layout.partials.select2-config')
    <!-- Theme JS -->
    <script src="{{ asset('build/js/theme-colorpicker.js') }}?v={{ $themeColorpickerVersion }}"></script>
    <!-- Custom JS -->
    <script src="{{ asset('build/js/script.js') }}?v={{ $scriptVersion }}"></script>
    <script>
        // Toggle password visibility
        $(document).on('click', '.toggle-password', function () {
            const input = $(this).closest('.pass-group').find('.pass-input');
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                $(this).removeClass('ti-eye-off').addClass('ti-eye');
            } else {
                input.attr('type', 'password');
                $(this).removeClass('ti-eye').addClass('ti-eye-off');
            }
        });
    </script>
</body>
</html>
