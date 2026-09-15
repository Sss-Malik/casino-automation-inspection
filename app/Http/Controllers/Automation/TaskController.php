<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\Models\AutomationResult;
use App\Models\BackendGames;
use App\Support\Format;
use App\Support\TaskDetail;
use Illuminate\Http\Request;
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

    /**
     * Server-side rows. This runs against the live database (350k+ rows), so
     * every filter is either an indexed column predicate or a single-pass
     * subquery — never a per-row EXISTS, which MySQL 5.7 will not semi-join.
     */
    public function data(Request $request)
    {
        // Ordered by id, not created_at: the two are monotonic together and
        // only id is indexed. task_id is a unique-index point lookup, so it
        // arrives as its own parameter rather than through the LIKE search.
        $query = AutomationResult::with('backend', 'request')
            ->when($request->input('task_id'), fn ($q, $taskId) => $q->where('task_id', trim($taskId)))
            ->orderByDesc('id');

        return DataTables::eloquent($query)
            ->filterColumn('backend', function ($query, $keyword) {
                if (ctype_digit($keyword)) {
                    $query->where('backend_id', (int) $keyword);
                }
            })
            ->filterColumn('status', fn ($query, $keyword) => $query->where('status', $keyword))
            ->filterColumn('type', function ($query, $keyword) {
                $query->whereIn('task_id', fn ($q) => $q->select('task_id')->from('automation_requests')->where('type', $keyword));
            })
            ->filterColumn('payload', function ($query, $keyword) {
                $query->whereIn('task_id', fn ($q) => $q->select('task_id')->from('automation_requests')->where('payload', 'LIKE', "%{$keyword}%"));
            })
            ->addColumn('backend', fn($row) => $row->backend?->name ?? '')
            ->addColumn('type', fn ($row) => $row->request
                ? '<span class="badge bg-secondary text-white fs-10">'.e($row->request->type).'</span>'
                : '—')
            ->addColumn('payload', fn ($row) => TaskDetail::payloadCell($row->request?->payload))
            ->addColumn('detail', fn ($row) => TaskDetail::json($row, $row->request))
            ->addColumn('created_at', fn($row) => Format::dateTime($row->created_at))
            ->addColumn('updated_at', fn($row) => Format::dateTime($row->updated_at))
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
                      data-bs-html="true"
                      title="' . nl2br($full) . '">
                      ' . e($truncated) . '
                </span>';
            })
            ->rawColumns(['type', 'payload', 'detail', 'data_rendered', 'screenshot', 'action', 'description', 'status'])
            ->make(true);
    }
}
