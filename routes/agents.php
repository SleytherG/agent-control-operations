<?php

use App\Http\Middleware\AuthenticateJwtSession;
use App\Modules\Agents\Http\Controllers\AgentController;
use App\Modules\Agents\Http\Controllers\MyAgentsController;
use App\Modules\Agents\Http\Controllers\UserAgentAssignmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', AuthenticateJwtSession::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/agents', [AgentController::class, 'index'])->name('agents.index');
    Route::get('/agents/create', [AgentController::class, 'create'])->name('agents.create');
    Route::post('/agents', [AgentController::class, 'store'])->name('agents.store');
    Route::get('/agents/{agent}/edit', [AgentController::class, 'edit'])->name('agents.edit');
    Route::patch('/agents/{agent}', [AgentController::class, 'update'])->name('agents.update');
    Route::delete('/agents/{agent}', [AgentController::class, 'deactivate'])->name('agents.deactivate');

    Route::get('/users/{user}/assignments', [UserAgentAssignmentController::class, 'index'])->name('users.assignments.index');
    Route::post('/users/{user}/assignments', [UserAgentAssignmentController::class, 'store'])->name('users.assignments.store');
    Route::delete('/assignments/{assignment}', [UserAgentAssignmentController::class, 'destroy'])->name('assignments.destroy');
});

Route::middleware(['web', AuthenticateJwtSession::class])->group(function () {
    Route::get('/my-agents', [MyAgentsController::class, 'index'])->name('my-agents.index');
});
