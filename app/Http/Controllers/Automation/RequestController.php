<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
class RequestController extends Controller
{
    public function index()
    {
        // backends + endpoints for the form
        $backends = [
            'gamevault','juwa','pandamaster','ultrapanda',
            'orionstars','gameroom','vblink','milkyway','firekirin'
        ];
        $endpoints = [
            'read-account'     => ['account_id'],
            'create-account'   => [],
            'recharge-account' => ['account_id','count', 'order_id'],
            'withdraw-account' => ['account_id','count'],
            'freeplay-account' => ['account_id','type'],
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
            'order_id' => 'sometimes|string'
        ]);

        $apiBase = config('services.casino_automation.base_url');
        $appKey   = config('services.casino_automation.app_key');    // <-- load from config/services.php

        $responses = [];
        for ($i = 0; $i < $data['repeat']; $i++) {
            // build JSON payload
            $body = ['backend' => $data['backend']];
            foreach (['account_id','count','type'] as $f) {
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

            // only add x-app-key for these two endpoints
            if (in_array($data['endpoint'], ['create-account','read-account'])) {
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

    // helper getters so validation and view share the same lists
    private function backends()
    {
        return ['gamevault','juwa','pandamaster','ultrapanda','orionstars','gameroom','vblink','milkyway','firekirin'];
    }

    private function endpoints()
    {
        return [
            'read-account'     => ['account_id'],
            'create-account'   => [],                          // no extra fields
            'recharge-account' => ['account_id','count', 'order_id'],
            'withdraw-account' => ['account_id','count'],
            'freeplay-account' => ['account_id','type'],
        ];
    }
}
