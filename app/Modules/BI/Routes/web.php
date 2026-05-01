<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant.context', 'enterpriseAccess', 'verificaEmpresa', 'throttle:enterprise'])
    ->prefix('enterprise/bi')
    ->group(function () {
        Route::get('/', [\App\Modules\BI\Controllers\GovernanceController::class, 'index'])
            ->name('enterprise.bi.index');
        Route::get('/dashboard', [\App\Modules\BI\Controllers\GovernanceController::class, 'dashboard'])
            ->name('enterprise.bi.dashboard');
        Route::get('/dre', [\App\Modules\BI\Controllers\GovernanceController::class, 'dre'])
            ->name('enterprise.bi.dre');
    });
