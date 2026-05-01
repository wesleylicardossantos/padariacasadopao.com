<?php

namespace App\Modules\Comercial\UseCases;

use App\Modules\Comercial\Repositories\CustomerRepository;
use App\Modules\Comercial\Services\CommercialAuditService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UpsertCustomerUseCase
{
    public function __construct(
        private readonly CustomerRepository $repository,
        private readonly CommercialAuditService $auditService,
    ) {
    }

    public function handle(int $empresaId, array $payload, ?int $usuarioId = null)
    {
        return DB::transaction(function () use ($empresaId, $payload, $usuarioId) {
            $documento = preg_replace('/\D+/', '', (string) ($payload['cpf_cnpj'] ?? '')) ?: null;
            $nome = trim((string) ($payload['razao_social'] ?? $payload['nome'] ?? ''));

            if ($empresaId <= 0) {
                throw new InvalidArgumentException('empresa_id é obrigatório.');
            }

            if ($nome === '') {
                throw new InvalidArgumentException('Informe o nome ou razão social do cliente.');
            }

            $customer = $this->repository->upsert($empresaId, [
                'id' => $payload['id'] ?? null,
                'razao_social' => $nome,
                'nome_fantasia' => trim((string) ($payload['nome_fantasia'] ?? '')),
                'cpf_cnpj' => $documento,
                'telefone' => trim((string) ($payload['telefone'] ?? '')),
                'celular' => trim((string) ($payload['celular'] ?? '')),
                'email' => trim((string) ($payload['email'] ?? '')),
                'rua' => trim((string) ($payload['rua'] ?? '')),
                'numero' => trim((string) ($payload['numero'] ?? '')),
                'bairro' => trim((string) ($payload['bairro'] ?? '')),
                'cep' => trim((string) ($payload['cep'] ?? '')),
                'cidade_id' => $payload['cidade_id'] ?? null,
                'observacao' => $payload['observacao'] ?? null,
                'inativo' => (bool) ($payload['inativo'] ?? false),
            ]);

            $this->auditService->record([
                'empresa_id' => $empresaId,
                'usuario_id' => $usuarioId,
                'entidade' => 'cliente',
                'entidade_id' => $customer->id,
                'acao' => !empty($payload['id']) ? 'atualizado' : 'criado',
                'depois' => $customer->toArray(),
            ]);

            return $customer;
        });
    }
}
