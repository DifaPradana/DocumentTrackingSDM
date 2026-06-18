<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Default Title' }}</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/sdm_logo.png" />
    @include('partials.styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>


<body>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        <!-- Sidebar Start -->
        @include('partials.sidebar')
        <!--  Sidebar End -->
        <!--  Main wrapper -->
        <div class="body-wrapper">
            <!--  Header Start -->
            @include('partials.header')
            <!--  Header End -->
            <div class="container-fluid">

                {{ $slot }}
                @yield('content')
                <!-- End Content -->
            </div>
        </div>
    </div>

    @include('partials.scripts')
    @livewireScripts

</body>

</html>