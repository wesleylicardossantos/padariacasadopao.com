<?php

namespace App\Modules\Financeiro\DTOs;

use Illuminate\Http\Request;
use InvalidArgumentException;

class SettleFinancialEntryData
{
    public function __construct(
        public readonly float $paidAmount,
        public readonly string $settlementDate,
        public readonly ?string $paymentType,
        public readonly ?string $observation,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $rawAmount = $request->input('valor_pago', $request->input('valor_recebido', 0));
        $paidAmount = function_exists('__convert_value_bd')
            ? (float) __convert_value_bd($rawAmount)
            : (float) str_replace(',', '.', (string) $rawAmount);
        $settlementDate = (string) $request->input('data_pagamento', $request->input('data_recebimento', now()->toDateString()));

        if ($paidAmount <= 0) {
            throw new InvalidArgumentException('O valor de liquidação deve ser maior que zero.');
        }

        if ($settlementDate === '') {
            throw new InvalidArgumentException('A data de liquidação é obrigatória.');
        }

        return new self(
            paidAmount: round($paidAmount, 2),
            settlementDate: $settlementDate,
            paymentType: $request->input('tipo_pagamento') ?: null,
            observation: $request->input('observacao') ?: null,
        );
    }
}
