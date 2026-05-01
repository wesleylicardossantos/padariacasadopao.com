<?php

namespace App\Modules\Fiscal\Services;

class InvoiceService
{
    public function preparePayload(array $sale, array $company, array $customer = []): array
    {
        $items = array_values((array) ($sale['itens'] ?? []));
        $totals = app(TaxCalculatorService::class)->estimateTotals(
            $items,
            (float) ($sale['desconto'] ?? 0),
            (float) ($sale['acrescimo'] ?? 0)
        );

        return [
            'empresa' => [
                'id' => $company['id'] ?? null,
                'razao_social' => $company['razao_social'] ?? null,
                'cnpj' => $company['cnpj'] ?? null,
            ],
            'cliente' => [
                'id' => $customer['id'] ?? null,
                'nome' => $customer['razao_social'] ?? $customer['nome'] ?? null,
                'cpf_cnpj' => $customer['cpf_cnpj'] ?? null,
            ],
            'venda' => [
                'id' => $sale['id'] ?? null,
                'itens' => $items,
                'pagamentos' => array_values((array) ($sale['pagamentos'] ?? [])),
                'totais' => $totals,
                'observacao' => $sale['observacao'] ?? null,
            ],
            'prepared_at' => now()->toDateTimeString(),
        ];
    }
}
