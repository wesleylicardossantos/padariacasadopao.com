<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/controle'], function () {
    Route::get('/relatorio-vendas-resumido', 'RelatorioVendasResumidoController@index')
        ->name('controle.relatorio-vendas-resumido');
});
