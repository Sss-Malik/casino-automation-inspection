<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\Models\Logs;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    public function index($taskId = null)
    {
        $logs = Logs::with('backend', 'automationResult')
            ->when($taskId, function ($query, $taskId) {
                return $query->where('task_id', $taskId);
            })
            ->orderByDesc('created_at')
            ->get();
        return view('automation.logs.index', compact('logs'));
    }
}
