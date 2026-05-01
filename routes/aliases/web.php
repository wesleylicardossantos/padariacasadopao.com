<?php

use App\Http\Controllers\AdminMaintenanceController;
use App\Http\Controllers\ConfigDeliveryController;
use App\Http\Controllers\CteController;
use App\Http\Controllers\CteOsController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\EnderecosEcommerceController;
use App\Http\Controllers\MdfeController;
use App\Http\Controllers\RHFeriasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route aliases and compatibility fixes
|--------------------------------------------------------------------------
*/

Route::middleware(['web'])->group(function () {
    // Auth compatibility aliases used by framework/views.
    Route::get('/login', 'UserController@newAccess')->name('login');
    Route::get('/register', function () {
        return redirect('/cadastro');
    })->name('register');
    Route::get('/password/reset/{token?}', function ($token = null) {
        return redirect('/login');
    })->name('password.reset');

    // Legacy maintenance aliases referenced by old views.
    Route::get('/__admin/maintenance', [AdminMaintenanceController::class, 'index'])
        ->name('adminMaintenance.index');

    Route::post('/__admin/maintenance/run', function (Request $request) {
        $action = (string) $request->input('action');
        $map = [
            'optimize_clear' => 'optimize:clear',
            'cache_clear' => 'cache:clear',
            'config_clear' => 'config:clear',
            'view_clear' => 'view:clear',
            'route_clear' => 'route:clear',
        ];

        if (! isset($map[$action])) {
            return redirect()->route('adminMaintenance.index')
                ->with('flash_erro', 'Ação de manutenção inválida.');
        }

        try {
            Artisan::call($map[$action]);
            return redirect()->route('adminMaintenance.index')
                ->with('flash_sucesso', 'Ação executada com sucesso!')
                ->with('maintenance_output', trim((string) Artisan::output()));
        } catch (\Throwable $e) {
            return redirect()->route('adminMaintenance.index')
                ->with('flash_erro', 'Falha ao executar a ação: ' . $e->getMessage());
        }
    })->name('adminMaintenance.run');

    // Delivery config aliases referenced by views/controllers.
    Route::get('/configDelivery', [ConfigDeliveryController::class, 'index'])
        ->name('configDelivery.index');
    Route::post('/configDelivery', [ConfigDeliveryController::class, 'store'])
        ->name('configDelivery.store');
    Route::get('/configDelivery/galeria', [ConfigDeliveryController::class, 'galeria'])
        ->name('configDelivery.galeria');
    Route::post('/configDelivery/saveImagem', [ConfigDeliveryController::class, 'storeImagem'])
        ->name('configDelivery.storeImagem');
    Route::get('/configDelivery/deleteImagem/{id}', [ConfigDeliveryController::class, 'deleteImagem'])
        ->name('configDelivery.deleteImagem');
    Route::post('/configDelivery/save-coords', [ConfigDeliveryController::class, 'saveCoords'])
        ->name('configDelivery.save-coords');

    // CT-e / CT-e OS compatibility aliases.
    Route::get('/cte/estadoFiscal/{id}', [CteController::class, 'estadoFiscal'])
        ->name('cte.state-fiscal');
    Route::get('/cteOs/custos/{id}', [CteOsController::class, 'detalhes'])
        ->name('cteOs.custos');

    // Empresa certificate download alias by filename.
    Route::get('/empresas/download-file/{file}', function (string $file) {
        $file = basename($file);
        $path = public_path('certificados/' . $file);
        abort_unless(is_file($path), 404);
        return response()->download($path);
    })->where('file', '.*')->name('empresas.download_file');

    // Ecommerce addresses aliases.
    Route::get('/enderecosEcommerce/edit/{id}', [EnderecosEcommerceController::class, 'edit'])
        ->name('enderecosEcommerce.edit');
    Route::match(['PUT', 'PATCH', 'POST'], '/enderecosEcommerce/update/{id}', [EnderecosEcommerceController::class, 'update'])
        ->name('enderecoEcommerce.update');

    // MDF-e aliases referenced by old views/forms.
    Route::get('/mdfe/nao-encerrados', [MdfeController::class, 'naoEncerrados'])
        ->name('mdfe.nao-encerrados');
    Route::get('/mdfe/estadoFiscal/{id}', [MdfeController::class, 'estadoFiscal'])
        ->name('mdfe.estadoFiscal');
    Route::post('/mdfe/estadoFiscal', [MdfeController::class, 'estadoFiscalStore'])
        ->name('mdfe.estadoFiscalStore');
    Route::get('/mdfe/encerrar', [MdfeController::class, 'encerrar'])
        ->name('mdfe.encerrar');
    Route::post('/mdfe/enviarXml', [MdfeController::class, 'enviarXml'])
        ->name('mdfe.enviarXml');

    // RH férias compatibility aliases.
    Route::get('/rh/ferias/{id}/edit', [RHFeriasController::class, 'edit'])
        ->name('rh.ferias.edit');
    Route::match(['PUT', 'PATCH', 'POST'], '/rh/ferias/{id}', [RHFeriasController::class, 'update'])
        ->name('rh.ferias.update');
    Route::delete('/rh/ferias/{id}', [RHFeriasController::class, 'destroy'])
        ->name('rh.ferias.destroy');
});
