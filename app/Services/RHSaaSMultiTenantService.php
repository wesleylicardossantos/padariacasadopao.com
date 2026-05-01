<?php
namespace App\Services;

use App\Models\Empresa;

class RHSaaSMultiTenantService
{
    public static function executarParaTodas()
    {
        if(!class_exists(Empresa::class)) return [];

        $empresas = Empresa::all();
        $resultados = [];

        foreach($empresas as $empresa){
            $resultados[] = RHMaximoAutomationService::processarEmpresa($empresa->id);
        }

        return $resultados;
    }
}
