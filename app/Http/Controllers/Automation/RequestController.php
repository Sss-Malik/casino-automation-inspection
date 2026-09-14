<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\Models\AutomationRequest;
use App\Models\BackendGames;
use App\Support\Format;
use App\Support\TaskDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class RequestController extends Controller
{

    protected $statusMap = [
        'pending'  => 'bg-warning',
        'success'  => 'bg-success',
        'finished' => 'bg-success',
        'failed'   => 'bg-danger',
    ];

    /**
     * The rails a developer can exercise from the panel. All three authenticate
     * with the shared app key and take nothing but a backend (and an account
     * name). The other automation endpoints (recharge, freeplay, withdraw,
     * reset-password, read-account-user) need a player's Sanctum token plus an
     * order / freeplay / redeem row, so they are only meaningful from the game.
     */
    public const ENDPOINTS = [
        'read-account'   => ['account_id'],
        'read-backend'   => [],
        'create-account' => [],
    ];

    /** Upper bound on one form submission, so a typo cannot flood a backend. */
    public const MAX_REPEAT = 20;

    public function index()
    {
        $backends = BackendGames::options();
        $endpoints = self::ENDPOINTS;

        return view('automation.requests.index', compact('backends', 'endpoints'));
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'endpoint'   => ['required', Rule::in(array_keys(self::ENDPOINTS))],
            'backend'    => ['required', Rule::exists('backend_games', 'name')->whereNull('deleted_at')],
            'account_id' => ['required_if:endpoint,read-account', 'nullable', 'string', 'max:255'],
            'repeat'     => ['required', 'integer', 'min:1', 'max:'.self::MAX_REPEAT],
        ]);

        $apiBase = config('services.casino_automation.base_url');
        $appKey  = config('services.casino_automation.app_key');

        $body = ['backend' => $data['backend']];
        foreach (self::ENDPOINTS[$data['endpoint']] as $field) {
            $body[$field] = $data[$field];
        }

        $responses = [];
        for ($i = 0; $i < $data['repeat']; $i++) {
            $resp = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-app-key' => $appKey,
            ])->post("$apiBase/{$data['endpoint']}", $body);

            $responses[] = [
                'status' => $resp->status(),
                'body'   => $resp->json(),
            ];
        }

        return back()->with('responses', $responses)->withInput();
    }


    public function view(Request $request)
    {
        $backends = BackendGames::options();

        return view('automation.requests.view', compact('backends'));
    }

    public function data(Request $request)
    {
        // Ordered by id: monotonic with created_at and the only indexed column.
        $query = AutomationRequest::with('result.backend')->orderByDesc('id');

        return DataTables::eloquent($query)
            ->filterColumn('type_badge', fn ($query, $keyword) => $query->where('type', $keyword))
            ->filterColumn('backend', function ($query, $keyword) {
                $query->whereHas('result.backend', fn ($q) => $q->where('name', 'LIKE', "%{$keyword}%"));
            })
            ->filterColumn('payload', fn ($query, $keyword) => $query->where('payload', 'LIKE', "%{$keyword}%"))
            ->editColumn('task_id', function ($req) {
                return '<button type="button" class="btn btn-link p-0 view-task font-monospace" title="View task">'
                    .e($req->task_id).'</button>';
            })
            ->addColumn('type_badge', function ($req) {
                $typeClass = [
                    'create'   => 'bg-primary',
                    'recharge' => 'bg-success',
                    'freeplay' => 'bg-info',
                    'withdraw' => 'bg-warning',
                    'read'     => 'bg-secondary',
                ];
                $class = $typeClass[$req->type] ?? 'bg-secondary';

                return "<span class='badge $class text-white fs-10'>".e($req->type)."</span>";
            })
            ->addColumn('status_badge', function ($req) {
                $result = $req->result;
                $class = $result ? ($this->statusMap[$result->status] ?? 'bg-secondary') : 'bg-secondary';

                return "<span class='badge $class text-white fs-10'>".e($result?->status ?? 'no result')."</span>"
                    .($req->status_code ? " <span class='badge bg-dark text-white fs-10'>".e($req->status_code).'</span>' : '');
            })
            ->addColumn('backend', fn ($req) => $req->result?->backend?->name ?? '')
            ->addColumn('payload', fn ($req) => TaskDetail::payloadCell($req->payload))
            ->addColumn('detail', fn ($req) => TaskDetail::make($req->result, $req))
            ->addColumn('created_fmt', fn ($req) => Format::dateTime($req->created_at))
            ->addColumn('updated_fmt', fn ($req) => Format::dateTime($req->updated_at))
            ->rawColumns(['task_id', 'type_badge', 'status_badge', 'payload'])
            ->make(true);
    }
}
