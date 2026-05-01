<?php

namespace App\Modules\Comercial\Services;

use App\Modules\Comercial\Repositories\CustomerRepository;
use InvalidArgumentException;

class CustomerLifecycleService
{
    public function __construct(private readonly CustomerRepository $repository)
    {
    }

    public function register(int $empresaId, array $payload): array
    {
        $documento = preg_replace('/\D+/', '', (string) ($payload['cpf_cnpj'] ?? ''));
        $nome = trim((string) ($payload['razao_social'] ?? $payload['nome'] ?? ''));

        if ($empresaId <= 0) {
            throw new InvalidArgumentException('empresa_id é obrigatório para cadastrar cliente.');
        }

        if ($nome === '') {
            throw new InvalidArgumentException('Informe o nome ou razão social do cliente.');
        }

        $customer = $this->repository->upsert($empresaId, [
            'id' => $payload['id'] ?? null,
            'razao_social' => $nome,
            'nome_fantasia' => trim((string) ($payload['nome_fantasia'] ?? '')),
            'cpf_cnpj' => $documento ?: null,
            'telefone' => trim((string) ($payload['telefone'] ?? '')),
            'email' => trim((string) ($payload['email'] ?? '')),
            'rua' => trim((string) ($payload['rua'] ?? '')),
            'numero' => trim((string) ($payload['numero'] ?? '')),
            'bairro' => trim((string) ($payload['bairro'] ?? '')),
            'cep' => trim((string) ($payload['cep'] ?? '')),
        ]);

        return [
            'id' => $customer->id,
            'empresa_id' => $customer->empresa_id,
            'razao_social' => $customer->razao_social,
            'cpf_cnpj' => $customer->cpf_cnpj,
            'salvo_em' => optional($customer->updated_at)->toDateTimeString(),
        ];
    }
}
