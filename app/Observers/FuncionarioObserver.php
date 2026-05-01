<?php

namespace App\Observers;

use App\Models\Funcionario;
use App\Models\HistoricoFuncionario;
use App\Models\RHPortalFuncionario;
use App\Services\RH\RHDossieAutomationService;
use Illuminate\Support\Facades\Schema;

class FuncionarioObserver
{
    public function created(Funcionario $funcionario): void
    {
        $this->registrarHistorico($funcionario, 'cadastro', 'Funcionário cadastrado no módulo RH.');
        $this->syncDossie($funcionario);
    }

    public function updated(Funcionario $funcionario): void
    {
        if ($funcionario->wasChanged('salario')) {
            $this->registrarHistorico($funcionario, 'salario', 'Salário atualizado.', $funcionario->getOriginal('salario'), $funcionario->salario);
        }

        if ($funcionario->wasChanged('funcao')) {
            $this->registrarHistorico($funcionario, 'funcao', 'Função atualizada.');
        }

        if ($funcionario->wasChanged('ativo')) {
            $ativo = (int) ($funcionario->ativo ?? 0) === 1;
            $this->registrarHistorico(
                $funcionario,
                'status',
                $ativo ? 'Funcionário reativado.' : 'Funcionário movido para arquivo morto / inativado.',
                (int) ((int) $funcionario->getOriginal('ativo') === 1),
                (int) $ativo
            );
            $this->syncPortalStatus($funcionario, $ativo);
        }

        $this->syncDossie($funcionario);
    }

    private function syncDossie(Funcionario $funcionario): void
    {
        try {
            app(RHDossieAutomationService::class)->syncFuncionario($funcionario, (int) $funcionario->empresa_id);
        } catch (\Throwable $e) {
            // falha de automação não pode derrubar o fluxo principal.
        }
    }

    private function syncPortalStatus(Funcionario $funcionario, bool $ativo): void
    {
        if (!Schema::hasTable('rh_portal_funcionarios')) {
            return;
        }

        try {
            RHPortalFuncionario::query()
                ->where('funcionario_id', $funcionario->id)
                ->where('empresa_id', (int) $funcionario->empresa_id)
                ->update(['ativo' => $ativo]);
        } catch (\Throwable $e) {
            // falha de sincronização do portal não pode derrubar o fluxo principal.
        }
    }

    private function registrarHistorico(Funcionario $funcionario, string $tipo, string $descricao, mixed $valorAnterior = null, mixed $valorNovo = null): void
    {
        if (!Schema::hasTable('historico_funcionarios')) {
            return;
        }

        try {
            HistoricoFuncionario::create([
                'funcionario_id' => $funcionario->id,
                'tipo' => $tipo,
                'descricao' => $descricao,
                'valor_anterior' => is_numeric($valorAnterior) ? $valorAnterior : null,
                'valor_novo' => is_numeric($valorNovo) ? $valorNovo : null,
            ]);
        } catch (\Throwable $e) {
            // histórico não pode derrubar o fluxo principal.
        }
    }
}
