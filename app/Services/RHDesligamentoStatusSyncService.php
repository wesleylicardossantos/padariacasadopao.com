<?php

namespace App\Services;

use App\Models\Funcionario;
use App\Models\RHDesligamento;
use App\Models\RHPortalFuncionario;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class RHDesligamentoStatusSyncService
{
    public function syncEmpresa(int $empresaId, ?array $funcionarioIds = null): int
    {
        if ($empresaId <= 0 || !Schema::hasTable('funcionarios') || !Schema::hasTable('rh_desligamentos')) {
            return 0;
        }

        $query = Funcionario::query()
            ->withoutGlobalScope('rh_status_visibility')
            ->where('empresa_id', $empresaId);

        if (!empty($funcionarioIds)) {
            $query->whereIn('id', array_values(array_unique(array_map('intval', $funcionarioIds))));
        } else {
            $idsComDesligamento = RHDesligamento::query()
                ->where('empresa_id', $empresaId)
                ->distinct()
                ->pluck('funcionario_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            if (empty($idsComDesligamento)) {
                return 0;
            }

            $query->whereIn('id', $idsComDesligamento);
        }

        $alterados = 0;
        foreach ($query->get() as $funcionario) {
            if ($this->syncFuncionario($funcionario, $empresaId)) {
                $alterados++;
            }
        }

        return $alterados;
    }

    public function syncFuncionario(Funcionario|int $funcionario, ?int $empresaId = null): bool
    {
        if (!$funcionario instanceof Funcionario) {
            $funcionario = Funcionario::query()
                ->withoutGlobalScope('rh_status_visibility')
                ->find($funcionario);
        }

        if (!$funcionario) {
            return false;
        }

        $empresaId = $empresaId ?: (int) $funcionario->empresa_id;
        $ultimoDesligamento = RHDesligamento::query()
            ->where('empresa_id', $empresaId)
            ->where('funcionario_id', $funcionario->id)
            ->orderByDesc('data_desligamento')
            ->orderByDesc('id')
            ->first();

        $deveFicarAtivo = true;
        if ($ultimoDesligamento && !empty($ultimoDesligamento->data_desligamento)) {
            $deveFicarAtivo = Carbon::parse($ultimoDesligamento->data_desligamento)->isFuture();
        }

        $novoAtivo = $deveFicarAtivo ? 1 : 0;
        $atualAtivo = (int) ($funcionario->ativo ?? 1) === 1 ? 1 : 0;
        $alterado = false;

        $payload = [];
        if (Schema::hasColumn($funcionario->getTable(), 'ativo')) {
            $payload['ativo'] = $novoAtivo;
        }
        if (Schema::hasColumn($funcionario->getTable(), 'status')) {
            $payload['status'] = $novoAtivo === 1 ? 'Ativo' : 'Inativo';
        }

        if ($payload !== []) {
            $statusAtual = $funcionario->status ?? null;
            $precisaAtualizarStatusTexto = array_key_exists('status', $payload) && $statusAtual !== $payload['status'];

            if ($atualAtivo !== $novoAtivo || $precisaAtualizarStatusTexto) {
                Funcionario::query()
                    ->withoutGlobalScope('rh_status_visibility')
                    ->where('id', $funcionario->id)
                    ->update($payload);

                $funcionario->forceFill($payload);
                $alterado = true;
            }
        }

        $this->syncUsuarioRelaciondo($funcionario, $novoAtivo);
        $this->syncPortal($funcionario, $novoAtivo);

        return $alterado;
    }

    private function syncUsuarioRelaciondo(Funcionario $funcionario, int $ativo): void
    {
        if (empty($funcionario->usuario_id) || !Schema::hasTable('usuarios')) {
            return;
        }

        try {
            $query = Usuario::query()->where('id', (int) $funcionario->usuario_id);
            if (Schema::hasColumn('usuarios', 'empresa_id') && (int) $funcionario->empresa_id > 0) {
                $query->where('empresa_id', (int) $funcionario->empresa_id);
            }
            $query->update(['ativo' => $ativo]);
        } catch (\Throwable $e) {
            // falha de sincronização do usuário não pode derrubar o RH.
        }
    }

    private function syncPortal(Funcionario $funcionario, int $ativo): void
    {
        if (!Schema::hasTable('rh_portal_funcionarios')) {
            return;
        }

        try {
            RHPortalFuncionario::query()
                ->where('funcionario_id', (int) $funcionario->id)
                ->where('empresa_id', (int) $funcionario->empresa_id)
                ->update(['ativo' => $ativo]);
        } catch (\Throwable $e) {
            // falha de sincronização do portal não pode derrubar o RH.
        }
    }
}
