// ==== PATCH RH INTELIGENTE (NÃO SUBSTITUIR O ARQUIVO) ====
// Cole dentro do grupo de rotas autenticadas (web.php)

Route::prefix('rh')->group(function () {

    Route::get('/dashboard-executivo', [\App\Http\Controllers\RHDashboardExecutivoController::class, 'index'])
        ->name('rh.dashboard_executivo.index');

    Route::get('/dre-inteligente', [\App\Http\Controllers\RHDreInteligenteController::class, 'index'])
        ->name('rh.dre_inteligente.index');

    Route::get('/dre-preditivo', [\App\Http\Controllers\RHDrePreditivoController::class, 'index'])
        ->name('rh.dre_preditivo.index');

});

// ==== FIX GLOBAL LOGOFF ====
// evita erro: Route [logoff] not defined
Route::get('/logoff', function () {
    session()->flush();
    return redirect('/');
})->name('logoff');
