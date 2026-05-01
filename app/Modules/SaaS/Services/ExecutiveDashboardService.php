<?php

namespace App\Modules\SaaS\Services;

use App\Modules\RH\Services\RHAnalyticsModuleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExecutiveDashboardService
{
    public function __construct(
        private PlanLimitService $limits,
        private BillingService $billing,
        private SubscriptionLifecycleService $lifecycle,
        private OnboardingService $onboarding,
        private TenantHealthService $health,
        private TenantScalabilityService $scale,
        private RHAnalyticsModuleService $rhAnalytics,
    ) {
    }

    public function build(int $empresaId, int $mes, int $ano): array
    {
        $rh = $this->rhAnalytics->montarDashboardExecutivo($empresaId, $mes, $ano);
        $billing = $empresaId > 0 ? $this->billing->summary($empresaId) : [];
        $usage = $empresaId > 0 ? $this->limits->limitsMatrix($empresaId) : [];
        $lifecycle = $empresaId > 0 ? $this->lifecycle->current($empresaId) : [];
        $onboarding = $empresaId > 0 ? $this->onboarding->status($empresaId) : [];
        $health = $empresaId > 0 ? $this->health->health($empresaId) : [];
        $scale = $empresaId > 0 ? $this->scale->readiness($empresaId) : [];

        $receita = $this->receitaReal($empresaId, $mes, $ano, $billing);
        $rhCusto = (float) ($rh['rhAtual'] ?? 0);
        $lucro = $receita - $rhCusto;
        $margem = $receita > 0 ? round(($lucro / $receita) * 100, 2) : 0.0;

        return [
            'empresaId' => $empresaId,
            'mes' => $mes,
            'ano' => $ano,
            'updatedAt' => now()->format('d/m/Y H:i:s'),
            'kpis' => [
                'receita' => round($receita, 2),
                'rh' => round($rhCusto, 2),
                'lucro' => round($lucro, 2),
                'margem' => $margem,
                'recebimentos' => (float) ($billing['valor_total'] ?? 0),
                'usuarios' => $this->countUsuarios($empresaId),
                'clientes' => $this->countClientes($empresaId),
            ],
            'alerts' => array_values(array_unique(array_merge(
                $this->normalizarAlertas($rh['alertas'] ?? []),
                $this->buildSaasAlerts($usage, $lifecycle, $health, $scale)
            ))),
            'rh' => $rh,
            'billing' => $billing,
            'usage' => $usage,
            'lifecycle' => $lifecycle,
            'onboarding' => $onboarding,
            'tenantHealth' => $health,
            'scaleReadiness' => $scale,
        ];
    }

    private function receitaReal(int $empresaId, int $mes, int $ano, array $billing = []): float
    {
        if ($empresaId <= 0) {
            return 0.0;
        }

        $total = 0.0;
        $periodo = function ($query, string $column) use ($mes, $ano) {
            return $query->whereMonth($column, $mes)->whereYear($column, $ano);
        };

        if (Schema::hasTable('conta_recebers') && Schema::hasColumn('conta_recebers', 'valor_recebido')) {
            $q = DB::table('conta_recebers')->where('empresa_id', $empresaId);
            $dateColumn = Schema::hasColumn('conta_recebers', 'data_recebimento') ? 'data_recebimento' : (Schema::hasColumn('conta_recebers', 'data_pagamento') ? 'data_pagamento' : 'created_at');
            if (Schema::hasColumn('conta_recebers', $dateColumn)) {
                $periodo($q, $dateColumn);
            }
            $total += (float) $q->sum('valor_recebido');
        } elseif (Schema::hasTable('conta_receber') && Schema::hasColumn('conta_receber', 'valor_recebido')) {
            $q = DB::table('conta_receber')->where('empresa_id', $empresaId);
            $dateColumn = Schema::hasColumn('conta_receber', 'data_recebimento') ? 'data_recebimento' : (Schema::hasColumn('conta_receber', 'data_pagamento') ? 'data_pagamento' : 'created_at');
            if (Schema::hasColumn('conta_receber', $dateColumn)) {
                $periodo($q, $dateColumn);
            }
            $total += (float) $q->sum('valor_recebido');
        }

        if ($total <= 0 && Schema::hasTable('vendas') && Schema::hasColumn('vendas', 'valor_total')) {
            $q = DB::table('vendas')->where('empresa_id', $empresaId);
            if (Schema::hasColumn('vendas', 'estado_emissao')) {
                $q->whereIn('estado_emissao', ['aprovado', 'novo']);
            }
            $dateColumn = Schema::hasColumn('vendas', 'data_emissao') ? 'data_emissao' : (Schema::hasColumn('vendas', 'data_registro') ? 'data_registro' : 'created_at');
            if (Schema::hasColumn('vendas', $dateColumn)) {
                $periodo($q, $dateColumn);
            }
            $total += (float) $q->sum('valor_total');
        }

        if ($total <= 0 && Schema::hasTable('venda_caixas') && Schema::hasColumn('venda_caixas', 'valor_total')) {
            $q = DB::table('venda_caixas')->where('empresa_id', $empresaId);
            $dateColumn = Schema::hasColumn('venda_caixas', 'data_registro') ? 'data_registro' : 'created_at';
            if (Schema::hasColumn('venda_caixas', $dateColumn)) {
                $periodo($q, $dateColumn);
            }
            $total += (float) $q->sum('valor_total');
        }

        return $total > 0 ? round($total, 2) : round((float) ($billing['valor_total'] ?? 0), 2);
    }

    private function countUsuarios(int $empresaId): int
    {
        return Schema::hasTable('usuarios')
            ? (int) DB::table('usuarios')->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))->count()
            : 0;
    }

    private function countClientes(int $empresaId): int
    {
        return Schema::hasTable('clientes')
            ? (int) DB::table('clientes')->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))->count()
            : 0;
    }

    private function normalizarAlertas(array $alertas): array
    {
        return array_values(array_filter(array_map(fn ($alerta) => trim((string) $alerta), $alertas)));
    }

    private function buildSaasAlerts(array $usage, array $lifecycle, array $health, array $scale): array
    {
        $alerts = [];
        foreach ($usage as $feature => $info) {
            if (($info['allowed'] ?? true) === false) {
                $alerts[] = 'Limite do plano atingido em ' . $feature . '.';
            }
        }
        if (($lifecycle['status'] ?? null) && !in_array($lifecycle['status'], ['active', 'trial', 'ativo'], true)) {
            $alerts[] = 'Assinatura requer atenção: status ' . strtoupper((string) $lifecycle['status']) . '.';
        }
        if (($health['status'] ?? null) && !in_array($health['status'], ['healthy', 'ok', 'saudavel'], true)) {
            $alerts[] = 'Saúde do tenant requer acompanhamento.';
        }
        if (($scale['status'] ?? null) && in_array($scale['status'], ['warning', 'critical'], true)) {
            $alerts[] = 'Escalabilidade do tenant requer ajustes.';
        }
        return $alerts;
    }
}
