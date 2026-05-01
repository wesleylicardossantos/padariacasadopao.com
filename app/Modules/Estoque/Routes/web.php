<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant.context', 'enterpriseAccess', 'verificaEmpresa', 'throttle:enterprise'])
    ->prefix('enterprise/estoque')
    ->group(function () {
        Route::get('/', [\App\Modules\Estoque\Controllers\GovernanceController::class, 'index'])
            ->name('enterprise.estoque.index');
        Route::get('/snapshot', [\App\Modules\Estoque\Controllers\GovernanceController::class, 'snapshot'])
            ->name('enterprise.estoque.snapshot');
        Route::post('/entry', [\App\Modules\Estoque\Controllers\GovernanceController::class, 'entry'])
            ->name('enterprise.estoque.entry');
        Route::post('/exit', [\App\Modules\Estoque\Controllers\GovernanceController::class, 'exit'])
            ->name('enterprise.estoque.exit');
        Route::post('/adjustment', [\App\Modules\Estoque\Controllers\GovernanceController::class, 'adjustment'])
            ->name('enterprise.estoque.adjustment');
        Route::post('/reconcile', [\App\Modules\Estoque\Controllers\GovernanceController::class, 'reconcile'])
            ->name('enterprise.estoque.reconcile');
        Route::get('/write-guard-report', [\App\Modules\Estoque\Controllers\GovernanceController::class, 'guardReport'])
            ->name('enterprise.estoque.write_guard_report');
    });
