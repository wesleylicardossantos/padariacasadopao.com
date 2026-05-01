/*
|--------------------------------------------------------------------------
| RH - Custo por Funcionário
|--------------------------------------------------------------------------
| Cole este bloco dentro do grupo autenticado do routes/web.php
*/
Route::group(['prefix' => 'rh'], function () {
    Route::get('/custo-funcionario', 'RHCustoFuncionarioController@index')->name('rh.custo_funcionario.index');
});
