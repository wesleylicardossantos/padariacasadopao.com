<?php
// Cole APENAS estas linhas dentro do mesmo grupo autenticado das rotas /rh no routes/web.php

Route::get('/preditivo-ia', 'RHPreditivoController@index')->name('rh.preditivo_ia.index');
Route::get('/alertas-inteligentes', 'RHAlertasInteligentesController@index')->name('rh.alertas_inteligentes.index');
Route::get('/alertas-inteligentes/ler/{id}', 'RHAlertasInteligentesController@ler')->name('rh.alertas_inteligentes.ler');
