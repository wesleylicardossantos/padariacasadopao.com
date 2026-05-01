<?php

namespace App\Modules\SaaS\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Schema;

class BillingService
{
    public function summary(int $empresaId): array
    {
        if (! Schema::hasTable('payments')) {
            return [
                'total_cobrancas' => 0,
                'aprovadas' => 0,
                'pendentes' => 0,
                'valor_total' => 0.0,
            ];
        }

        $query = Payment::query()->where('empresa_id', $empresaId);

        return [
            'total_cobrancas' => (int) (clone $query)->count(),
            'aprovadas' => (int) (clone $query)->where('status', 1)->count(),
            'pendentes' => (int) (clone $query)->where('status', 2)->count(),
            'valor_total' => (float) (clone $query)->sum('valor'),
        ];
    }
}
