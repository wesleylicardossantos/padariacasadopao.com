# Estoque profissional implementado

## O que foi aplicado
- módulo `App\Modules\Estoque`
- tabela `stock_movements` para razão de estoque
- serviço `StockLedgerService` para entradas, saídas e ajustes
- sincronização automática com a tabela legada `estoques`
- bootstrap automático do saldo inicial a partir do legado
- comando `php artisan stock:rebuild-ledger {empresa_id?}`
- rotas enterprise em `/enterprise/estoque`

## Objetivo
Substituir a lógica de saldo puro por uma estrutura auditável de movimentações, mantendo compatibilidade com o legado.

## Integração feita
O helper legado `App\Helpers\StockMove` passa a registrar movimentações no razão e a manter a tabela `estoques` sincronizada.
