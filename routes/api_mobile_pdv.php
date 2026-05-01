<?php

use Illuminate\Support\Facades\Route;

Route::prefix('mobile/pdv')->middleware(['tenant.context'])->group(function () {
    Route::get('/health', function () {
        return response()->json(['status' => 'ok', 'module' => 'mobile-pdv']);
    });

    Route::get('/bootstrap', 'Pdv\\OfflineBootstrapController');
    Route::post('/vendas/sincronizar', 'Pdv\\OfflineVendaSyncController@sincronizar');
    Route::match(['GET', 'POST'], '/sync/status', 'Pdv\\OfflineVendaSyncController@status');
});
