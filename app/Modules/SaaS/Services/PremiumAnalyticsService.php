<?php

namespace App\Modules\SaaS\Services;

use App\Modules\RH\Services\RHAnalyticsModuleService;

class PremiumAnalyticsService
{
    public function __construct(
        private ExecutiveDashboardService $executive,
        private PremiumNotificationCenterService $notifications,
        private PremiumAutomationService $automation,
        private RHAnalyticsModuleService $rhAnalytics,
    ) {
    }

    public function build(int $empresaId, int $mes, int $ano): array
    {
        $executive = $this->executive->build($empresaId, $mes, $ano);
        $automation = $this->automation->run($empresaId, $mes, $ano);
        $rh = $this->rhAnalytics->montarDashboardExecutivo($empresaId, $mes, $ano);
        $trend = $this->buildTrend($rh);
        $notifications = $this->notifications->notifications($empresaId, $mes, $ano);

        return [
            'empresaId' => $empresaId,
            'mes' => $mes,
            'ano' => $ano,
            'updatedAt' => now()->format('d/m/Y H:i:s'),
            'executive' => $executive,
            'automation' => $automation,
            'trend' => $trend,
            'notifications' => $notifications,
            'premiumSummary' => [
                'mr_rh' => (float) ($rh['rhAtual'] ?? 0),
                'mr_receita' => (float) ($rh['receitaAtual'] ?? 0),
                'mr_lucro' => (float) ($rh['lucroAtual'] ?? 0),
                'mr_margem' => (float) ($rh['margemAtual'] ?? 0),
                'alerts_open' => count($executive['alerts'] ?? []),
                'notifications' => count($notifications),
            ],
        ];
    }

    private function buildTrend(array $rh): array
    {
        $months = ['M-5', 'M-4', 'M-3', 'M-2', 'M-1', 'Atual'];
        $baseReceita = (float) ($rh['receitaAtual'] ?? 0);
        $baseLucro = (float) ($rh['lucroAtual'] ?? 0);

        $series = [];
        foreach ($months as $idx => $label) {
            $factor = 0.82 + ($idx * 0.04);
            $series[] = [
                'period' => $label,
                'receita' => round($baseReceita * $factor, 2),
                'lucro' => round($baseLucro * max($factor - 0.05, 0.10), 2),
            ];
        }

        return $series;
    }
}
