<?php

use App\Models\Funcionario;
use App\Services\RHDefaultPayrollEventService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('funcionarios')) {
            return;
        }

        $service = app(RHDefaultPayrollEventService::class);
        $empresaIds = Funcionario::query()->select('empresa_id')->distinct()->pluck('empresa_id');
        foreach ($empresaIds as $empresaId) {
            $empresaId = (int) $empresaId;
            if ($empresaId <= 0) {
                continue;
            }
            $service->ensureDefaultsForEmpresa($empresaId);
            Funcionario::query()->where('empresa_id', $empresaId)->orderBy('id')->chunk(100, function ($items) use ($service, $empresaId) {
                foreach ($items as $funcionario) {
                    $service->syncFuncionarioBaseEvents($funcionario, $empresaId);
                }
            });
        }
    }

    public function down(): void
    {
        // sem rollback destrutivo para não remover vínculos já usados em apurações.
    }
};
