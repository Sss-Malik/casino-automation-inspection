@extends('layouts.main')

@section('title', 'Tasks')


@section('content')
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
                            <div class="col-md-2">
                                <label for="statusFilter">Filter by Status</label>
                                <select id="statusFilter" class="form-control">
                                    <option value="">All</option>
                                    <option value="pending">Pending</option>
                                    <option value="success">Success</option>
                                    <option value="failed">Failed</option>
                                    <option value="finished">Finished</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="backendFilter">Filter by Backend</label>
                                <select id="backendFilter" class="form-control">
                                    @include('automation.partials.backend-options')
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="typeFilter">Filter by Type</label>
                                <select id="typeFilter" class="form-control">
                                    <option value="">All</option>
                                    @foreach (['create', 'recharge', 'freeplay', 'withdraw', 'read', 'reset-password', 'read-backend'] as $type)
                                        <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label for="taskFilter">Task ID</label>
                                <div class="input-group">
                                    <input type="text" id="taskFilter" class="form-control font-monospace"
                                           placeholder="paste a task id to see only that task"
                                           value="{{ request()->query('task_id', '') }}">
                                    <button type="button" id="taskFilterClear" class="btn btn-light">Clear</button>
                                </div>
                            </div>
                        </div>

                        <table id="datatable-basic" class="table table-bordered text-nowrap w-100">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>User ID</th>
                                <th>Description</th>
                                <th>Task ID</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Duration</th>
                                <th>Payload</th>
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

    @include('automation.partials.task-modal')
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            const taskFilter = $('#taskFilter');

            const table = $('#datatable-basic').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('tasks.data') }}",
                    // ?task_id= (from the logs page and make-request results) is an
                    // exact, indexed lookup — kept out of the global LIKE search.
                    data: function (d) {
                        d.task_id = taskFilter.val().trim();
                    }
                },
                pageLength: 10,
                ordering: false,
                searchDelay: 500,
                columns: [
                    { data: 'id' },
                    { data: 'user_id' },
                    { data: 'description' },
                    { data: 'task_id', className: 'font-monospace' },
                    { data: 'type' },
                    { data: 'status' },
                    { data: 'duration_seconds', searchable: false },
                    { data: 'payload' },
                    { data: 'data_rendered', orderable: false, searchable: false },
                    { data: 'backend' },
                    { data: 'order_id' },
                    { data: 'screenshot', orderable: false, searchable: false },
                    { data: 'created_at', searchable: false },
                    { data: 'updated_at', searchable: false },
                    { data: 'action', orderable: false, searchable: false }
                ]
            });

            // Filters (column indexes match the `columns` list above)
            $('#statusFilter').on('change', function () {
                table.column(5).search($(this).val()).draw();
            });

            $('#backendFilter').on('change', function () {
                table.column(9).search($(this).val()).draw();
            });

            $('#typeFilter').on('change', function () {
                table.column(4).search($(this).val()).draw();
            });

            let taskTimer;
            taskFilter.on('input', function () {
                clearTimeout(taskTimer);
                taskTimer = setTimeout(() => table.draw(), 300);
            });

            $('#taskFilterClear').on('click', function () {
                taskFilter.val('');
                table.draw();
            });

            $('#datatable-basic').on('draw.dt', function () {
                $('[data-bs-toggle="tooltip"]').tooltip();
            });

            // Details button and payload cell both open the task modal
            $('#datatable-basic').on('click', '.view-task-row, .payload-cell', function () {
                const row = table.row($(this).closest('tr')).data();
                if (row) showTaskDetail(row.detail);
            });
        });
    </script>

@endpush
