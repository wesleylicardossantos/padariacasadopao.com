# Governança do Estoque e Ledger

## Objetivo desta etapa
Consolidar o ledger como trilha oficial de estoque sem quebrar o sistema legado.

## O que foi implementado
- Form Requests para entrada, saída e ajuste de estoque.
- Observer do modelo `Estoque` para auditar escritas diretas fora do ledger.
- Tabela `stock_write_audits` para registrar violações e compatibilidades do fluxo legado.
- `LegacyStockWriteGuard` para permitir apenas as escritas de projeção feitas pelo `StockLedgerService`.
- Comando `stock:write-guard-report --write` para gerar relatório de hotspots e violações recentes.
- `stock:reconcile --write` ampliado para gravar JSON e Markdown em `docs/operacao`.
- Endpoints enterprise para reconciliação e relatório de guardas.

## Regra operacional
- Novos fluxos devem registrar movimentação via `StockLedgerService`.
- A tabela `estoques` permanece como projeção de compatibilidade temporária.
- O bloqueio duro de escritas diretas só deve ser ativado depois de observar produção com o monitoramento ligado.

## Flags de ambiente
- `STOCK_MONITOR_DIRECT_LEGACY_WRITES=true`
- `STOCK_BLOCK_DIRECT_LEGACY_WRITES=false`
- `STOCK_GOVERNANCE_REPORT_PATH=docs/operacao/stock_governance_report.json`

## Comandos
```bash
php artisan stock:reconcile 1 --write
php artisan stock:write-guard-report --write
```

## Próximo passo sugerido
Usar os relatórios para eliminar os hotspots de escrita direta antes de elevar `STOCK_BLOCK_DIRECT_LEGACY_WRITES` para `true`.
