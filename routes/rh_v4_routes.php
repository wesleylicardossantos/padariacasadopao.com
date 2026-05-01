/*
|--------------------------------------------------------------------------
| RH V4
|--------------------------------------------------------------------------
| Cole este bloco dentro do grupo principal autenticado do routes/web.php
*/

Route::group(['prefix' => 'rh'], function () {
    Route::get('/dashboard-v4', 'RHV4DashboardController@index')->name('rh.dashboard.v4');

    Route::get('/faltas', 'RHFaltaController@index')->name('rh.faltas.index');
    Route::get('/faltas/create', 'RHFaltaController@create')->name('rh.faltas.create');
    Route::post('/faltas', 'RHFaltaController@store')->name('rh.faltas.store');

    Route::get('/desligamentos', 'RHDesligamentoController@index')->name('rh.desligamentos.index');
    Route::get('/desligamentos/create', 'RHDesligamentoController@create')->name('rh.desligamentos.create');
    Route::post('/desligamentos', 'RHDesligamentoController@store')->name('rh.desligamentos.store');
    Route::delete('/desligamentos/{id}', 'RHDesligamentoController@destroy')->name('rh.desligamentos.destroy');

    Route::get('/ferias/calculo', 'RHFeriasCalculoController@index')->name('rh.ferias.calculo');
});
