<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\Models\BackendAccounts;
use App\Models\BackendGames;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BackendAccountController extends Controller
{
    public function index() {
        $backendAccounts = BackendAccounts::with('user', 'backendGame')->get();
        return view('automation.backend_accounts.index', compact('backendAccounts'));
    }

    public function view($backendId = null) {
        $query = BackendAccounts::with(['user', 'backendGame']);

        if ($backendId) {
            $query->where('backend_id', $backendId);
        }

        $accounts = $query->get();

        return view('automation.backend_accounts.view', compact('accounts'));
    }

    public function createMore($backendId) {
        $backend = BackendGames::findOrFail($backendId);
        $body = ['backend' => $backend->name];

        $appKey = config('services.casino_automation.app_key');
        $apiBase = config('services.casino_automation.base_url');


        $client = Http::withHeaders([
            'Content-Type' => 'application/json',
            'x-app-key' => $appKey
        ]);



        $resp = $client->post("$apiBase/create-account", $body);
        return back()->with('response', ['status' => $resp->status(), 'body' => $resp->json()]);
    }

}
