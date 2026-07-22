<?php

use App\Http\Middleware\AuthenticateJwtSession;
use App\Modules\BankingNetwork\Http\Controllers\BankController;
use App\Modules\BankingNetwork\Http\Controllers\BankAgentController;
use App\Modules\BankingNetwork\Http\Controllers\MyAgentsController;
use App\Modules\BankingNetwork\Http\Controllers\UserBankAgentAssignmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', AuthenticateJwtSession::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/banks', [BankController::class, 'index'])->name('banks.index');
    Route::get('/banks/create', [BankController::class, 'create'])->name('banks.create');
    Route::post('/banks', [BankController::class, 'store'])->name('banks.store');
    Route::patch('/banks/{bank}', [BankController::class, 'update'])->name('banks.update');
    Route::delete('/banks/{bank}', [BankController::class, 'deactivate'])->name('banks.deactivate');

    Route::get('/bank-agents', [BankAgentController::class, 'index'])->name('bank-agents.index');
    Route::get('/bank-agents/create', [BankAgentController::class, 'create'])->name('bank-agents.create');
    Route::post('/bank-agents', [BankAgentController::class, 'store'])->name('bank-agents.store');
    Route::patch('/bank-agents/{agent}', [BankAgentController::class, 'update'])->name('bank-agents.update');
    Route::delete('/bank-agents/{agent}', [BankAgentController::class, 'deactivate'])->name('bank-agents.deactivate');

    Route::get('/users/{user}/assignments', [UserBankAgentAssignmentController::class, 'index'])->name('users.assignments.index');
    Route::post('/users/{user}/assignments', [UserBankAgentAssignmentController::class, 'store'])->name('users.assignments.store');
    Route::delete('/assignments/{assignment}', [UserBankAgentAssignmentController::class, 'destroy'])->name('assignments.destroy');
});

Route::middleware(['web', AuthenticateJwtSession::class])->group(function () {
    Route::get('/my-agents', [MyAgentsController::class, 'index'])->name('my-agents.index');
});
