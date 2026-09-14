@extends('layouts.main')

@section('title', 'Requests')

@section('content')
    <div class="row mt-5">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Automation Requests</div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        {{-- Filters --}}
                        <div class="row mb-3">
                            <div class="col-md-2">
                                <label for="typeFilter" class="form-label mb-1">Filter by Type</label>
                                <select id="typeFilter" class="form-control">
                                    <option value="">All</option>
                                    @foreach (['create', 'recharge', 'freeplay', 'withdraw', 'read', 'reset-password', 'read-backend'] as $t)
                                        <option value="{{ $t }}">{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="backendFilter" class="form-label mb-1">Filter by Backend</label>
                                <select id="backendFilter" class="form-control">
                                    @include('automation.partials.backend-options')
                                </select>
                            </div>
                        </div>

                        <table id="datatable-requests" class="table table-bordered text-nowrap w-100">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Task ID</th>
                                <th>Type</th>
                                <th>Result</th>
                                <th>Backend</th>
                                <th>Payload</th>
                                <th>Created</th>
                                <th>Updated</th>
                            </tr>
                            </thead>
                        </table>
                    </div> <!-- table-responsive -->
                </div>
            </div>
        </div>
    </div>

    @include('automation.partials.task-modal')
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {

            const table = $('#datatable-requests').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('request.data') }}',
                ordering: false,
                pageLength: 10,
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'task_id', name: 'task_id' },
                    { data: 'type_badge', name: 'type_badge' },
                    { data: 'status_badge', name: 'status_badge', searchable: false },
                    { data: 'backend', name: 'backend' },
                    { data: 'payload', name: 'payload' },
                    { data: 'created_fmt', name: 'created_fmt', searchable: false },
                    { data: 'updated_fmt', name: 'updated_fmt', searchable: false },
                ],
                language: { searchPlaceholder: 'Search...', sSearch: '' }
            });

            // Filters (column indexes match the `columns` list above)
            $('#typeFilter').on('change', function () {
                table.column(2).search(this.value).draw();
            });

            $('#backendFilter').on('change', function () {
                table.column(4).search(this.value).draw();
            });

            $('#datatable-requests').on('draw.dt', function () {
                $('[data-bs-toggle="tooltip"]').tooltip();
            });

            // Task id and payload cell open the task modal with the full payload + result
            $('#datatable-requests').on('click', '.view-task, .payload-cell', function () {
                const row = table.row($(this).closest('tr')).data();
                if (row) showTaskDetail(row.detail);
            });
        });
    </script>
@endpush
