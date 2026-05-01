\
/*
|--------------------------------------------------------------------------
| RH V3
|--------------------------------------------------------------------------
| Cole este bloco dentro do grupo principal autenticado do routes/web.php
*/

Route::group(['prefix' => 'rh'], function () {
    Route::get('/ferias', 'RHFeriasV3Controller@index')->name('rh.ferias.index');
    Route::get('/ferias/create', 'RHFeriasV3Controller@create')->name('rh.ferias.create');
    Route::post('/ferias', 'RHFeriasV3Controller@store')->name('rh.ferias.store');

    Route::get('/alertas', 'RHAlertaController@index')->name('rh.alertas.index');
    Route::get('/dossie/{id}', 'RHDossieController@show')->name('rh.dossie.show');
    Route::delete('/dossie/{id}/documentos/{documentoId}', 'RHDossieController@destroyDocumento')->name('rh.dossie.documentos.destroy');
    Route::delete('/dossie/{id}/eventos/{eventoId}', 'RHDossieController@destroyEvento')->name('rh.dossie.eventos.destroy');
});
