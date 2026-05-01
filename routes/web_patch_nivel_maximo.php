<?php
// PATCH SEGURO - cole APENAS esta linha dentro do mesmo grupo autenticado das rotas /rh no routes/web.php

Route::get('/maximo', 'RHModoMaximoController@index')->name('rh.maximo.index');
