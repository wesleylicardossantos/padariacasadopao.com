<?php

namespace App\Modules\SaaS\Services;

use App\Models\Empresa;
use App\Modules\SaaS\Models\SaasTenantSetting;
use Illuminate\Support\Facades\Schema;

class OnboardingService
{
    public function status(int $empresaId): array
    {
        $empresa = Empresa::find($empresaId);
        $settings = Schema::hasTable('saas_tenant_settings')
            ? SaasTenantSetting::query()->where('empresa_id', $empresaId)->pluck('setting_value', 'setting_key')->toArray()
            : [];

        $checklist = [
            'empresa_cadastrada' => (bool) $empresa,
            'nome_fantasia' => ! empty($empresa?->nome_fantasia),
            'email' => ! empty($empresa?->email),
            'telefone' => ! empty($empresa?->telefone),
            'tenant_settings' => ! empty($settings),
        ];

        return [
            'empresa_id' => $empresaId,
            'progress_percent' => round((collect($checklist)->filter()->count() / max(count($checklist), 1)) * 100, 2),
            'checklist' => $checklist,
            'settings' => $settings,
        ];
    }
}
