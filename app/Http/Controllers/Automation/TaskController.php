<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\Models\AutomationResult;
use App\Models\BackendGames;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index() {
        $tasks = AutomationResult::with('backend')->get()
            ->sortByDesc('created_at');
        return view('automation.task.index', compact('tasks'));

    }
}
