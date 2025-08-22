@extends('layouts.main')

@section('title', 'Logs')


@section('content')
    @php
        $statusClass = [
            'info' => 'bg-info',
            'error' => 'bg-danger',
            'warning' => 'bg-warning'
        ];
    @endphp
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
                            <div class="col-md-3">
                                <label for="typeFilter">Filter by Type</label>
                                <select id="typeFilter" class="form-control">
                                    <option value="">All</option>
                                    <option value="info">Info</option>
                                    <option value="error">Error</option>
                                    <option value="warning">Warning</option>
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
                                <th>Type</th>
                                <th>Description</th>
                                <th>Source</th>
                                <th>Backend</th>
                                <th>Created</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td><span class="badge {{ $statusClass[$log->type] }} fs-10">{{ $log->type }}</span></td>
                                    <td>{{ $log->description }}</td>
                                    <td>{{ $log->source_url }}</td>
                                    <td>{{ $log->backend->name }}</td>
                                    <td>{{ $log->created_at->timezone('Asia/Karachi')->format('F j, Y g:i A') }}</td>
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
            $('#typeFilter').on('change', function () {
                const value = $(this).val();
                table.column(1).search(value).draw();
            });

            $('#backendFilter').on('change', function () {
                const value = $(this).val();
                table.column(4).search(value).draw();
            });
        });
    </script>

@endpush
