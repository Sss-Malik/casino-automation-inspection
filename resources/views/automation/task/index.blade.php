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
                                <th>Updated</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($tasks as $task)
                                <tr>
                                    <td>{{ $task->id }}</td>
                                    <td>{{ $task->user_id ?? 'N/A' }}</td>
                                    <td>{{ $task->description }}</td>
                                    <td>{{ $task->task_id }}</td>
                                    <td><span class="badge {{ $statusClass[$task->status] }} text-white fs-10">{{ $task->status }}</span></td>
                                    <td>{{ $task->duration_seconds }}</td>
                                    <td>
                                        @if(!empty($task->data))
                                            <ul>
                                                @foreach ($task->data as $key => $value)
                                                    <li>{{ $key }}: {{ $value }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $task->backend->name }}</td>
                                    <td>{{ $task->order_id ?? 'N/A' }}</td>
                                    <td>
                                        @if (!empty($task->screenshot_url))
                                            <a class="text-indigo" href="{{ $task->screenshot_url }}" target="_blank" rel="noopener noreferrer">View</a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $task->updated_at->timezone('Asia/Karachi')->format('F j, Y g:i A') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
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
                language: {
                    searchPlaceholder: 'Search...',
                    sSearch: '',
                },
                pageLength: 10,
                ordering: false
            });

            // Status filter
            $('#statusFilter').on('change', function () {
                const value = $(this).val();
                table.column(4).search(value).draw(); // 4th index = Status column
            });

            // Backend filter
            $('#backendFilter').on('change', function () {
                const value = $(this).val();
                table.column(6).search(value).draw(); // 6th index = Backend column
            });
        });
    </script>

@endpush
