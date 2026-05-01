<?php

namespace App\Services;

use App\Models\Funcionario;
use App\Models\RHPortalFuncionario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RHPortalAcessoService
{
    public function criarOuObter(Funcionario $funcionario): RHPortalFuncionario
    {
        return RHPortalFuncionario::firstOrCreate(
            ['funcionario_id' => (int) $funcionario->id],
            [
                'empresa_id' => (int) $funcionario->empresa_id,
                'ativo' => true,
            ]
        );
    }


    public function ativarParaFuncionario(Funcionario $funcionario, ?int $perfilId = null, array $permissoesExtras = []): RHPortalFuncionario
    {
        $acesso = $this->criarOuObter($funcionario);
        $acesso->empresa_id = (int) $funcionario->empresa_id;
        $acesso->ativo = true;
        if ($perfilId) {
            $acesso->perfil_id = $perfilId;
        }
        if ($permissoesExtras !== []) {
            $acesso->permissoes_extras = array_values(array_unique($permissoesExtras));
        }
        $acesso->save();

        return $acesso;
    }

    public function desativarParaFuncionario(Funcionario $funcionario): void
    {
        RHPortalFuncionario::query()
            ->where('empresa_id', (int) $funcionario->empresa_id)
            ->where('funcionario_id', (int) $funcionario->id)
            ->update(['ativo' => false]);
    }

    public function relatoriosDisponiveis(?RHPortalFuncionario $acesso): array
    {
        if (!$acesso) {
            return [];
        }

        return array_values(array_filter([
            $acesso->hasPermission('dashboard.visualizar') ? 'dashboard' : null,
            $acesso->hasPermission('holerites.visualizar') ? 'holerites' : null,
            $acesso->hasPermission('produtos.visualizar') ? 'produtos' : null,
            $acesso->hasPermission('documentos.visualizar') ? 'documentos' : null,
            $acesso->hasPermission('comissoes.visualizar') ? 'comissoes' : null,
            $acesso->hasPermission('pedidos.visualizar') ? 'pedidos' : null,
            $acesso->hasPermission('dossie.visualizar') ? 'dossie' : null,
        ]));
    }

    public function gerarTokenPrimeiroAcesso(Funcionario $funcionario): RHPortalFuncionario
    {
        $acesso = $this->criarOuObter($funcionario);
        $acesso->empresa_id = (int) $funcionario->empresa_id;
        $acesso->ativo = true;
        $acesso->token_primeiro_acesso = Str::random(48);
        $acesso->token_recuperacao = null;
        $acesso->token_expira_em = Carbon::now()->addDays(3);
        $acesso->save();

        return $acesso;
    }

    public function gerarTokenRecuperacao(Funcionario $funcionario): RHPortalFuncionario
    {
        $acesso = $this->criarOuObter($funcionario);
        $acesso->token_recuperacao = Str::random(48);
        $acesso->token_expira_em = Carbon::now()->addHours(12);
        $acesso->save();

        return $acesso;
    }

    public function definirSenha(RHPortalFuncionario $acesso, string $senha): RHPortalFuncionario
    {
        $acesso->senha = Hash::make($senha);
        $acesso->token_primeiro_acesso = null;
        $acesso->token_recuperacao = null;
        $acesso->token_expira_em = null;
        $acesso->ativo = true;
        if ($this->supportsTentativas($acesso)) {
            $acesso->tentativas_login = 0;
            $acesso->bloqueado_ate = null;
        }
        $acesso->save();

        return $acesso;
    }

    public function senhaValida(RHPortalFuncionario $acesso, string $senha): bool
    {
        if ($this->estaBloqueado($acesso)) {
            return false;
        }

        $senhaOk = !empty($acesso->senha) && Hash::check($senha, $acesso->senha);

        if ($senhaOk) {
            $this->limparTentativas($acesso);
            return true;
        }

        $this->registrarFalha($acesso);
        return false;
    }

    public function registrarLogin(RHPortalFuncionario $acesso, ?string $ip = null): RHPortalFuncionario
    {
        $acesso->ultimo_login_em = now();
        $acesso->ultimo_login_ip = $ip;
        if ($this->supportsTentativas($acesso)) {
            $acesso->tentativas_login = 0;
            $acesso->bloqueado_ate = null;
        }
        $acesso->save();

        $this->auditar($acesso, 'login_sucesso', ['ip' => $ip]);

        return $acesso;
    }

    public function tokenValido(?RHPortalFuncionario $acesso, string $campoToken): bool
    {
        if (!$acesso || empty($acesso->{$campoToken})) {
            return false;
        }

        return !$acesso->token_expira_em || $acesso->token_expira_em->isFuture();
    }

    public function primeiroAcessoUrl(RHPortalFuncionario $acesso): string
    {
        return url('/portal/primeiro-acesso/' . $acesso->token_primeiro_acesso);
    }

    public function recuperacaoUrl(RHPortalFuncionario $acesso): string
    {
        return url('/portal/redefinir-senha/' . $acesso->token_recuperacao);
    }

    public function mensagemBloqueio(RHPortalFuncionario $acesso): ?string
    {
        if (!$this->estaBloqueado($acesso)) {
            return null;
        }

        $minutos = max(1, now()->diffInMinutes(Carbon::parse($acesso->bloqueado_ate), false) * -1);
        return 'Acesso temporariamente bloqueado. Tente novamente em aproximadamente ' . $minutos . ' minuto(s).';
    }

    private function registrarFalha(RHPortalFuncionario $acesso): void
    {
        if (!$this->supportsTentativas($acesso)) {
            return;
        }

        $acesso->tentativas_login = (int) ($acesso->tentativas_login ?? 0) + 1;
        $limite = (int) env('RH_PORTAL_MAX_TENTATIVAS', 5);
        if ($acesso->tentativas_login >= $limite) {
            $acesso->bloqueado_ate = now()->addMinutes((int) env('RH_PORTAL_BLOQUEIO_MINUTOS', 15));
        }
        $acesso->save();

        $this->auditar($acesso, 'login_falha', [
            'tentativas' => (int) $acesso->tentativas_login,
            'bloqueado_ate' => $acesso->bloqueado_ate,
        ]);
    }

    private function limparTentativas(RHPortalFuncionario $acesso): void
    {
        if (!$this->supportsTentativas($acesso)) {
            return;
        }

        $acesso->tentativas_login = 0;
        $acesso->bloqueado_ate = null;
        $acesso->save();
    }

    private function estaBloqueado(RHPortalFuncionario $acesso): bool
    {
        return $this->supportsTentativas($acesso)
            && !empty($acesso->bloqueado_ate)
            && Carbon::parse($acesso->bloqueado_ate)->isFuture();
    }

    private function supportsTentativas(RHPortalFuncionario $acesso): bool
    {
        try {
            return Schema::hasColumn($acesso->getTable(), 'tentativas_login')
                && Schema::hasColumn($acesso->getTable(), 'bloqueado_ate');
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function auditar(RHPortalFuncionario $acesso, string $evento, array $payload = []): void
    {
        try {
            if (!Schema::hasTable('portal_audit_logs')) {
                return;
            }

            DB::table('portal_audit_logs')->insert([
                'empresa_id' => $acesso->empresa_id,
                'funcionario_id' => $acesso->funcionario_id,
                'rh_portal_funcionario_id' => $acesso->id,
                'evento' => $evento,
                'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'ip' => $payload['ip'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // falha de auditoria não pode derrubar login
        }
    }
}
