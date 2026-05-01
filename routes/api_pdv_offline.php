<?php

use Illuminate\Support\Facades\Route;

Route::get('/pdv/login', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Rota ativa. Use POST para autenticar no PDV.',
        'method_supported' => ['POST'],
    ]);
});

Route::group(['prefix' => 'pdv', 'middleware' => 'authPdv'], function () {
    Route::get('/bootstrap', 'Pdv\\OfflineBootstrapController');
    Route::post('/vendas/sincronizar', 'Pdv\\OfflineVendaSyncController@sincronizar');
    Route::match(['GET', 'POST'], '/sync/status', 'Pdv\\OfflineVendaSyncController@status');
});
