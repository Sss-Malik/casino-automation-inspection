<button type="button"
        class="btn btn-link p-0 view-task"
        data-bs-toggle="modal"
        data-bs-target="#taskModal"
        data-task='@json($req->result)'
        data-request-id="{{ $req->id }}"
        title="View task">
    {{ $req->task_id }}
</button>
