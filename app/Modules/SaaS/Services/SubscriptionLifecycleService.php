<?php

namespace App\Modules\SaaS\Services;

use App\Models\PlanoEmpresa;
use App\Modules\SaaS\Models\SaasSubscriptionCycle;
use Illuminate\Support\Facades\Schema;

class SubscriptionLifecycleService
{
    public function current(int $empresaId): array
    {
        $subscription = PlanoEmpresa::query()->with('plano')->where('empresa_id', $empresaId)->latest()->first();
        $cycle = Schema::hasTable('saas_subscription_cycles')
            ? SaasSubscriptionCycle::query()->where('empresa_id', $empresaId)->latest('period_end')->first()
            : null;

        $today = now()->toDateString();
        $expiresAt = $subscription?->expiracao;
        $status = 'inactive';

        if ($subscription) {
            if ($expiresAt && $expiresAt !== '0000-00-00' && $today <= $expiresAt) {
                $status = 'active';
            } elseif ($cycle?->grace_ends_at && now()->lte($cycle->grace_ends_at)) {
                $status = 'grace_period';
            } else {
                $status = 'expired';
            }
        }

        return [
            'status' => $status,
            'expiracao' => $expiresAt,
            'plano_empresa' => $subscription,
            'cycle' => $cycle,
        ];
    }
}
