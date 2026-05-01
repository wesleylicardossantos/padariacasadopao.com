<?php

namespace App\Modules\AI\Services;

class BusinessInsightService
{
    public function overview(int $empresaId): array
    {
        $forecast = app(ForecastService::class)->monthlyRevenueForecast($empresaId);
        $cashRisk = app(ForecastService::class)->cashRisk($empresaId);
        $recommendations = app(SalesRecommendationService::class)->recommendations($empresaId);
        $anomalies = app(PdvAnomalyService::class)->detect($empresaId);

        return [
            'empresa_id' => $empresaId,
            'forecast' => $forecast,
            'cash_risk' => $cashRisk,
            'recommendations' => $recommendations,
            'pdv_anomalies' => $anomalies,
            'generated_at' => now()->format('d/m/Y H:i:s'),
        ];
    }
}
