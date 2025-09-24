@extends('layouts.main')

@section('title', 'Accounts')

@section('content')
    <div class="row mb-4 mt-5">
        <div class="col-12">
            <div class="card custom-card shadow rounded-3">
                <div class="card-header">
                    <div class="card-title">
                       Backend accounts
                    </div>
                </div>
                <div class="card-body">
                    @if($accounts->count())
                        <table id="datatable-basic" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Password</th>
                                <th>User</th>
                                <th>Backend</th>
                                <th>Assigned</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($accounts as $account)
                                <tr>
                                    <td>{{ $account->id }}</td>
                                    <td>{{ $account->username }}</td>
                                    <td>{{ $account->password }}</td>
                                    <td>{{ $account->user?->name ?? 'N/A' }}</td>
                                    <td>{{ $account->backendGame?->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($account->is_assigned)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">No accounts found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
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
        });
    </script>

@endpush
