<!-- Start::app-sidebar -->
<aside class="app-sidebar sticky" id="sidebar">

    <!-- Start::main-sidebar-header -->
    <div class="main-sidebar-header">
        <a href="#" class="header-logo">
{{--            <img src="{{asset('dist/assets/images/app-logos/Black logo - no background.png')}}" alt="logo" class="desktop-logo">--}}
{{--            <img src="{{asset('dist/assets/images/app-logos/Purple logo.png')}}" alt="logo" class="desktop-white">--}}
{{--            <img src="{{asset('dist/assets/images/app-logos/White logo - no background.png')}}" alt="logo" class="desktop-dark">--}}
{{--            <img src="{{asset('dist/assets/images/favicon.png')}}" alt="logo" class="toggle-logo">--}}
{{--            <img src="{{asset('dist/assets/images/app-logos/image001.png')}}" alt="logo" class="toggle-white">--}}
{{--            <img src="{{asset('dist/assets/images/favicon.png')}}" alt="logo" class="toggle-dark">--}}
        </a>
    </div>
    <!-- End::main-sidebar-header -->

    <!-- Start::main-sidebar -->
    <div class="main-sidebar" id="sidebar-scroll">

        <!-- Start::nav -->
        <nav class="main-menu-container nav nav-pills flex-column sub-open">
            <div class="slide-left" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                </svg>
            </div>
            <ul class="main-menu">
                <!-- Start::slide__category -->
                <!-- End::slide__category -->
                <li class="slide">
                    <a href="{{ route('dashboard') }}" class="side-menu__item">
                        <i class="bx bx-home side-menu__icon"></i>
                        <span class="side-menu__label">Dashboard</span>
                    </a>
                </li>
                <hr>
                <li class="slide has-sub "><a href="javascript:void(0);" class="side-menu__item"> <i class="bx bx-layer fs-18"></i>
                        <span class="side-menu__label ms-1">Tasks</span> <i
                            class="fe fe-chevron-right side-menu__angle"></i> </a>
                    <ul class="slide-menu child1"
                        style="position: relative; left: 0px; top: 0px; margin: 0px; transform: translate(120px, 228.889px); box-sizing: border-box; display: none;"
                        data-popper-placement="bottom">
                        <li class="slide side-menu__label1"><a class="side-menu__item" href="#">Tasks</a></li>
                        <li class="slide"><a class="side-menu__item" href="{{ route('tasks.index') }}">View tasks</a></li>

                    </ul>
                </li>
                <li class="slide has-sub "><a href="javascript:void(0);" class="side-menu__item">
                        <i class="bi bi-book fs-18"></i>
                        <span class="side-menu__label ms-1">Logs</span> <i
                            class="fe fe-chevron-right side-menu__angle"></i> </a>
                    <ul class="slide-menu child1"
                        style="position: relative; left: 0px; top: 0px; margin: 0px; transform: translate(120px, 228.889px); box-sizing: border-box; display: none;"
                        data-popper-placement="bottom">
                        <li class="slide side-menu__label1"><a class="side-menu__item" href="#">Logs</a></li>
                        <li class="slide"><a class="side-menu__item" href="{{ route('logs.index') }}">View logs</a></li>
                    </ul>
                </li>
                <li class="slide has-sub "><a href="javascript:void(0);" class="side-menu__item">
                        <i class="bi bi-hdd-network fs-18"></i>
                        <span class="side-menu__label ms-1">Requests</span> <i
                            class="fe fe-chevron-right side-menu__angle"></i> </a>
                    <ul class="slide-menu child1"
                        style="position: relative; left: 0px; top: 0px; margin: 0px; transform: translate(120px, 228.889px); box-sizing: border-box; display: none;"
                        data-popper-placement="bottom">
                        <li class="slide side-menu__label1"><a class="side-menu__item" href="#">Requests</a></li>
                        <li class="slide"><a class="side-menu__item" href="{{ route('request.index') }}">Make requests</a></li>
                        <li class="slide"><a class="side-menu__item" href="{{ route('request.view') }}">View requests</a></li>
                    </ul>
                </li>
                <li class="slide has-sub "><a href="javascript:void(0);" class="side-menu__item">
                        <i class="bx bx-layer fs-18"></i>
                        <span class="side-menu__label ms-1">Backend Accounts</span> <i
                            class="fe fe-chevron-right side-menu__angle"></i> </a>
                    <ul class="slide-menu child1"
                        style="position: relative; left: 0px; top: 0px; margin: 0px; transform: translate(120px, 228.889px); box-sizing: border-box; display: none;"
                        data-popper-placement="bottom">
                        <li class="slide side-menu__label1"><a class="side-menu__item" href="#">Backend Accounts</a></li>
                        <li class="slide"><a class="side-menu__item" href="{{ route('backend.accounts.stats') }}">View stats</a></li>
                        <li class="slide"><a class="side-menu__item" href="{{ route('backend.accounts.view.all') }}">View accounts</a></li>
                    </ul>
                </li>
            </ul>

            <div class="slide-right" id="slide-right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
                </svg>
            </div>
        </nav>
        <!-- End::nav -->

    </div>
    <!-- End::main-sidebar -->

</aside>
<!-- End::app-sidebar -->
