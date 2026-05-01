/*
|--------------------------------------------------------------------------
| RH - Holerite + Resumo Financeiro
|--------------------------------------------------------------------------
| Cole este bloco dentro do grupo principal autenticado do routes/web.php
*/

Route::group(['prefix' => 'rh'], function () {
    Route::get('/holerite/{id}', 'RHHoleriteController@show')->name('rh.holerite.show');
    Route::get('/folha/resumo-financeiro', 'RHFolhaFinanceiroResumoController@index')->name('rh.folha.resumo_financeiro');
});
