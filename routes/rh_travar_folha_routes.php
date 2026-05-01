/*
|--------------------------------------------------------------------------
| RH - Travar / Reabrir Folha
|--------------------------------------------------------------------------
| Cole este bloco dentro do grupo principal autenticado do routes/web.php
*/

Route::group(['prefix' => 'rh'], function () {
    Route::post('/folha/reabrir', 'RHFechamentoFolhaController@reabrir')->name('rh.folha.reabrir');
});
