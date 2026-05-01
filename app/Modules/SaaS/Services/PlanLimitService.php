<?php

namespace App\Modules\SaaS\Services;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Fornecedor;
use App\Models\PlanoEmpresa;
use App\Models\Produto;
use App\Modules\SaaS\Models\SaasPlanFeature;
use Illuminate\Support\Facades\Schema;

class PlanLimitService
{
    public function check(int $empresaId, string $feature): array
    {
        $usage = $this->usage($empresaId);
        $limit = $this->limitForFeature($empresaId, $feature);

        if ($limit === null || $limit < 0) {
            return [
                'allowed' => true,
                'used' => $usage[$feature] ?? 0,
                'limit' => $limit,
                'message' => 'Recurso sem limite configurado.',
            ];
        }

        $used = (int) ($usage[$feature] ?? 0);
        return [
            'allowed' => $used <= $limit,
            'used' => $used,
            'limit' => $limit,
            'message' => $used <= $limit
                ? 'Uso dentro do plano.'
                : 'Limite do plano atingido para '.$feature.'.',
        ];
    }

    public function usage(int $empresaId): array
    {
        return [
            'clientes' => Schema::hasTable('clientes') ? Cliente::query()->where('empresa_id', $empresaId)->count() : 0,
            'produtos' => Schema::hasTable('produtos') ? Produto::query()->where('empresa_id', $empresaId)->count() : 0,
            'fornecedores' => Schema::hasTable('fornecedors') ? Fornecedor::query()->where('empresa_id', $empresaId)->count() : 0,
            'usuarios' => Schema::hasTable('usuarios') ? (Empresa::find($empresaId)?->usuarios()->count() ?? 0) : 0,
        ];
    }

    public function limitsMatrix(int $empresaId): array
    {
        $usage = $this->usage($empresaId);
        $matrix = [];
        foreach (array_keys($usage) as $feature) {
            $check = $this->check($empresaId, $feature);
            $matrix[$feature] = [
                'used' => $check['used'],
                'limit' => $check['limit'],
                'available' => $check['limit'] === null ? null : max(0, (int) $check['limit'] - (int) $check['used']),
                'allowed' => $check['allowed'],
                'message' => $check['message'],
            ];
        }
        return $matrix;
    }

    private function limitForFeature(int $empresaId, string $feature): ?int
    {
        if (! Schema::hasTable('plano_empresas')) {
            return null;
        }

        $activePlan = PlanoEmpresa::query()->where('empresa_id', $empresaId)->latest()->first();
        if (! $activePlan) {
            return null;
        }

        $plano = $activePlan->plano;
        $legacyMap = [
            'clientes' => $plano?->maximo_clientes,
            'produtos' => $plano?->maximo_produtos,
            'fornecedores' => $plano?->maximo_fornecedores,
            'usuarios' => $plano?->maximo_usuario,
        ];

        if (array_key_exists($feature, $legacyMap) && $legacyMap[$feature] !== null) {
            return (int) $legacyMap[$feature];
        }

        if (Schema::hasTable('saas_plan_features')) {
            $featureRow = SaasPlanFeature::query()
                ->where('plano_id', $activePlan->plano_id)
                ->where('feature_key', $feature)
                ->first();

            if ($featureRow) {
                return $featureRow->limit_value;
            }
        }

        return null;
    }
}
