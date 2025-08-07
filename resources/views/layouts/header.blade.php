<!-- app-header -->
<header class="app-header">

    <!-- Start::main-header-container -->
    <div class="main-header-container container-fluid">

        <!-- Start::header-content-left -->
        <div class="header-content-left">

            <!-- Start::header-element -->
            <div class="header-element">
                <div class="horizontal-logo">
                    {{-- <a href="index.html" class="header-logo">--}}
                    {{-- <img src="{{asset('images/livinc-logo-full.svg')}}" alt="logo"--}}
                    {{-- class="desktop-logo">--}}
                    {{-- <img src="{{asset('images/livinc-logo-sm.png')}}" alt="logo"--}}
                    {{-- class="toggle-logo">--}}
                    {{-- <img src="{{asset('images/livinc-logo-full.svg')}}" alt="logo"--}}
                    {{-- class="desktop-dark">--}}
                    {{-- <img src="{{asset('images/livinc-logo-sm.png')}}" alt="logo"--}}
                    {{-- class="toggle-dark">--}}
                    {{-- <img src="{{asset('images/livinc-logo-full.svg')}}" alt="logo"--}}
                    {{-- class="desktop-white">--}}
                    {{-- <img src="{{asset('images/livinc-logo-sm.png')}}" alt="logo"--}}
                    {{-- class="toggle-white">--}}
                    {{-- </a>--}}
                </div>
            </div>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <div class="header-element">
                <!-- Start::header-link -->
                <a aria-label="Hide Sidebar"
                    class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle"
                    data-bs-toggle="sidebar" href="javascript:void(0);"><span></span></a>
                <!-- End::header-link -->
            </div>
            <!-- End::header-element -->

        </div>
        <!-- End::header-content-left -->
        <div class="header-content-right">

{{--            <li class="nav-item dropdown">--}}
{{--                <a id="navbarDropdown" class="nav-link" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">--}}
{{--                    <i class="fa fa-bell"></i>--}}
{{--                    <span id="notificationCount" class="badge badge-light bg-success badge-xs">0</span>--}}
{{--                </a>--}}
{{--                <ul id="notificationDropdown" class="dropdown-menu" style="width:50vh;height:50vh;overflow:auto;">--}}

{{--                    <!-- Notification items will be appended here -->--}}
{{--                </ul>--}}
{{--            </li>--}}

            <!-- Start::header-content-right -->
            <div class="header-element" style="display: flex; align-items: center;">
                <a href="javascript:void(0);" class="header-link dropdown-toggle" id="mainHeaderProfile" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                    <div class="d-flex align-items-center">
                        <div class="me-sm-2 me-0">
{{--                            <img src="../assets/images/faces/9.jpg" alt="img" width="32" height="32" class="rounded-circle">--}}
                        </div>
                        <div class="d-sm-block d-none">
                            <p class="fw-semibold mb-0 lh-1"></p>
                            <span class="op-7 fw-normal d-block fs-11">Admin</span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Start::header-element -->
            <div class="header-element header-theme-mode">
                <!-- Start::header-link|layout-setting -->
                <a class="header-link layout-setting" onclick="toggleTheme()">
                    <span class="light-layout">
                        <!-- Start::header-link-icon -->
                        <i class="bx bx-moon header-link-icon"></i>
                        <!-- End::header-link-icon -->
                    </span>
                    <span class="dark-layout">
                        <!-- Start::header-link-icon -->
                        <i class="bx bx-sun header-link-icon"></i>
                        <!-- End::header-link-icon -->
                    </span>
                </a>
                <!-- End::header-link|layout-setting -->
            </div>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <div class="header-element header-theme-mode">
                <a href="{{ route('logout') }}" class="header-link layout-setting" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                    <i class="bx bx-log-out-circle header-link-icon" title="Logout"></i>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </a>

            </div>

            <!-- End::header-element -->

            <!-- Start::header-element -->
            {{-- <div class="header-element">--}}
            {{-- <!-- Start::header-link|switcher-icon -->--}}
            {{-- <a href="javascript:void(0);" class="header-link switcher-icon" data-bs-toggle="offcanvas"--}}
            {{-- data-bs-target="#switcher-canvas">--}}
            {{-- <i class="bx bx-cog header-link-icon"></i>--}}
            {{-- </a>--}}
            {{-- <!-- End::header-link|switcher-icon -->--}}
            {{-- </div>--}}
            <!-- End::header-element -->

        </div>
        <!-- End::header-content-right -->

    </div>
    <!-- End::main-header-container -->

</header>
<!-- /app-header -->
<script>
    // Function to toggle theme
    function toggleTheme() {
        var isDarkMode = document.body.classList.toggle('dark-mode');
        localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
        console.log(isDarkMode);
    }


    document.addEventListener('DOMContentLoaded', function() {
        var theme = localStorage.getItem('theme');
        if (theme === 'dark') {
            document.body.classList.add('dark-mode');
        }
    });
</script>
