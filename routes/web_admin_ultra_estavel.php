/*
|--------------------------------------------------------------------------
| ADMIN ULTRA ESTÁVEL
|--------------------------------------------------------------------------
| Cole/substitua este bloco no routes/web.php
*/

Route::group(['prefix' => '__admin'], function () {

    Route::get('/', 'AdminProController@dashboard')->name('admin.index');

    Route::get('pro', 'AdminProController@dashboard')->name('admin.pro.dashboard');

    Route::get('monitor', 'AdminProController@monitor')->name('admin.pro.monitor');

    Route::get('audit', 'RecordLogController@index')->name('admin.audit');

});
