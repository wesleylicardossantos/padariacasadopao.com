<?php
// PATCH SEGURO - substitua APENAS estas rotas dentro do grupo autenticado /rh no routes/web.php

Route::get('/ia-aprovacao', 'RHIAAprovacaoController@index')->name('rh.ia_aprovacao.index');
Route::get('/ia-aprovacao/aprovar', 'RHIAAprovacaoController@aprovarGet')->name('rh.ia_aprovacao.aprovar_get');
Route::post('/ia-aprovacao/aprovar', 'RHIAAprovacaoController@aprovar')->name('rh.ia_aprovacao.aprovar');
