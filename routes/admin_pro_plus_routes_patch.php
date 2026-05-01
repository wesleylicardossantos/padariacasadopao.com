
/*
|--------------------------------------------------------------------------
| ERP ADMIN PRO PLUS
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => '__admin'], function () {
    Route::get('pro', 'AdminProController@dashboard')->name('admin.pro.dashboard');
    Route::get('monitor', 'AdminProController@monitor')->name('admin.pro.monitor');
    Route::get('audit', 'AdminProController@audit')->name('admin.pro.audit');
});
