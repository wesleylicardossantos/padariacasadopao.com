<?php

namespace App\Modules\SaaS\Services;

use App\Models\PlanoEmpresa;
use Illuminate\Support\Facades\Schema;

class SubscriptionService
{
    public function activeByEmpresa(int $empresaId)
    {
        if (! Schema::hasTable('plano_empresas')) {
            return null;
        }

        return PlanoEmpresa::query()->with('plano')->where('empresa_id', $empresaId)->latest()->first();
    }
}
