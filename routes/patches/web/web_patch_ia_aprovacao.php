<?php
// Cole APENAS estas linhas dentro do mesmo grupo autenticado das rotas /rh no routes/web.php

Route::get('/ia-aprovacao', 'RHIAAprovacaoController@index')->name('rh.ia_aprovacao.index');
Route::post('/ia-aprovacao/aprovar', 'RHIAAprovacaoController@aprovar')->name('rh.ia_aprovacao.aprovar');
