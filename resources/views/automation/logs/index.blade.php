@extends('layouts.main')

@section('title', 'Logs')


@section('content')
    <!-- Start::row-1 -->
    <div class="row mt-5">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        Automation Logs
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <div class="row mb-3">
                            <div class="col-md-2">
                                <label for="typeFilter">Filter by Type</label>
                                <select id="typeFilter" class="form-control">
                                    <option value="">All</option>
                                    <option value="info">Info</option>
                                    <option value="error">Error</option>
                                    <option value="warning">Warning</option>
                                    <option value="debug">Debug</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="backendFilter">Filter by Backend</label>
                                <select id="backendFilter" class="form-control">
                                    @include('automation.partials.backend-options')
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label for="taskFilter">Task ID</label>
                                <div class="input-group">
                                    <input type="text" id="taskFilter" class="form-control font-monospace"
                                           placeholder="paste a task id to see only its log lines"
                                           value="{{ $taskId ?? '' }}">
                                    <button type="button" id="taskFilterClear" class="btn btn-light">Clear</button>
                                </div>
                            </div>
                        </div>

                        <table id="datatable-basic" class="table table-bordered text-nowrap w-100">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Source</th>
                                <th>Backend</th>
                                <th>Task ID</th>
                                <th>Created</th>
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
            const taskFilter = $('#taskFilter');

            const table = $('#datatable-basic').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('logs.data') }}",
                    data: function (d) {
                        d.task_id = taskFilter.val().trim();
                    }
                },
                language: {
                    searchPlaceholder: 'Search...',
                    sSearch: '',
                },
                pageLength: 25,
                ordering: false,
                searchDelay: 500,
                columns: [
                    { data: 'id' },
                    { data: 'type' },
                    { data: 'description' },
                    { data: 'source_url' },
                    { data: 'backend' },
                    { data: 'task_id', className: 'font-monospace' },
                    { data: 'created_at', searchable: false }
                ]
            });

            $('#typeFilter').on('change', function () {
                table.column(1).search($(this).val()).draw();
            });

            $('#backendFilter').on('change', function () {
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
        });
    </script>
@endpush
