<?php

use App\Http\Middleware\AuthenticateJwtSession;
use App\Modules\Operations\Http\Controllers\OperationController;
use App\Modules\Operations\Http\Controllers\OperationTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', AuthenticateJwtSession::class])->group(function () {
    Route::get('/operations', [OperationController::class, 'index'])->name('operations.index');
    Route::get('/operations/create', [OperationController::class, 'create'])->name('operations.create');
    Route::post('/operations', [OperationController::class, 'store'])->name('operations.store');
    Route::get('/operations/{operation}', [OperationController::class, 'show'])->name('operations.show');
    Route::post('/operations/{operation}/annul', [OperationController::class, 'annul'])->name('operations.annul');
});

Route::middleware(['web', AuthenticateJwtSession::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/operation-types', [OperationTypeController::class, 'index'])->name('operation-types.index');
    Route::get('/operation-types/create', [OperationTypeController::class, 'create'])->name('operation-types.create');
    Route::post('/operation-types', [OperationTypeController::class, 'store'])->name('operation-types.store');
    Route::get('/operation-types/{type}/edit', [OperationTypeController::class, 'edit'])->name('operation-types.edit');
    Route::patch('/operation-types/{type}', [OperationTypeController::class, 'update'])->name('operation-types.update');
    Route::delete('/operation-types/{type}', [OperationTypeController::class, 'destroy'])->name('operation-types.destroy');
});
