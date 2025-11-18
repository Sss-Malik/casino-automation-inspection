<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\Models\AutomationResult;
use App\Models\BackendGames;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TaskController extends Controller
{
    protected $statusMap = [
        'pending' => 'bg-warning',
        'success' => 'bg-success',
        'failed'  => 'bg-danger',
    ];

    public function index() {
        $tasks = AutomationResult::with('backend', 'logs')->orderByDesc('created_at')
        ->get();
        return view('automation.task.index', compact('tasks'));
    }

    public function data()
    {
        $query = AutomationResult::with('backend')->orderByDesc('created_at');

        return DataTables::eloquent($query)
            ->filterColumn('backend', function ($query, $keyword) {
                $query->whereHas('backend', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%");
                });
            })
            ->addColumn('backend', fn($row) => $row->backend->name)
            ->addColumn('created_at', fn($row) => app()->environment('local') ? $row->created_at->timezone('Asia/Karachi')->format('F j, Y g:i A'): $row->created_at->format('F j, Y g:i A'))
            ->addColumn('updated_at', fn($row) => app()->environment('local') ? $row->updated_at->timezone('Asia/Karachi')->format('F j, Y g:i A'): $row->updated_at->format('F j, Y g:i A'))
            ->addColumn('data_rendered', function ($row) {
                if (!$row->data) return 'N/A';
                $html = "<ul>";
                foreach ($row->data as $k => $v) $html .= "<li>$k: $v</li>";
                return $html . "</ul>";
            })
            ->addColumn('screenshot', function ($row) {
                return $row->screenshot_url
                    ? '<a href="'.$row->screenshot_url.'" target="_blank">View</a>'
                    : 'N/A';
            })
            ->addColumn('action', fn($row) =>
                '<a href="'.route('logs.index', ['taskId' => $row->task_id]).'">
                <button class="btn btn-sm btn-primary">View logs</button>
            </a>'
            )
            ->addColumn('status', function ($row) {
                return '<span class="badge text-white fs-12 '. $this->statusMap[$row->status] . '">' . $row->status . '</span>';
            })
            ->addColumn('description', function ($row) {
                $full = e($row->description ?? '');
                $truncated = \Illuminate\Support\Str::limit($row->description, 40);

                return '
                <span class="desc-tooltip"
                      data-bs-toggle="tooltip"
                      title="' . nl2br($full) . '">
                      ' . e($truncated) . '
                </span>';
            })
            ->rawColumns(['data_rendered','screenshot','action', 'description', 'status'])
            ->make(true);
    }
}
