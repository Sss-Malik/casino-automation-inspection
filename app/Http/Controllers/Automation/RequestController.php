<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\Models\AutomationRequest;
use App\Models\BackendGames;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class RequestController extends Controller
{

    protected $statusMap = [
        'pending' => 'bg-warning',
        'success' => 'bg-success',
        'failed'  => 'bg-danger',
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
        return view('automation.requests.view');
    }

    public function data(Request $request)
    {
        $query = AutomationRequest::with('result.backend')->latest('created_at');

        return DataTables::eloquent($query)
            ->addColumn('task_button', function ($req) {
                return view('automation.requests.partials.task-button', compact('req'))->render();
            })
            ->addColumn('type_badge', function ($req) {
                $typeClass = [
                    'create' => 'bg-success',
                    'update' => 'bg-info',
                    'delete' => 'bg-danger',
                ];
                $class = $typeClass[$req->type] ?? 'bg-secondary';

                return "<span class='badge $class text-white fs-10'>" . ucfirst($req->type) . "</span>";
            })
            ->addColumn('status_badge', function ($req) {
                $code = $req->status_code;

                return "<span class='badge text-white fs-10'>"
                    . ($code ?? '—') . "</span>";
            })
            ->addColumn('payload_short', function ($req) {
                return '<code class="small d-inline-block text-wrap">' .
                    \Illuminate\Support\Str::limit(json_encode($req->payload, JSON_UNESCAPED_SLASHES), 120)
                    . '</code>';
            })
            ->addColumn('created_fmt', function ($req) {
                return app()->environment('local') ? $req->created_at->timezone('Asia/Karachi')->format('F j, Y g:i A'): $req->created_at->format('F j, Y g:i A');
            })
            ->addColumn('updated_fmt', function ($req) {
                return app()->environment('local') ? $req->updated_at->timezone('Asia/Karachi')->format('F j, Y g:i A'): $req->updated_at->format('F j, Y g:i A');
            })
            ->rawColumns(['task_button', 'type_badge', 'status_badge', 'payload_short'])
            ->make(true);
    }
}
