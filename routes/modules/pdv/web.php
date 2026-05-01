<?php

use Illuminate\Support\Facades\Route;

Route::middleware([
    'verificaEmpresa',
    'validaAcesso',
    'verificaContratoAssinado',
    'limiteArmazenamento',
])->prefix('pdv')->group(function () {
    Route::get('/teste-web', 'Pdv\\WebTestController@index')->name('pdv.teste_web');
});
