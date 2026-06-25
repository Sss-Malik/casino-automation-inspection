@extends('layouts.main')

@section('title', 'Tasks')


@section('content')
    @php
        $statusClass = [
            'pending' => 'bg-warning',
            'success' => 'bg-success',
            'failed' => 'bg-danger'
        ];
    @endphp
    <!-- Start::row-1 -->
    <div class="row mt-5">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        Automation Tasks
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="statusFilter">Filter by Status</label>
                                <select id="statusFilter" class="form-control">
                                    <option value="">All</option>
                                    <option value="pending">Pending</option>
                                    <option value="success">Success</option>
                                    <option value="failed">Failed</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="backendFilter">Filter by Backend</label>
                                <select id="backendFilter" class="form-control">
                                    <option value="">All</option>
                                    <option value="firekirin">FireKirin</option>
                                    <option value="gameroom">GameRoom</option>
                                    <option value="gamevault">GameVault</option>
                                    <option value="juwa">Juwa</option>
                                    <option value="orionstars">OrionStars</option>
                                    <option value="pandamaster">PandaMaster</option>
                                    <option value="ultrapanda">UltraPanda</option>
                                    <option value="vblink">VBLink</option>
                                    <option value="river">River</option>
                                    <option value="milkyway">MilkyWay</option>
                                    <option value="juwa2">Juwa 2.0</option>
                                    <option value="goldentreasure">GoldenTreasure</option>
                                    <option value="yolo">Yolo</option>
                                    <option value="cashmachine">Cash Machine</option>
                                    <option value="cashfrenzy">Cash Frenzy</option>
                                </select>
                            </div>
                        </div>

                        <table id="datatable-basic" class="table table-bordered text-nowrap w-100">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>User ID</th>
                                <th>Description</th>
                                <th>task ID</th>
                                <th>Status</th>
                                <th>Duration</th>
                                <th>Data</th>
                                <th>Backend</th>
                                <th>Order ID</th>
                                <th>Screenshot</th>
                                <th>Created</th>
                                <th>Updated</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--End::row-1 -->
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            const table = $('#datatable-basic').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('tasks.data') }}",
                pageLength: 10,
                ordering: false,
                columns: [
                    { data: 'id' },
                    { data: 'user_id' },
                    { data: 'description' },
                    { data: 'task_id' },
                    { data: 'status' },
                    { data: 'duration_seconds' },
                    { data: 'data_rendered', orderable: false, searchable: false },
                    { data: 'backend' },
                    { data: 'order_id' },
                    { data: 'screenshot' },
                    { data: 'created_at' },
                    { data: 'updated_at' },
                    { data: 'action', orderable: false, searchable: false }
                ]
            });

            // Filters
            $('#statusFilter').on('change', function () {
                table.column(4).search($(this).val()).draw();
            });

            $('#backendFilter').on('change', function () {
                table.column(7).search($(this).val()).draw();
            });

            $('#datatable-basic').on('draw.dt', function () {
                $('[data-bs-toggle="tooltip"]').tooltip();
            });

        });
    </script>

@endpush
