<?php
// Cole APENAS estas linhas dentro do mesmo grupo autenticado das rotas /rh no routes/web.php

Route::get('/dashboard-premium', 'RHDashboardPremiumController@index')->name('rh.dashboard_premium.index');
Route::get('/whatsapp-inteligente', 'RHWhatsAppInteligenteController@index')->name('rh.whatsapp_inteligente.index');
Route::post('/whatsapp-inteligente', 'RHWhatsAppInteligenteController@responder')->name('rh.whatsapp_inteligente.responder');
