/*
|--------------------------------------------------------------------------
| RH - DRE com Folha
|--------------------------------------------------------------------------
| Cole este bloco dentro do grupo principal autenticado do routes/web.php
*/

Route::group(['prefix' => 'rh'], function () {
    Route::get('/dre-folha', 'RHDreFolhaController@index')->name('rh.dre_folha.index');
});
