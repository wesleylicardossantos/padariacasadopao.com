<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant.context', 'enterpriseAccess', 'verificaEmpresa', 'throttle:enterprise'])
    ->prefix('enterprise/financeiro')
    ->group(function () {
        Route::get('/', [\App\Modules\Financeiro\Controllers\GovernanceController::class, 'index'])
            ->name('enterprise.financeiro.index');
        Route::get('/kpis', [\App\Modules\Financeiro\Controllers\GovernanceController::class, 'kpis'])
            ->name('enterprise.financeiro.kpis');
        Route::get('/audit', [\App\Modules\Financeiro\Controllers\GovernanceController::class, 'audit'])
            ->name('enterprise.financeiro.audit');
        Route::get('/inconsistencias', [\App\Modules\Financeiro\Controllers\GovernanceController::class, 'inconsistencias'])
            ->name('enterprise.financeiro.inconsistencias');

        Route::get('/operations', [\App\Modules\Financeiro\Controllers\OperationsController::class, 'overview'])
            ->name('enterprise.financeiro.operations');
        Route::post('/receivables', [\App\Modules\Financeiro\Controllers\OperationsController::class, 'registerReceivable'])
            ->name('enterprise.financeiro.receivables.store');
        Route::post('/payables', [\App\Modules\Financeiro\Controllers\OperationsController::class, 'registerPayable'])
            ->name('enterprise.financeiro.payables.store');
        Route::patch('/receivables/{id}/settle', [\App\Modules\Financeiro\Controllers\OperationsController::class, 'settleReceivable'])
            ->name('enterprise.financeiro.receivables.settle');
        Route::patch('/payables/{id}/settle', [\App\Modules\Financeiro\Controllers\OperationsController::class, 'settlePayable'])
            ->name('enterprise.financeiro.payables.settle');
    });
