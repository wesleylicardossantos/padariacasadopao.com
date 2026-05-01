<?php
// Cole APENAS estas linhas dentro do mesmo grupo autenticado das rotas /rh no routes/web.php

Route::get('/ia-aprendizado', 'RHIAAprendizadoController@index')->name('rh.ia_aprendizado.index');
Route::post('/ia-aprendizado/decidir', 'RHIAAprendizadoController@decidir')->name('rh.ia_aprendizado.decidir');
