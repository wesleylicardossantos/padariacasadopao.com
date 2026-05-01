/*
|--------------------------------------------------------------------------
| DASHBOARD ADMIN ERP
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => '__admin'], function () {

    Route::get('dashboard', 'AdminDashboardController@index')
        ->name('admin.dashboard');

});
