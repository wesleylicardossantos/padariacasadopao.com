<?php

namespace App\Modules\SaaS\Services;

use App\Services\RHIntelligentAlertsService;

class PremiumAutomationService
{
    public function __construct(
        private UsageSnapshotService $snapshots,
        private OnboardingService $onboarding,
        private SubscriptionLifecycleService $lifecycle,
        private PremiumNotificationCenterService $notifications,
    ) {
    }

    public function run(int $empresaId, int $mes, int $ano): array
    {
        $usageSnapshot = $this->snapshots->snapshot($empresaId);
        $alerts = RHIntelligentAlertsService::gerar($empresaId, $mes, $ano);
        $persistedAlerts = RHIntelligentAlertsService::persistir($empresaId, $alerts);
        $onboarding = $this->onboarding->status($empresaId);
        $lifecycle = $this->lifecycle->current($empresaId);

        if (($onboarding['progress_percent'] ?? 0) < 100) {
            $this->notifications->push(
                $empresaId,
                'onboarding',
                'warning',
                'Onboarding incompleto',
                'Existem pendências de onboarding para concluir a operação SaaS.',
                $onboarding
            );
        }

        if (($lifecycle['status'] ?? null) && !in_array($lifecycle['status'], ['active', 'trial', 'ativo'], true)) {
            $this->notifications->push(
                $empresaId,
                'billing',
                'critical',
                'Assinatura exige atenção',
                'O ciclo de assinatura do tenant requer acompanhamento.',
                $lifecycle
            );
        }

        return [
            'empresa_id' => $empresaId,
            'usage_snapshot_created' => (bool) $usageSnapshot,
            'rh_alerts_generated' => count($alerts),
            'rh_alerts_persisted' => $persistedAlerts,
            'onboarding_progress' => (float) ($onboarding['progress_percent'] ?? 0),
            'lifecycle_status' => (string) ($lifecycle['status'] ?? 'inactive'),
            'executed_at' => now()->format('d/m/Y H:i:s'),
        ];
    }
}
