<?php

use App\Modules\Fiscal\Controllers\GovernanceController;
use App\Modules\Fiscal\Controllers\OperationsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant.context', 'enterpriseAccess', 'verificaEmpresa', 'throttle:enterprise'])
    ->prefix('enterprise/fiscal')
    ->group(function () {
        Route::get('/snapshot', [GovernanceController::class, 'snapshot'])
            ->name('enterprise.fiscal.snapshot');

        Route::post('/prepare', [OperationsController::class, 'prepare'])
            ->name('enterprise.fiscal.prepare');
        Route::post('/transmit', [OperationsController::class, 'transmit'])
            ->name('enterprise.fiscal.transmit');
        Route::post('/documents/{id}/cancel', [OperationsController::class, 'cancel'])
            ->name('enterprise.fiscal.cancel');
        Route::get('/documents/{id}/status', [OperationsController::class, 'status'])
            ->name('enterprise.fiscal.status');
    });
