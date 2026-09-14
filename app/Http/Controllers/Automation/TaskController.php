<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\Models\AutomationResult;
use App\Models\BackendGames;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class TaskController extends Controller
{
    protected $statusMap = [
        'pending'  => 'bg-warning',
        'success'  => 'bg-success',
        'finished' => 'bg-success',
        'failed'   => 'bg-danger',
    ];

    public function index() {
        $backends = BackendGames::options();

        return view('automation.task.index', compact('backends'));
    }

    public function data()
    {
        // Ordered by id, not created_at: the two are monotonic together and
        // only id is indexed (350k+ rows, no created_at index).
        $query = AutomationResult::with('backend', 'request')->orderByDesc('id');

        return DataTables::eloquent($query)
            ->filterColumn('backend', function ($query, $keyword) {
                $query->whereHas('backend', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%");
                });
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('status', 'LIKE', "%{$keyword}%");
            })
            ->filterColumn('type', function ($query, $keyword) {
                $query->whereHas('request', fn ($q) => $q->where('type', $keyword));
            })
            ->filterColumn('payload', function ($query, $keyword) {
                $query->whereHas('request', fn ($q) => $q->where('payload', 'LIKE', "%{$keyword}%"));
            })
            ->addColumn('backend', fn($row) => $row->backend?->name ?? '')
            ->addColumn('type', fn ($row) => $row->request
                ? '<span class="badge bg-secondary text-white fs-10">'.e($row->request->type).'</span>'
                : '—')
            ->addColumn('payload', fn ($row) => $this->renderPayload($row->request?->payload))
            ->addColumn('detail', fn ($row) => $this->detail($row))
            ->addColumn('created_at', fn($row) => $this->formatDateTime($row->created_at))
            ->addColumn('updated_at', fn($row) => $this->formatDateTime($row->updated_at))
            ->addColumn('data_rendered', function ($row) {
                if (!$row->data) return 'N/A';
                $html = "<ul class='mb-0 ps-3'>";
                foreach ($row->data as $k => $v) {
                    $html .= '<li>'.e($k).': '.e(is_scalar($v) || $v === null ? (string) $v : json_encode($v)).'</li>';
                }
                return $html . "</ul>";
            })
            ->addColumn('screenshot', function ($row) {
                return $row->screenshot_url
                    ? '<a href="'.e($row->screenshot_url).'" target="_blank">View</a>'
                    : 'N/A';
            })
            ->addColumn('action', fn($row) =>
                '<button type="button" class="btn btn-sm btn-secondary view-task-row me-1">Details</button>'
                .'<a href="'.route('logs.index', ['taskId' => $row->task_id]).'">
                <button class="btn btn-sm btn-primary">View logs</button>
            </a>'
            )
            ->addColumn('status', function ($row) {
                $class = $this->statusMap[$row->status] ?? 'bg-secondary';

                return '<span class="badge text-white fs-12 '.$class.'">'.e($row->status).'</span>';
            })
            ->addColumn('description', function ($row) {
                $full = e($row->description ?? '');
                $truncated = Str::limit($row->description, 40);

                return '
                <span class="desc-tooltip"
                      data-bs-toggle="tooltip"
                      title="' . nl2br($full) . '">
                      ' . e($truncated) . '
                </span>';
            })
            ->rawColumns(['type', 'payload', 'data_rendered', 'screenshot', 'action', 'description', 'status'])
            ->make(true);
    }

    /**
     * Compact one-line JSON of the request payload for the table cell, with
     * the full JSON in a tooltip. `action` is dropped: the Type column has it.
     */
    private function renderPayload(?array $payload): string
    {
        if (! $payload) {
            return '—';
        }

        unset($payload['action']);
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);

        return '<code class="small payload-cell" data-bs-toggle="tooltip" title="'.e($json).'">'
            .e(Str::limit($json, 80))
            .'</code>';
    }

    /**
     * Everything the task-detail modal shows, as raw values (no HTML).
     */
    private function detail(AutomationResult $row): array
    {
        return [
            'id' => $row->id,
            'user_id' => $row->user_id,
            'description' => $row->description,
            'task_id' => $row->task_id,
            'status' => $row->status,
            'duration_seconds' => $row->duration_seconds,
            'data' => $row->data,
            'backend' => $row->backend?->name,
            'order_id' => $row->order_id,
            'screenshot_url' => $row->screenshot_url,
            'created_at' => $this->formatDateTime($row->created_at),
            'updated_at' => $this->formatDateTime($row->updated_at),
            'logs_url' => route('logs.index', ['taskId' => $row->task_id]),
            'request' => $row->request ? [
                'type' => $row->request->type,
                'payload' => $row->request->payload,
                'created_at' => $this->formatDateTime($row->request->created_at),
            ] : null,
        ];
    }
}
