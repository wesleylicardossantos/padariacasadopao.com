<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant.context', 'enterpriseAccess', 'verificaEmpresa', 'throttle:enterprise'])
    ->prefix('enterprise/saas')
    ->group(function () {
        Route::get('/', [\App\Modules\SaaS\Controllers\GovernanceController::class, 'index'])
            ->name('enterprise.saas.index');
        Route::get('/executive', [\App\Modules\SaaS\Controllers\ExecutiveDashboardController::class, 'index'])
            ->name('enterprise.saas.executive');
        Route::get('/premium', [\App\Modules\SaaS\Controllers\PremiumController::class, 'index'])
            ->name('enterprise.saas.premium');
        Route::get('/scale', [\App\Modules\SaaS\Controllers\ScaleController::class, 'index'])
            ->name('enterprise.saas.scale');
        Route::get('/observability', [\App\Modules\SaaS\Controllers\ObservabilityController::class, 'index'])
            ->name('enterprise.saas.observability');
        Route::get('/overview', [\App\Modules\SaaS\Controllers\GovernanceController::class, 'overview'])
            ->name('enterprise.saas.overview');
        Route::get('/plans', [\App\Modules\SaaS\Controllers\GovernanceController::class, 'plans'])
            ->name('enterprise.saas.plans');
        Route::get('/usage', [\App\Modules\SaaS\Controllers\GovernanceController::class, 'usage'])
            ->name('enterprise.saas.usage');
        Route::get('/billing', [\App\Modules\SaaS\Controllers\GovernanceController::class, 'billing'])
            ->name('enterprise.saas.billing');
        Route::get('/scale-readiness', [\App\Modules\SaaS\Controllers\GovernanceController::class, 'scaleReadiness'])
            ->name('enterprise.saas.scale_readiness');
        Route::get('/tenant-health', [\App\Modules\SaaS\Controllers\GovernanceController::class, 'tenantHealth'])
            ->name('enterprise.saas.tenant_health');
        Route::get('/platform-overview', [\App\Modules\SaaS\Controllers\GovernanceController::class, 'platformOverview'])
            ->name('enterprise.saas.platform_overview');
    });
