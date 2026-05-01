/*
|--------------------------------------------------------------------------
| RH V6
|--------------------------------------------------------------------------
| Cole este bloco dentro do grupo principal autenticado do routes/web.php
*/

Route::group(['prefix' => 'rh'], function () {
    Route::get('/folha', 'RHFolhaController@index')->name('rh.folha.index');
    Route::get('/recibo/{id}', 'RHFolhaController@recibo')->name('rh.folha.recibo');
    Route::get('/financeiro', 'RHFolhaController@financeiro')->name('rh.financeiro');

    Route::get('/documentos/contrato/{id}', 'RHDocumentoGeradoController@contrato')->name('rh.documentos.contrato');
    Route::get('/documentos/ferias/{id}', 'RHDocumentoGeradoController@avisoFerias')->name('rh.documentos.ferias');
    Route::get('/documentos/desligamento/{id}', 'RHDocumentoGeradoController@termoDesligamento')->name('rh.documentos.desligamento');
});
