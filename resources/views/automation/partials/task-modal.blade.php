{{--
    Task detail modal, shared by the tasks and requests tables.
    Fill it with showTaskDetail(detail) where `detail` is the raw array
    TaskController::detail() / RequestController produce for a row.
--}}
<div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title font-monospace" id="taskModalLabel">Task</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div><strong>ID:</strong> <span id="t-id">—</span></div>
                        <div><strong>User ID:</strong> <span id="t-user">—</span></div>
                        <div><strong>Backend:</strong> <span id="t-backend">—</span></div>
                        <div><strong>Type:</strong> <span id="t-type">—</span></div>
                        <div><strong>Status:</strong> <span id="t-status" class="badge bg-secondary">—</span></div>
                        <div><strong>Duration (s):</strong> <span id="t-duration">—</span></div>
                    </div>
                    <div class="col-md-6">
                        <div><strong>Order ID:</strong> <span id="t-order">—</span></div>
                        <div><strong>Requested:</strong> <span id="t-requested">—</span></div>
                        <div><strong>Created:</strong> <span id="t-created">—</span></div>
                        <div><strong>Updated:</strong> <span id="t-updated">—</span></div>
                        <div><strong>Screenshot:</strong> <a id="t-shot" href="#" target="_blank" rel="noopener">—</a></div>
                        <div class="mt-2"><a href="#" id="t-logs" class="btn btn-sm btn-primary">View logs</a></div>
                    </div>
                </div>
                <div class="mt-3">
                    <strong>Description</strong>
                    <pre class="mb-0 small text-wrap" id="t-desc">—</pre>
                </div>
                <hr>
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>Request payload</strong>
                        <pre class="mb-0 small"><code id="t-payload">{}</code></pre>
                    </div>
                    <div class="col-md-6">
                        <strong>Result data</strong>
                        <pre class="mb-0 small"><code id="t-data">{}</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // `task` is TaskDetail::json() — a JSON string in the row data.
        window.showTaskDetail = function (task) {
            if (typeof task === 'string') {
                try { task = JSON.parse(task); } catch (e) { task = {}; }
            }
            task = task || {};
            const text = (v) => (v === null || v === undefined || v === '') ? '—' : v;
            const json = (v) => {
                if (v === null || v === undefined) return '—';
                if (typeof v === 'string') {
                    try { return JSON.stringify(JSON.parse(v), null, 2); } catch (e) { return v; }
                }
                return JSON.stringify(v, null, 2);
            };

            $('#taskModalLabel').text(task.task_id ? `Task ${task.task_id}` : 'Task');
            $('#t-id').text(text(task.id));
            $('#t-user').text(text(task.user_id));
            $('#t-backend').text(text(task.backend));
            $('#t-type').text(text(task.request?.type));
            $('#t-duration').text(text(task.duration_seconds));
            $('#t-order').text(text(task.order_id));
            $('#t-requested').text(text(task.request?.created_at));
            $('#t-created').text(text(task.created_at));
            $('#t-updated').text(text(task.updated_at));
            $('#t-desc').text(text(task.description));

            const status = task.status || 'no result';
            const cls = {success: 'bg-success', finished: 'bg-success', failed: 'bg-danger', pending: 'bg-warning'}[status] || 'bg-secondary';
            $('#t-status').removeClass('bg-success bg-danger bg-warning bg-secondary').addClass(cls).text(status);

            $('#t-logs').attr('href', task.logs_url || '#');

            if (task.screenshot_url) {
                $('#t-shot').attr('href', task.screenshot_url).text('View');
            } else {
                $('#t-shot').attr('href', '#').text('—');
            }

            $('#t-payload').text(json(task.request?.payload));
            $('#t-data').text(json(task.data));

            bootstrap.Modal.getOrCreateInstance(document.getElementById('taskModal')).show();
        };
    </script>
@endpush
