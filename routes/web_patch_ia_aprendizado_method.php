<?php
// PATCH SEGURO - substitua APENAS estas rotas dentro do grupo autenticado /rh no routes/web.php

Route::get('/ia-aprendizado', 'RHIAAprendizadoController@index')->name('rh.ia_aprendizado.index');
Route::get('/ia-aprendizado/decidir', 'RHIAAprendizadoController@decidirGet')->name('rh.ia_aprendizado.decidir_get');
Route::post('/ia-aprendizado/decidir', 'RHIAAprendizadoController@decidir')->name('rh.ia_aprendizado.decidir');
