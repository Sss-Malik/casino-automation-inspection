@extends('layouts.main')

@section('title', 'Requests')

@section('content')
    <div class="row mt-5">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('request.send') }}">
                    @csrf

                    {{-- 1) Endpoint selector --}}
                    <div class="mb-3">
                        <label class="form-label">Endpoint</label>
                        <select id="endpoint" name="endpoint" class="form-select" required>
                            <option value="">— select —</option>
                            @foreach($endpoints as $key => $fields)
                                <option value="{{ $key }}" {{ old('endpoint') == $key ? 'selected' : '' }}>
                                    {{ $key }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 2) Backend selector + hidden mirror --}}
                    <div class="mb-3">
                        <label class="form-label">Backend</label>
                        <select id="backend" class="form-select" required>
                            <option value="">— select —</option>
                            @foreach($backends as $b)
                                <option value="{{ $b }}" {{ old('backend') == $b ? 'selected' : '' }}>
                                    {{ $b }}
                                </option>
                            @endforeach
                        </select>
                        {{-- this actually submits the backend field --}}
                        <input type="hidden" name="backend" id="backend-input" value="{{ old('backend') }}">
                    </div>

                    {{-- 3) Endpoint-specific payload fields --}}
                    <div id="payload-fields"></div>

                    {{-- 4) Repeat count --}}
                    <div class="mb-3">
                        <label class="form-label">Repeat Requests</label>
                        <input type="number"
                               name="repeat"
                               class="form-control"
                               min="1"
                               value="{{ old('repeat',1) }}"
                               required>
                    </div>

                    <button class="btn btn-primary">Send</button>
                </form>

            </div>
        </div>
    </div>
    {{-- show results --}}
    @if(session('responses'))
        <div class="mt-4">
            <h5>Results:</h5>
            @foreach(session('responses') as $i => $resp)
                <div class="alert alert-{{ $resp['status'] == 200 ? 'success' : 'danger' }}">
                    <strong>Request #{{ $i+1 }}</strong><br>
                    Status: {{ $resp['status'] }}<br>
                    <pre>{{ json_encode($resp['body'], JSON_PRETTY_PRINT) }}</pre>
                </div>
            @endforeach
        </div>
    @endif
@endsection

@section('js')
    <script>
        // server-passed maps
        const endpointMap  = @json($endpoints);
        const backendSel   = document.getElementById('backend');
        const backendInput = document.getElementById('backend-input');
        const endpointSel  = document.getElementById('endpoint');
        const payloadDiv   = document.getElementById('payload-fields');

        // 1) Always mirror the dropdown into the hidden input
        function syncBackend() {
            backendInput.value = backendSel.value;
        }
        backendSel.addEventListener('change', syncBackend);
        // initialize on load
        syncBackend();

        // 2) Render only the fields for the chosen endpoint
        function renderFields() {
            payloadDiv.innerHTML = '';
            const ep = endpointSel.value;
            if (!ep || !endpointMap[ep]) return;

            endpointMap[ep].forEach(field => {
                const wrapper = document.createElement('div');
                wrapper.classList.add('mb-3');

                const label = document.createElement('label');
                label.classList.add('form-label');
                label.textContent = field.replace('_',' ').toUpperCase();

                const input = document.createElement('input');
                input.name     = field;
                input.classList.add('form-control');
                input.required = true;
                input.type     = (field === 'count') ? 'number' : 'text';

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

