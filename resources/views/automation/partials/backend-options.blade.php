{{-- <option>s for a backend filter, from backend_games. $backends = BackendGames::options() --}}
<option value="">All</option>
@foreach($backends as $backend)
    <option value="{{ $backend->name }}" {{ ($selected ?? '') === $backend->name ? 'selected' : '' }}>
        {{ $backend->name }}{{ $backend->status ? '' : ' (disabled)' }}
    </option>
@endforeach
