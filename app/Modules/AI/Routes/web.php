<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant.context', 'enterpriseAccess', 'verificaEmpresa', 'throttle:enterprise'])
    ->prefix('enterprise/ai')
    ->group(function () {
        Route::get('/', [\App\Modules\AI\Controllers\GovernanceController::class, 'index'])->name('enterprise.ai.index');
        Route::get('/overview', [\App\Modules\AI\Controllers\GovernanceController::class, 'overview'])->name('enterprise.ai.overview');
        Route::get('/forecast', [\App\Modules\AI\Controllers\GovernanceController::class, 'forecast'])->name('enterprise.ai.forecast');
        Route::get('/recommendations', [\App\Modules\AI\Controllers\GovernanceController::class, 'recommendations'])->name('enterprise.ai.recommendations');
        Route::get('/anomalies', [\App\Modules\AI\Controllers\GovernanceController::class, 'anomalies'])->name('enterprise.ai.anomalies');
    });
