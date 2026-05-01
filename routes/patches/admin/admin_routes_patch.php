
/*
|--------------------------------------------------------------------------
| PAINEL ADMINISTRATIVO OCULTO
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => '__admin'], function () {

    Route::get('/', 'AdminMaintenanceController@index')->name('admin.panel');

    Route::get('clear-cache', 'AdminMaintenanceController@clearAll')
        ->name('admin.clearAll');

    Route::get('backup-db', 'AdminMaintenanceController@backupDatabase')
        ->name('admin.backup');

});
