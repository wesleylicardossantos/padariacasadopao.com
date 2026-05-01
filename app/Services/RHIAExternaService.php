<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class RHIAExternaService
{
    public static function montarAlertasExternos(array $resumo, array $historico)
    {
        $alertas = [];
        if(isset($resumo['peso_folha']) && $resumo['peso_folha'] > 45){
            $alertas[] = "Folha acima de 45% do faturamento";
        }
        if(isset($resumo['resultado']) && $resumo['resultado'] < 0){
            $alertas[] = "Empresa operando no prejuízo";
        }
        return $alertas;
    }

    public static function enviarEmail($destino, $payload)
    {
        if(!$destino) return;
        Mail::raw(json_encode($payload), function($m) use ($destino){
            $m->to($destino)->subject('Alerta IA RH');
        });
    }

    public static function enviarWhatsappWebhook($url, $payload)
    {
        if(!$url) return;
        Http::post($url, $payload);
    }
}
