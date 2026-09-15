@extends('layouts.main')

@section('title', 'Requests')

@section('content')
    <div class="row mt-5">
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Make a request</div>
                </div>
                <div class="card-body">
                    <p class="text-muted fs-12 mb-3">
                        Developer rails only — these authenticate with the shared app key and need no player,
                        order or freeplay row. Player actions (recharge, freeplay, withdraw, reset-password) can
                        only be started from the game itself.
                    </p>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('request.send') }}">
                        @csrf

                        {{-- 1) Endpoint selector --}}
                        <div class="mb-3">
                            <label class="form-label" for="endpoint">Endpoint</label>
                            <select id="endpoint" name="endpoint" class="form-select" required>
                                <option value="">— select —</option>
                                @foreach($endpoints as $key => $fields)
                                    <option value="{{ $key }}" {{ old('endpoint') == $key ? 'selected' : '' }}>
                                        {{ $key }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 2) Backend selector --}}
                        <div class="mb-3">
                            <label class="form-label" for="backend">Backend</label>
                            <select id="backend" name="backend" class="form-select" required>
                                <option value="">— select —</option>
                                @foreach($backends as $b)
                                    <option value="{{ $b->name }}" {{ old('backend') == $b->name ? 'selected' : '' }}>
                                        {{ $b->name }}{{ $b->status ? '' : ' (disabled)' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 3) Endpoint-specific payload fields --}}
                        <div id="payload-fields"></div>

                        {{-- 4) Repeat count --}}
                        <div class="mb-3">
                            <label class="form-label" for="repeat">Repeat Requests</label>
                            <input type="number"
                                   id="repeat"
                                   name="repeat"
                                   class="form-control"
                                   min="1"
                                   max="{{ \App\Http\Controllers\Automation\RequestController::MAX_REPEAT }}"
                                   value="{{ old('repeat',1) }}"
                                   required>
                            <div class="form-text">create-account with a repeat of N stocks N pool accounts.</div>
                        </div>

                        <button class="btn btn-primary">Send</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            @if(session('responses'))
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">Results</div>
                    </div>
                    <div class="card-body">
                        @foreach(session('responses') as $i => $resp)
                            <div class="alert alert-{{ $resp['status'] == 200 ? 'success' : 'danger' }}">
                                <strong>Request #{{ $i+1 }}</strong> — HTTP {{ $resp['status'] }}
                                @if(!empty($resp['body']['task_id']))
                                    · <a href="{{ route('logs.index', ['taskId' => $resp['body']['task_id']]) }}">logs</a>
                                    · <a href="{{ route('tasks.index', ['task_id' => $resp['body']['task_id']]) }}">task</a>
                                @endif
                                <pre class="mb-0 mt-2 small" style="white-space: pre-wrap; word-break: break-all;">{{ json_encode($resp['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('js')
    <script>
        // server-passed maps
        const endpointMap  = @json($endpoints);
        const oldValues    = @json(old());
        const endpointSel  = document.getElementById('endpoint');
        const payloadDiv   = document.getElementById('payload-fields');

        // Render only the fields for the chosen endpoint
        function renderFields() {
            payloadDiv.innerHTML = '';
            const ep = endpointSel.value;
            if (!ep || !endpointMap[ep]) return;

            endpointMap[ep].forEach(field => {
                const wrapper = document.createElement('div');
                wrapper.classList.add('mb-3');

                const label = document.createElement('label');
                label.classList.add('form-label');
                label.htmlFor = field;
                label.textContent = field.replace('_',' ').toUpperCase();

                const input = document.createElement('input');
                input.id       = field;
                input.name     = field;
                input.classList.add('form-control');
                input.required = true;
                input.type     = 'text';
                input.value    = oldValues[field] ?? '';

                wrapper.append(label, input);
                payloadDiv.append(wrapper);
            });
        }
        endpointSel.addEventListener('change', renderFields);

        window.addEventListener('DOMContentLoaded', () => {
            renderFields();
        });
    </script>
@endsection
