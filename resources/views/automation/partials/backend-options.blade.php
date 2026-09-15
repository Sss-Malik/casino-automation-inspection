{{-- <option>s for a backend filter, from backend_games. $backends = BackendGames::options().
     The value is the id: filters compare backend_id directly, so "juwa" can never match "juwa2". --}}
<option value="">All</option>
@foreach($backends as $backend)
    <option value="{{ $backend->id }}">
        {{ $backend->name }}{{ $backend->status ? '' : ' (disabled)' }}
    </option>
@endforeach
