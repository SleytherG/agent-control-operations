<?php

use App\Http\Middleware\AuthenticateJwtSession;
use App\Modules\Organization\Http\Controllers\GeoHierarchyController;
use App\Modules\Organization\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', AuthenticateJwtSession::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/regions', [GeoHierarchyController::class, 'regionsIndex'])->name('regions.index');
    Route::post('/regions', [GeoHierarchyController::class, 'storeRegion'])->name('regions.store');
    Route::get('/regions/{region}', [GeoHierarchyController::class, 'showRegion'])->name('regions.show');
    Route::patch('/regions/{region}', [GeoHierarchyController::class, 'updateRegion'])->name('regions.update');
    Route::delete('/regions/{region}', [GeoHierarchyController::class, 'deactivateRegion'])->name('regions.deactivate');

    Route::get('/regions/{region}/provinces', [GeoHierarchyController::class, 'provincesIndex'])->name('regions.provinces.index');
    Route::post('/regions/{region}/provinces', [GeoHierarchyController::class, 'storeProvince'])->name('regions.provinces.store');
    Route::patch('/provinces/{province}', [GeoHierarchyController::class, 'updateProvince'])->name('provinces.update');
    Route::delete('/provinces/{province}', [GeoHierarchyController::class, 'deactivateProvince'])->name('provinces.deactivate');

    Route::get('/provinces/{province}/districts', [GeoHierarchyController::class, 'districtsIndex'])->name('provinces.districts.index');
    Route::post('/provinces/{province}/districts', [GeoHierarchyController::class, 'storeDistrict'])->name('provinces.districts.store');
    Route::patch('/districts/{district}', [GeoHierarchyController::class, 'updateDistrict'])->name('districts.update');
    Route::delete('/districts/{district}', [GeoHierarchyController::class, 'deactivateDistrict'])->name('districts.deactivate');

    Route::get('/stores', [StoreController::class, 'index'])->name('stores.index');
    Route::get('/stores/create', [StoreController::class, 'create'])->name('stores.create');
    Route::post('/stores', [StoreController::class, 'store'])->name('stores.store');
    Route::get('/stores/{store}', [StoreController::class, 'show'])->name('stores.show');
    Route::patch('/stores/{store}', [StoreController::class, 'update'])->name('stores.update');
    Route::delete('/stores/{store}', [StoreController::class, 'deactivate'])->name('stores.deactivate');
});
