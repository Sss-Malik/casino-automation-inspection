<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\Models\AutomationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Facades\DataTables;

class RequestController extends Controller
{

    protected $statusMap = [
        'pending' => 'bg-warning',
        'success' => 'bg-success',
        'failed'  => 'bg-danger',
    ];

    public function index()
    {
        // backends + endpoints for the form
        $backends = [
            'gamevault','juwa','pandamaster','ultrapanda',
            'orionstars','gameroom','vblink','milkyway','firekirin', 'river'
        ];
        $endpoints = [
            'read-account'     => ['account_id'],
            'read-backend'     => [],
            'create-account'   => [],
            'recharge-account' => ['account_id','count', 'order_id', 'amount_to_deduct'],
            'withdraw-account' => ['account_id','count', 'redeem_id'],
            'freeplay-account' => ['account_id','type', 'freeplay_id'],
            'reset-password' => ['account_id'],
            'read-account-user' => ['account_id']
        ];

        return view('automation.requests.index', compact('backends','endpoints'));
    }

    public function send(Request $request)
    {


        $data = $request->validate([
            'endpoint'   => 'required|in:'.implode(',', array_keys($this->endpoints())),
            'backend'    => 'required|in:'.implode(',', $this->backends()),
            'account_id' => 'sometimes|string',
            'count'      => 'sometimes|integer|min:1',
            'type'       => 'sometimes|string',
            'repeat'     => 'required|integer|min:1',
            'order_id' => 'sometimes|string',
            'redeem_id' => 'sometimes',
            'amount_to_deduct' => 'sometimes',
            'freeplay_id' => 'sometimes'
        ]);

        $apiBase = config('services.casino_automation.base_url');
        $appKey   = config('services.casino_automation.app_key');    // <-- load from config/services.php

        $responses = [];
        for ($i = 0; $i < $data['repeat']; $i++) {
            // build JSON payload
            $body = ['backend' => $data['backend']];
            foreach (['account_id','count','type', 'redeem_id', 'amount_to_deduct'] as $f) {
                if (!empty($data[$f])) {
                    $body[$f] = $data[$f];
                }
            }

            // prepare the HTTP client
            $client = Http::withHeaders([
                'Content-Type' => 'application/json',
            ]);

            if ($data['endpoint'] === 'recharge-account') {
                $client = $client->withHeaders([
                    'x-order-id' => $request->input('order_id')
                ]);
            }

            if (in_array($data['endpoint'], ['reset-password', 'read-account-user', 'recharge-account', 'freeplay-account'])) {
                $token = Auth::user()->tokens()->first()->token;
                $client->withHeaders([
                    'token' => $token
                ]);
            }

            // only add x-app-key for these two endpoints
            if (in_array($data['endpoint'], ['create-account','read-account', 'read-backend'])) {
                $client = $client->withHeaders([
                    'x-app-key' => $appKey,
                ]);
            }

            // fire it off
            $resp = $client->post("$apiBase/{$data['endpoint']}", $body);
            $responses[] = [
                'status' => $resp->status(),
                'body'   => $resp->json(),
            ];
        }

        return back()->with('responses', $responses);
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


    // helper getters so validation and view share the same lists
    private function backends()
    {
        return ['gamevault','juwa','pandamaster','ultrapanda','orionstars','gameroom','vblink','milkyway','firekirin', 'river'];
    }

    private function endpoints()
    {
        return [
            'read-account'     => ['account_id'],
            'read-backend'     => [],
            'create-account'   => [],
            'recharge-account' => ['account_id','count', 'amount_to_deduct'],
            'withdraw-account' => ['account_id','count', 'redeem_id'],
            'freeplay-account' => ['account_id','type', 'freeplay_id'],
            'reset-password' => ['account_id'],
            'read-account-user' => ['account_id']
        ];
    }
}
