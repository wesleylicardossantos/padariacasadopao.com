<?php
// PATCH SEGURO - NÃO SUBSTITUIR O routes/web.php INTEIRO
// Cole este bloco dentro do mesmo grupo autenticado onde já estão as rotas /rh

Route::get('/ia-autonoma', 'RHIAAutonomaController@index')->name('rh.ia_autonoma.index');
