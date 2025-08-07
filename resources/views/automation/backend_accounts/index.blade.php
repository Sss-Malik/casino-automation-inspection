@extends('layouts.main')

@section('title', 'Accounts')

@section('content')
    <div class="row mb-4 mt-5">
        {{-- Summary cards --}}
        <div class="col-md-3">
            <div class="card custom-card text-center p-3">
                <h6>Total Backends</h6>
                <h2>
                    {{-- count distinct backend games --}}
                    {{ $backendAccounts->pluck('backendGame.id')->unique()->count() }}
                </h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card custom-card text-center p-3">
                <h6>Total Accounts</h6>
                <h2>{{ $backendAccounts->count() }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card custom-card text-center p-3 text-white">
                <h6>Assigned Accounts</h6>
                <h2>{{ $backendAccounts->whereNotNull('user_id')->count() }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card custom-card text-center p-3 bg-warning-transparent text-white">
                <h6>Unassigned Accounts</h6>
                <h2>{{ $backendAccounts->whereNull('user_id')->count() }}</h2>
            </div>
        </div>
    </div>

    {{-- Per-backend breakdown --}}
    <div class="row">
        <div class="col-12">
            <div class="card custom-card">
                <div class="card-header">
                    <h5>Backend Account Stats</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th>Backend</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Assigned</th>
                            <th class="text-center">Unassigned</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($backendAccounts->groupBy('backendGame.id') as $backendId => $accounts)
                            @php
                                $backend = $accounts->first()->backendGame;
                                $total   = $accounts->count();
                                $assigned   = $accounts->whereNotNull('user_id')->count();
                                $unassigned = $accounts->whereNull('user_id')->count();
                            @endphp
                            <tr>
                                <td>{{ $backend->name }}</td>
                                <td class="text-center">{{ $total }}</td>
                                <td class="text-center">{{ $assigned }}</td>
                                <td class="text-center">{{ $unassigned }}</td>
                                <td class="text-center">
                                    <a
                                        href="{{ route('backend.accounts.create', ['backendId' => $backend->id]) }}"
                                        class="btn btn-sm btn-primary"
                                    >
                                        Create more
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @if(session('response'))
        <div class="mt-4">
            <h5>Results:</h5>
            @php
                $resp = session('response');
            @endphp

            <div class="alert alert-{{ $resp['status'] == 200 ? 'success' : 'danger' }}">
                <strong>Status:</strong> {{ $resp['status'] }}<br>
                <pre>{{ json_encode($resp['body'], JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    @endif

@endsection

@section('js')
@endsection
