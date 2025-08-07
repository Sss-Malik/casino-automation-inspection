<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="dark"
      data-menu-styles="dark" data-toggled="close" data-page-style="modern" style="--primary-rgb: 244, 67, 54;">


<head>
    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
{{--    <link rel="icon" type="image/x-icon" href="{{ asset('images/iconinc-favicon.ico') }}">--}}

    <title>@yield('title') - Casino.</title>
    @include('layouts.css')
    @yield('css')
    <style>
        #overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: none; /* Initially hidden */
        }
        #overlay .loader {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
        }
    </style>

</head>

<body>

@include('layouts.switcher')
<div id="overlay">
    <div class="loader">
        <span class="me-2">Loading</span>
        <span class="loading"><i class="ri-loader-4-line fs-16"></i></span>
    </div>
</div>
<!-- Loader -->
<div id="loader">
    <img src="{{asset('dist/assets/images/media/loader.svg')}}" alt="">
</div>
<!-- Loader -->
@php
    $segments = Request::segments() ?? 'N/A';
@endphp

<div class="page">
    @include('layouts.header')
    @include('layouts.sidebar')

    <div class="main-content app-content">
        <div class="container-fluid">
            @yield('content')
        </div>
    </div>
    @include('layouts.footer')
</div>

<!-- Scroll To Top -->
<div class="scrollToTop"  style="background-color: #e5b070">
    <span class="arrow"><i class="ri-arrow-up-s-fill fs-20"></i></span>
</div>
<div id="responsive-overlay"></div>
<!-- Scroll To Top -->

@include('layouts.js')
@yield('js')
@stack('scripts')
</body>
</html>
