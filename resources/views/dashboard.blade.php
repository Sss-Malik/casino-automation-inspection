@extends('layouts.main')


@section('title')
    Dashboard
@endsection

@section('content')
    <!-- Start::row-1 -->
    <div class="row mt-5">
        <!-- Left Column: Requests + Duration -->
        <div class="col-xl-7">
            <div class="row">
                <!-- Backend Requests Report -->
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">
                                Backend Requests Report
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="backendRequestAnalytics" style="height: 350px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Request Status -->
        <div class="col-xl-5">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">Request Status Analytics</div>
                    <div class="dropdown">
                        <a href="javascript:void(0);" class="p-2 fs-12 text-muted" data-bs-toggle="dropdown">
                            View All<i class="ri-arrow-down-s-line align-middle ms-1 d-inline-block"></i>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a class="dropdown-item" href="javascript:void(0);">Download</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);">Import</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);">Export</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div id="requestStatusAnalytics" style="height: 400px;"></div>
                </div>
            </div>
        </div>
    </div>
    <!--End::row-1 -->

    <!-- Start::row-2 -->
    <div class="row mt-4">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">
                        Average Duration per Request Type (by Backend)
                    </div>
                </div>
                <div class="card-body">
                    <div id="backendDurationByType" style="height: 500px;"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- End::row-2 -->

@endsection

@section('js')
@endsection

@pushonce('scripts')
    <!-- ApexCharts -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Backend Request Analytics chart start //
            // ====== 1️⃣ Pass PHP data to JS ======
            const backendData = @json($backends);

            // ====== 2️⃣ Prepare chart labels ======
            const labels = backendData.map(item => item.game_name);

            // ====== 3️⃣ Build series for each request type ======
            const series = [
                {
                    name: 'Freeplay',
                    data: backendData.map(item => item.freeplay_count)
                },
                {
                    name: 'Recharge',
                    data: backendData.map(item => item.recharge_count)
                },
                {
                    name: 'Create',
                    data: backendData.map(item => item.create_count)
                },
                {
                    name: 'Read',
                    data: backendData.map(item => item.read_count)
                },
                {
                    name: 'Reset Password',
                    data: backendData.map(item => item.reset_password_count)
                },
                {
                    name: 'Withdraw',
                    data: backendData.map(item => item.withdraw_count)
                }
            ];

            // ====== 4️⃣ Chart options ======
            const options = {
                series: series,
                chart: {
                    type: 'bar',
                    height: 420,
                    stacked: true,
                    toolbar: { show: true },
                    zoom: { enabled: true }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        borderRadius: 4,
                        columnWidth: '45%',
                    },
                },
                dataLabels: { enabled: false },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: labels,
                    title: { text: 'Games' },
                    labels: { rotate: -25, trim: true }
                },
                yaxis: {
                    title: { text: 'Number of Requests' },
                },
                fill: {
                    opacity: 1
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'center',
                    fontSize: '13px',
                    markers: {
                        radius: 12
                    }
                },
                tooltip: {
                    y: {
                        formatter: val => val + " requests"
                    }
                },
                grid: {
                    borderColor: '#f1f1f1',
                    strokeDashArray: 3
                },
                colors: [
                    '#845ADF', // freeplay
                    '#23b7e5', // recharge
                    '#28a745', // create
                    '#17a2b8', // read
                    '#dc3545', // reset-password
                    '#ffb22b'  // withdraw
                ]
            };

            // ====== 5️⃣ Render chart ======
            const chart = new ApexCharts(document.querySelector("#backendRequestAnalytics"), options);
            chart.render();
            // Backend Request Analytics chart end //


            // Request Status Analytics chart start //
            // 1️⃣  Data from controller
            const statusData = @json($statusAnalytics);

            // 2️⃣  Extract labels and data series
            const labels2 = statusData.map(i => i.type);

            const series2 = [
                { name: 'Success', data: statusData.map(i => i.success_count) },
                { name: 'Failed',  data: statusData.map(i => i.failed_count) },
                { name: 'Pending', data: statusData.map(i => i.pending_count) }
            ];

            // 3️⃣  Chart configuration
            const options2 = {
                series: series2,
                chart: {
                    type: 'bar',
                    height: 400,
                    stacked: true,
                    stackType: '100%',
                    toolbar: { show: true }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 4,
                    }
                },
                dataLabels: { enabled: false },
                stroke: {
                    width: 1,
                    colors: ['#fff']
                },
                xaxis: {
                    categories: labels2,
                    title: { text: 'Request Types' }
                },
                yaxis: {
                    title: { text: 'Percentage of Requests' }
                },
                fill: { opacity: 1 },
                legend: {
                    position: 'top',
                    horizontalAlign: 'center'
                },
                colors: [
                    '#28a745', // success
                    '#dc3545', // failed
                    '#ffc107'  // pending
                ],
                tooltip: {
                    shared: false,
                    y: { formatter: val => val + ' requests' }
                },
                grid: {
                    borderColor: '#f1f1f1',
                    strokeDashArray: 3
                }
            };

            // 4️⃣  Render chart
            new ApexCharts(document.querySelector("#requestStatusAnalytics"), options2).render();
            // Request Status Analytics chart end //

            // Backend duration by request type start //

            // 1️⃣ Raw data from Controller
            const rawData = @json($backendDurationType);

            // 2️⃣ Prepare data structure
            const gameNames = [...new Set(rawData.map(i => i.game_name))];
            const requestTypes = [...new Set(rawData.map(i => i.type))];

            // Build series per request type
            const series4 = requestTypes.map(type => ({
                name: type,
                data: gameNames.map(game => {
                    const record = rawData.find(r => r.game_name === game && r.type === type);
                    return record ? record.avg_duration : 0;
                })
            }));

            // 3️⃣ Chart options
            const options4 = {
                series: series4,
                chart: {
                    type: 'bar',
                    height: 450,
                    stacked: true,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 5
                    }
                },
                xaxis: {
                    categories: gameNames,
                    title: { text: 'Average Duration (seconds)' },
                    labels: { style: { fontSize: '12px' } }
                },
                yaxis: {
                    labels: { style: { fontSize: '13px' } }
                },
                colors: [
                    '#845ADF', // freeplay
                    '#23b7e5', // recharge
                    '#28a745', // create
                    '#17a2b8', // read
                    '#dc3545', // reset-password
                    '#ffb22b'  // withdraw
                ],
                tooltip: {
                    shared: false,
                    y: { formatter: val => val.toFixed(2) + ' s' }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'center'
                },
                grid: {
                    borderColor: '#f1f1f1',
                    strokeDashArray: 3
                },
                dataLabels: { enabled: false },
                fill: { opacity: 1 }
            };

            // 4️⃣ Render chart
            new ApexCharts(document.querySelector("#backendDurationByType"), options4).render();

            // Backend duration by request type end //

        });
    </script>

@endpushonce
