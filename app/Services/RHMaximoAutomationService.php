<?php

namespace App\Services;

use App\Models\Empresa;
use Illuminate\Support\Facades\Log;

class RHMaximoAutomationService
{
    public static function processarEmpresa($empresaId, $mes = null, $ano = null)
    {
        $mes = (int)($mes ?: date('m'));
        $ano = (int)($ano ?: date('Y'));

        $resumo = RHAnalyticsService::resumoCompetencia($empresaId, $mes, $ano);
        $historico = RHAnalyticsService::historico($empresaId, $mes, $ano, 6);
        $score = RHAnalyticsService::score($resumo);
        $alertas = RHIAExternaService::montarAlertasExternos($resumo, $historico);
        $previsoes = RHAnalyticsService::previsaoTresMeses($historico);

        $payload = [
            'competencia' => str_pad($mes, 2, '0', STR_PAD_LEFT) . '/' . $ano,
            'score' => $score,
            'alertas' => $alertas,
            'previsoes' => $previsoes,
            'modo_dono' => $score < 60
                ? 'Segurar custos e evitar expandir equipe.'
                : ($score < 80
                    ? 'Crescimento controlado, com foco em produtividade.'
                    : 'Cenário favorável para crescimento planejado.'),
        ];

        $email = env('RH_IA_EMAIL_TO');
        $webhook = env('RH_IA_WHATSAPP_WEBHOOK');

        $resultadoEmail = null;
        $resultadoWhatsapp = null;

        try {
            if ($email) {
                $resultadoEmail = RHIAExternaService::enviarEmail($email, $payload);
            }

            if ($webhook) {
                $resultadoWhatsapp = RHIAExternaService::enviarWhatsappWebhook($webhook, $payload);
            }

            Log::info('RH IA automática executada', [
                'empresa_id' => $empresaId,
                'competencia' => $payload['competencia'],
                'score' => $score,
                'alertas' => $alertas,
                'resultado_email' => $resultadoEmail,
                'resultado_whatsapp' => $resultadoWhatsapp,
            ]);
        } catch (\Throwable $e) {
            Log::error('Falha RH IA automática', [
                'empresa_id' => $empresaId,
                'erro' => $e->getMessage(),
            ]);
        }

        return $payload;
    }

    public static function processarTodasEmpresas()
    {
        if (!class_exists(Empresa::class)) {
            return [];
        }

        $empresas = Empresa::select('id')->get();
        $saida = [];

        foreach ($empresas as $empresa) {
            $saida[] = self::processarEmpresa($empresa->id);
        }

        return $saida;
    }
}
