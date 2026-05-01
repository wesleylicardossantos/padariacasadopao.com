<?php
// Cole APENAS estas linhas dentro do mesmo grupo autenticado das rotas /rh no routes/web.php

Route::get('/absurdo', 'RHModoAbsurdoController@index')->name('rh.absurdo.index');
Route::get('/whatsapp-bot', 'RHWhatsAppBotController@index')->name('rh.whatsapp_bot.index');
Route::post('/whatsapp-bot', 'RHWhatsAppBotController@responder')->name('rh.whatsapp_bot.responder');
