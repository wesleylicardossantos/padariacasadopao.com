/*
|--------------------------------------------------------------------------
| RH - Fechamento Folha
|--------------------------------------------------------------------------
| Cole este bloco dentro do grupo principal autenticado do routes/web.php
*/

Route::group(['prefix' => 'rh'], function () {
    Route::post('/folha/fechar', 'RHFechamentoFolhaController@store')->name('rh.folha.fechar');
});
