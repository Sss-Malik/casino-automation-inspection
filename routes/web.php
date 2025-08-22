<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Automation\BackendAccountController;
use App\Http\Controllers\Automation\LogsController;
use App\Http\Controllers\Automation\RequestController;
use App\Http\Controllers\Automation\TaskController;
use Illuminate\Support\Facades\Route;


Route::redirect('/', '/dashboard');


Route::get('login', [LoginController::class, 'showLoginForm'])
    ->name('login');

Route::post('/login', [LoginController::class, 'login']);


Route::group(['middleware' => 'auth'], function () {
    Route::get('dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('logs/{taskId?}', [LogsController::class, 'index'])->name('logs.index');
    Route::get('requests/make', [RequestController::class, 'index'])->name('request.index');
    Route::get('requests/view', [RequestController::class, 'view'])->name('request.view');
    Route::post('requests/send', [RequestController::class, 'send'])->name('request.send');
    Route::get('backend/accounts/stats', [BackendAccountController::class, 'index'])->name('backend.accounts.stats');
    Route::get('backend/{backendId}/accounts/create', [BackendAccountController::class, 'createMore'])->name('backend.accounts.create');
});

Route::post('logout', [LoginController::class, 'logout'])
    ->name('logout');
