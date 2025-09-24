<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\Models\AutomationResult;
use App\Models\BackendGames;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index() {
        $tasks = AutomationResult::with('backend', 'logs')->orderByDesc('created_at')
        ->get();
        return view('automation.task.index', compact('tasks'));
    }
}
