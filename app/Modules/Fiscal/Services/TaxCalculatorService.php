<?php

namespace App\Modules\Fiscal\Services;

class TaxCalculatorService
{
    public function estimateTotals(array $items, float $discount = 0, float $increase = 0): array
    {
        $subtotal = collect($items)->sum(function (array $item) {
            $quantity = (float) ($item['quantidade'] ?? 0);
            $unitPrice = (float) ($item['valor_unitario'] ?? $item['valor'] ?? 0);

            return $quantity * $unitPrice;
        });

        $base = max(0, $subtotal - $discount + $increase);

        return [
            'subtotal' => round($subtotal, 2),
            'desconto' => round($discount, 2),
            'acrescimo' => round($increase, 2),
            'base_calculo' => round($base, 2),
            'valor_estimado_impostos' => 0.0,
            'total' => round($base, 2),
        ];
    }
}
