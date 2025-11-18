@extends('layouts.main')

@section('title', 'Requests')

@section('content')
    @php
        $typeClass = [
            'create'   => 'bg-primary',
            'recharge' => 'bg-success',
            'freeplay' => 'bg-info',
            'withdraw' => 'bg-warning',
            'read'     => 'bg-secondary',
        ];

        $statusCodeClass = function ($code) {
            if (is_null($code)) return 'bg-secondary';
            if ($code >= 200 && $code < 300) return 'bg-success';
            if ($code >= 400 && $code < 500) return 'bg-warning';
            return 'bg-danger';
        };
    @endphp

    <div class="row mt-5">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title">Automation Requests</div>

                    {{-- Filters --}}
                    <div class="d-flex gap-3">
                        <div>
                            <label for="typeFilter" class="form-label mb-1">Filter by Type</label>
                            <select id="typeFilter" class="form-control">
                                <option value="">All</option>
                                @foreach (['create','recharge','freeplay','withdraw','read'] as $t)
                                    <option value="{{ ucfirst($t) }}">{{ ucfirst($t) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="statusCodeFilter" class="form-label mb-1">Filter by Status Code</label>
                            <select id="statusCodeFilter" class="form-control">
                                <option value="">All</option>
                                @foreach ([200,202,400,401,403,404,408,429,500,502,503] as $code)
                                    <option value="{{ $code }}">{{ $code }}</option>
                                @endforeach
                                <option value="—">—</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="datatable-requests" class="table table-bordered text-nowrap w-100">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Task ID</th>
                                <th>Type</th>
                                <th>Status Code</th>
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


    <div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="taskModalLabel">Task</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div><strong>ID:</strong> <span id="t-id">—</span></div>
                            <div><strong>User ID:</strong> <span id="t-user">—</span></div>
                            <div><strong>Description:</strong> <span id="t-desc">—</span></div>
                            <div><strong>Task ID:</strong> <span id="t-task">—</span></div>
                            <div><strong>Status:</strong> <span id="t-status" class="badge bg-secondary">—</span></div>
                            <div><strong>Duration (s):</strong> <span id="t-duration">—</span></div>
                        </div>
                        <div class="col-md-6">
                            <div><strong>Backend:</strong> <span id="t-backend">—</span></div>
                            <div><strong>Order ID:</strong> <span id="t-order">—</span></div>
                            <div><strong>Updated:</strong> <span id="t-updated">—</span></div>
                            <div><strong>Screenshot:</strong> <a id="t-shot" href="#" target="_blank" rel="noopener">—</a></div>
                            <div><strong>Logs:</strong> <a href="#" id="t-logs"> <button class="btn btn-sm btn-primary">View logs</button></a></div>
                        </div>
                    </div>
                    <hr>
                    <div>
                        <strong>Data (JSON)</strong>
                        <pre class="mb-0 small"><code id="t-data">{}</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>


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
                    { data: 'task_button', name: 'task_button', orderable: false, searchable: false },
                    { data: 'type_badge', name: 'type', orderable: false },
                    { data: 'status_badge', name: 'status_code', orderable: false },
                    { data: 'payload_short', name: 'payload' },
                    { data: 'created_fmt', name: 'created_at' },
                    { data: 'updated_fmt', name: 'updated_at' },
                ],
                language: { searchPlaceholder: 'Search...', sSearch: '' }
            });

            // Dropdown filter: Type
            $('#typeFilter').on('change', function () {
                table.column(2).search(this.value).draw();
            });

            // Dropdown filter: Status
            $('#statusCodeFilter').on('change', function () {
                table.column(3).search(this.value).draw();
            });
        });

    </script>

    <script>
        $(function () {
            // Handle click on Task ID
            $(document).on('click', '.view-task', function () {
                const task = $(this).data('task') || null;

                // Title
                $('#taskModalLabel').text(task?.task_id ? `Task ${task.task_id}` : 'Task');

                // Simple fields
                $('#t-id').text(task?.id ?? '—');
                $('#t-user').text(task?.user_id ?? 'N/A');
                $('#t-desc').text(task?.description ?? '—');
                $('#t-task').text(task?.task_id ?? '—');

                // Status badge color
                const status = task?.status ?? 'pending';
                const cls = status === 'success' ? 'bg-success' : (status === 'failed' ? 'bg-danger' : 'bg-warning');
                $('#t-status').removeClass('bg-success bg-danger bg-warning bg-secondary').addClass(cls).text(status);

                $('#t-duration').text(task?.duration_seconds ?? '—');
                $('#t-backend').text(task?.backend?.name ?? '—');
                $('#t-order').text(task?.order_id ?? '—');

                // Timestamps may be strings; show raw
                $('#t-updated').text(task?.updated_at ?? '—');

                $('#t-logs').attr('href',
                    task?.task_id
                        ? "{{ route('logs.index') }}/" + task.task_id
                        : "{{ route('logs.index') }}"
                );

                // Screenshot link
                if (task?.screenshot_url) {
                    $('#t-shot').attr('href', task.screenshot_url).text('View');
                } else {
                    $('#t-shot').attr('href', '#').text('—');
                }

                // Data JSON pretty
                let dataBlock = '{}';
                try {
                    const d = task?.data;
                    dataBlock = typeof d === 'string' ? d : JSON.stringify(d ?? {}, null, 2);
                } catch (e) {}
                $('#t-data').text(dataBlock);
            });
        });
    </script>
@endpush
