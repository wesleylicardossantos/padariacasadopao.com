<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ROTAS PARA EXPORTAÇÃO DE FUNCIONÁRIOS
|--------------------------------------------------------------------------
| Cole estas rotas dentro do grupo existente de 'funcionarios'
*/

Route::group(['prefix' => 'funcionarios'], function () {

    Route::get('export/pdf', 'FuncionarioController@exportPdf')->name('funcionarios.exportPdf');

    Route::get('export/excel', 'FuncionarioController@exportExcel')->name('funcionarios.exportExcel');

});
