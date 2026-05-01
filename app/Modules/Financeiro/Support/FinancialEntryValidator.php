<?php

namespace App\Modules\Financeiro\Support;

use App\Models\CategoriaConta;
use App\Models\Cliente;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Fornecedor;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class FinancialEntryValidator
{
    public function validateReceivablePayload(int $empresaId, array $attributes): array
    {
        $payload = $this->normalizeCommonPayload($empresaId, $attributes, 'cliente_id');

        if (array_key_exists('cliente_id', $payload) && $payload['cliente_id'] !== null) {
            $this->assertClienteBelongsToEmpresa($empresaId, (int) $payload['cliente_id']);
        }

        if (array_key_exists('venda_id', $attributes) && (int) $attributes['venda_id'] > 0) {
            $payload['venda_id'] = (int) $attributes['venda_id'];
        }

        return $payload;
    }

    public function validatePayablePayload(int $empresaId, array $attributes): array
    {
        $payload = $this->normalizeCommonPayload($empresaId, $attributes, 'fornecedor_id');

        if (array_key_exists('fornecedor_id', $payload) && $payload['fornecedor_id'] !== null) {
            $this->assertFornecedorBelongsToEmpresa($empresaId, (int) $payload['fornecedor_id']);
        }

        if (array_key_exists('compra_id', $attributes) && (int) $attributes['compra_id'] > 0) {
            $payload['compra_id'] = (int) $attributes['compra_id'];
        }

        return $payload;
    }

    public function validateSettlementAmount(float $paidAmount): void
    {
        if ($paidAmount <= 0) {
            throw new InvalidArgumentException('O valor de liquidação deve ser maior que zero.');
        }
    }

    public function assertReceivableBelongsToEmpresa(ContaReceber $receivable, int $empresaId): void
    {
        if ((int) $receivable->empresa_id !== $empresaId) {
            throw new InvalidArgumentException('Conta a receber não pertence à empresa atual.');
        }
    }

    public function assertPayableBelongsToEmpresa(ContaPagar $payable, int $empresaId): void
    {
        if ((int) $payable->empresa_id !== $empresaId) {
            throw new InvalidArgumentException('Conta a pagar não pertence à empresa atual.');
        }
    }

    private function normalizeCommonPayload(int $empresaId, array $attributes, string $partyKey): array
    {
        if ($empresaId <= 0) {
            throw new InvalidArgumentException('Empresa inválida para lançamento financeiro.');
        }

        $reference = trim((string) ($attributes['referencia'] ?? ''));
        if ($reference === '') {
            throw new InvalidArgumentException('A referência do lançamento é obrigatória.');
        }

        $amount = $this->parseMoney($attributes['valor_integral'] ?? 0);
        if ($amount <= 0) {
            throw new InvalidArgumentException('O valor do lançamento deve ser maior que zero.');
        }

        $dueDate = trim((string) ($attributes['data_vencimento'] ?? ''));
        if ($dueDate === '') {
            throw new InvalidArgumentException('A data de vencimento é obrigatória.');
        }

        $this->assertCategoriaBelongsToEmpresa($empresaId, $attributes['categoria_id'] ?? null);

        return [
            'empresa_id' => $empresaId,
            'filial_id' => $this->nullableInt($attributes['filial_id'] ?? null),
            'categoria_id' => $this->nullableInt($attributes['categoria_id'] ?? null),
            $partyKey => $this->nullableInt($attributes[$partyKey] ?? null),
            'referencia' => $reference,
            'valor_integral' => $amount,
            'data_vencimento' => $dueDate,
            'tipo_pagamento' => $this->nullableString($attributes['tipo_pagamento'] ?? null),
            'observacao' => $this->nullableString($attributes['observacao'] ?? null),
        ];
    }

    private function assertCategoriaBelongsToEmpresa(int $empresaId, mixed $categoriaId): void
    {
        $categoriaId = $this->nullableInt($categoriaId);
        if ($categoriaId === null) {
            throw new InvalidArgumentException('A categoria é obrigatória para o lançamento financeiro.');
        }

        $query = CategoriaConta::query()->where('id', $categoriaId)->where('empresa_id', $empresaId);
        if (Schema::hasColumn('categoria_contas', 'tipo')) {
            $query->whereIn('tipo', ['receber', 'pagar']);
        }

        if (! $query->exists()) {
            throw new InvalidArgumentException('A categoria informada não pertence à empresa atual.');
        }
    }

    private function assertClienteBelongsToEmpresa(int $empresaId, int $clienteId): void
    {
        if (! Cliente::query()->where('id', $clienteId)->where('empresa_id', $empresaId)->exists()) {
            throw new InvalidArgumentException('O cliente informado não pertence à empresa atual.');
        }
    }

    private function assertFornecedorBelongsToEmpresa(int $empresaId, int $fornecedorId): void
    {
        if (! Fornecedor::query()->where('id', $fornecedorId)->where('empresa_id', $empresaId)->exists()) {
            throw new InvalidArgumentException('O fornecedor informado não pertence à empresa atual.');
        }
    }

    private function parseMoney(mixed $value): float
    {
        if (function_exists('__convert_value_bd')) {
            return round((float) __convert_value_bd($value), 2);
        }

        $normalized = str_replace(['.', ','], ['', '.'], (string) $value);

        return round((float) $normalized, 2);
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '' || (int) $value <= 0 || (int) $value === -1) {
            return null;
        }

        return (int) $value;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
