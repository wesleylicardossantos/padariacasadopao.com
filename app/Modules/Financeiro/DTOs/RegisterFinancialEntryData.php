<?php

namespace App\Modules\Financeiro\DTOs;

use App\Modules\Financeiro\Support\FinancialEntryValidator;
use App\Support\Tenancy\ScopedFilialResolver;
use Illuminate\Http\Request;
use InvalidArgumentException;

class RegisterFinancialEntryData
{
    public function __construct(
        public readonly int $empresaId,
        public readonly ?int $filialId,
        public readonly ?int $categoriaId,
        public readonly ?int $partyId,
        public readonly string $reference,
        public readonly float $amount,
        public readonly string $dueDate,
        public readonly ?string $paymentType,
        public readonly ?string $observation,
        public readonly ?int $sourceId = null,
    ) {
    }

    public static function fromRequest(Request $request, int $empresaId, string $partyKey = 'cliente_id'): self
    {
        $payload = app(FinancialEntryValidator::class)->validateReceivablePayload($empresaId, [
            'filial_id' => $request->input('filial_id'),
            'categoria_id' => $request->input('categoria_id'),
            $partyKey => $request->input($partyKey),
            'referencia' => $request->input('referencia', 'Lançamento manual ERP'),
            'valor_integral' => $request->input('valor_integral', 0),
            'data_vencimento' => $request->input('data_vencimento'),
            'tipo_pagamento' => $request->input('tipo_pagamento'),
            'observacao' => $request->input('observacao'),
        ]);

        if ($partyKey === 'fornecedor_id') {
            $payload = app(FinancialEntryValidator::class)->validatePayablePayload($empresaId, [
                'filial_id' => $request->input('filial_id'),
                'categoria_id' => $request->input('categoria_id'),
                $partyKey => $request->input($partyKey),
                'referencia' => $request->input('referencia', 'Lançamento manual ERP'),
                'valor_integral' => $request->input('valor_integral', 0),
                'data_vencimento' => $request->input('data_vencimento'),
                'tipo_pagamento' => $request->input('tipo_pagamento'),
                'observacao' => $request->input('observacao'),
            ]);
        }

        $amount = (float) ($payload['valor_integral'] ?? 0);
        $dueDate = (string) ($payload['data_vencimento'] ?? '');

        if ($empresaId <= 0) {
            throw new InvalidArgumentException('Empresa inválida para lançamento financeiro.');
        }

        return new self(
            empresaId: $empresaId,
            filialId: ScopedFilialResolver::resolveForEmpresa(
                $empresaId,
                $payload['filial_id'] ?? $request->input('filial_id'),
                $request,
            ),
            categoriaId: isset($payload['categoria_id']) ? (int) $payload['categoria_id'] : null,
            partyId: isset($payload[$partyKey]) ? (int) $payload[$partyKey] : null,
            reference: trim((string) ($payload['referencia'] ?? 'Lançamento manual ERP')),
            amount: round($amount, 2),
            dueDate: $dueDate,
            paymentType: (string) ($payload['tipo_pagamento'] ?? ''),
            observation: trim((string) ($payload['observacao'] ?? '')),
            sourceId: $request->filled('source_id') ? (int) $request->input('source_id') : null,
        );
    }

    public function toReceivableAttributes(): array
    {
        return [
            'empresa_id' => $this->empresaId,
            'filial_id' => $this->filialId,
            'categoria_id' => $this->categoriaId,
            'cliente_id' => $this->partyId,
            'referencia' => $this->reference,
            'valor_integral' => $this->amount,
            'valor_recebido' => 0,
            'data_vencimento' => $this->dueDate,
            'status' => false,
            'tipo_pagamento' => $this->paymentType ?: '',
            'venda_id' => $this->sourceId,
        ];
    }

    public function toPayableAttributes(): array
    {
        return [
            'empresa_id' => $this->empresaId,
            'filial_id' => $this->filialId,
            'categoria_id' => $this->categoriaId,
            'fornecedor_id' => $this->partyId,
            'referencia' => $this->reference,
            'valor_integral' => $this->amount,
            'valor_pago' => 0,
            'data_vencimento' => $this->dueDate,
            'status' => false,
            'tipo_pagamento' => $this->paymentType ?: '',
            'compra_id' => $this->sourceId,
        ];
    }
}
