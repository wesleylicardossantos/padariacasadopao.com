<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'tenant.context', 'enterpriseAccess', 'verificaEmpresa', 'throttle:enterprise'])
    ->prefix('enterprise/comercial')
    ->group(function () {
        Route::get('/kpis', [\App\Modules\Comercial\Controllers\GovernanceController::class, 'kpis'])
            ->name('enterprise.comercial.kpis');

        Route::get('/portfolio', [\App\Modules\Comercial\Controllers\PortfolioController::class, 'index'])
            ->name('enterprise.comercial.portfolio');
        Route::get('/snapshot', [\App\Modules\Comercial\Controllers\PortfolioController::class, 'snapshot'])
            ->name('enterprise.comercial.snapshot');

        Route::post('/customers', [\App\Modules\Comercial\Controllers\OperationsController::class, 'upsertCustomer'])
            ->name('enterprise.comercial.customers.upsert');
        Route::post('/sales', [\App\Modules\Comercial\Controllers\OperationsController::class, 'createSale'])
            ->name('enterprise.comercial.sales.create');
        Route::post('/orders', [\App\Modules\Comercial\Controllers\OperationsController::class, 'createOrder'])
            ->name('enterprise.comercial.orders.create');
        Route::post('/budgets', [\App\Modules\Comercial\Controllers\OperationsController::class, 'createBudget'])
            ->name('enterprise.comercial.budgets.create');
    });
