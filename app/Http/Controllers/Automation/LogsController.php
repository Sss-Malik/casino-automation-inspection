<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\Models\Logs;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    public function index() {
        $logs = Logs::with('backend')->get()
            ->sortByDesc('created_at');
        return view('automation.logs.index', compact('logs'));
    }
}
