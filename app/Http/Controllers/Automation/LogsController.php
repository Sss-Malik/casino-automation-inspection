<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\Models\BackendGames;
use App\Models\Logs;
use App\Support\Format;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class LogsController extends Controller
{
    protected $typeMap = [
        'info'    => 'bg-info',
        'error'   => 'bg-danger',
        'warning' => 'bg-warning',
        'debug'   => 'bg-secondary',
    ];

    public function index($taskId = null)
    {
        $backends = BackendGames::options();

        return view('automation.logs.index', compact('backends', 'taskId'));
    }

    /**
     * Server-side rows for the logs table. The table holds 270k+ rows in
     * production, so nothing is loaded until DataTables asks for a page.
     */
    public function data(Request $request)
    {
        $query = Logs::with('backend')
            ->when($request->input('task_id'), fn ($q, $taskId) => $q->where('task_id', $taskId))
            ->orderByDesc('id');

        return DataTables::eloquent($query)
            ->filterColumn('backend', function ($query, $keyword) {
                if (ctype_digit($keyword)) {
                    $query->where('backend_id', (int) $keyword);
                }
            })
            ->filterColumn('type', fn ($query, $keyword) => $query->where('type', $keyword))
            ->addColumn('backend', fn ($row) => $row->backend?->name ?? '')
            ->editColumn('type', function ($row) {
                $class = $this->typeMap[$row->type] ?? 'bg-secondary';

                return '<span class="badge text-white fs-10 '.$class.'">'.e($row->type).'</span>';
            })
            ->editColumn('description', function ($row) {
                $full = e($row->description ?? '');

                return '<span class="desc-tooltip" data-bs-toggle="tooltip" data-bs-html="true" title="'.nl2br($full).'">'
                    .e(Str::limit($row->description, 60))
                    .'</span>';
            })
            ->editColumn('created_at', fn ($row) => Format::dateTime($row->created_at))
            ->rawColumns(['type', 'description'])
            ->make(true);
    }
}
