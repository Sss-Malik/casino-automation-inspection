<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Automation\BackendAccountController;
use App\Http\Controllers\Automation\LogsController;
use App\Http\Controllers\Automation\RequestController;
use App\Http\Controllers\Automation\TaskController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::redirect('/', '/dashboard');


Route::get('login', [LoginController::class, 'showLoginForm'])
    ->name('login');

Route::post('/login', [LoginController::class, 'login']);


Route::group(['middleware' => 'auth'], function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('tasks/data', [TaskController::class, 'data'])->name('tasks.data');
    Route::get('logs/{taskId?}', [LogsController::class, 'index'])->name('logs.index');

    Route::prefix('requests')->group(function () {
        Route::get('make', [RequestController::class, 'index'])->name('request.index');
        Route::get('view', [RequestController::class, 'view'])->name('request.view');
        Route::get('data', [RequestController::class, 'data'])->name('request.data');
        Route::post('send', [RequestController::class, 'send'])->name('request.send');
    });

    Route::get('backend/accounts/stats', [BackendAccountController::class, 'index'])->name('backend.accounts.stats');
    Route::get('backend/accounts/view', [BackendAccountController::class, 'view'])->name('backend.accounts.view.all');
    Route::get('backend/{backendId}/accounts/view', [BackendAccountController::class, 'view'])->name('backend.accounts.view');
    Route::get('backend/{backendId}/accounts/create', [BackendAccountController::class, 'createMore'])->name('backend.accounts.create');

});

Route::post('logout', [LoginController::class, 'logout'])
    ->name('logout');
