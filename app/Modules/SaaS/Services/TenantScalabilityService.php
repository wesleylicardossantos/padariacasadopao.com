<?php

namespace App\Modules\SaaS\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantScalabilityService
{
    public function readiness(int $empresaId): array
    {
        return Cache::remember("saas_scale_readiness_{$empresaId}", 120, function () use ($empresaId) {
            $usage = app(PlanLimitService::class)->limitsMatrix($empresaId);
            $score = 100;
            $alerts = [];

            foreach ($usage as $feature => $item) {
                if (($item['limit'] ?? null) !== null && ($item['used'] ?? 0) > ($item['limit'] ?? 0)) {
                    $score -= 15;
                    $alerts[] = "Limite excedido para {$feature}.";
                }
            }

            $queueBacklog = $this->queueBacklog();
            if ($queueBacklog > 500) {
                $score -= 20;
                $alerts[] = 'Fila de jobs acima do ideal para 1.000 clientes.';
            }

            $pdvBacklog = $this->pdvBacklog($empresaId);
            if ($pdvBacklog > 50) {
                $score -= 15;
                $alerts[] = 'Backlog do PDV acima do recomendado.';
            }

            return [
                'empresa_id' => $empresaId,
                'score' => max(0, $score),
                'queue_backlog' => $queueBacklog,
                'pdv_backlog' => $pdvBacklog,
                'usage' => $usage,
                'alerts' => $alerts,
                'recommended_workers' => $this->recommendedWorkers($queueBacklog),
            ];
        });
    }

    public function platformOverview(): array
    {
        return [
            'jobs_pending' => $this->queueBacklog(),
            'tenants_with_snapshots' => Schema::hasTable('saas_usage_snapshots') ? DB::table('saas_usage_snapshots')->distinct('empresa_id')->count('empresa_id') : 0,
            'pdv_pending_sync' => Schema::hasTable('pdv_offline_syncs') ? DB::table('pdv_offline_syncs')->where('status', '!=', 'sincronizado')->count() : 0,
            'generated_at' => now()->format('d/m/Y H:i:s'),
        ];
    }

    private function recommendedWorkers(int $queueBacklog): int
    {
        return max(2, (int) ceil($queueBacklog / 150));
    }

    private function queueBacklog(): int
    {
        return Schema::hasTable('jobs') ? (int) DB::table('jobs')->count() : 0;
    }

    private function pdvBacklog(int $empresaId): int
    {
        return Schema::hasTable('pdv_offline_syncs')
            ? (int) DB::table('pdv_offline_syncs')->where('empresa_id', $empresaId)->where('status', '!=', 'sincronizado')->count()
            : 0;
    }
}
