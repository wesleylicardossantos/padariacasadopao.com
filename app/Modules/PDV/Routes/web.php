<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant.context', 'enterpriseAccess', 'verificaEmpresa', 'throttle:enterprise'])
    ->prefix('enterprise/pdv')
    ->group(function () {
        Route::get('/', [\App\Modules\PDV\Controllers\GovernanceController::class, 'index'])
            ->name('enterprise.pdv.index');
        Route::get('/audit', [\App\Modules\PDV\Controllers\GovernanceController::class, 'audit'])
            ->name('enterprise.pdv.audit');
        Route::post('/reprocess', [\App\Modules\PDV\Controllers\GovernanceController::class, 'reprocess'])
            ->name('enterprise.pdv.reprocess');
        Route::get('/mobile', [\App\Modules\PDV\Controllers\MobileController::class, 'index'])
            ->name('enterprise.pdv.mobile');
    });
