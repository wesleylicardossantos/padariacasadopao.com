# Execução pesada — Financeiro completo, PDV produção e DRE + BI

## Entregas aplicadas

### Financeiro Enterprise
- rota web: `/enterprise/financeiro`
- rota JSON: `/enterprise/financeiro/kpis`
- camada consolidada:
  - `FinancialMetricsService`
  - `ReceivableService`
  - `PayableService`
  - `CashFlowService`
- indicadores:
  - contas a receber/pagar
  - aging vencido / até 7 dias / 8 a 30 dias
  - projeção de fluxo de caixa em 6 meses
  - top devedores e top fornecedores

### PDV produção
- rota web: `/enterprise/pdv`
- rota JSON: `/enterprise/pdv/audit`
- reprocessamento via POST: `/enterprise/pdv/reprocess`
- comando de console:
  - `php artisan pdv:offline-retry EMPRESA_ID --limit=100`
- auditoria adicionada:
  - health score
  - status operacional
  - divergências críticas
  - validação de schema do monitor

### BI + DRE real
- rota web: `/enterprise/bi`
- rota JSON dashboard: `/enterprise/bi/dashboard`
- rota JSON DRE: `/enterprise/bi/dre`
- visualização consolidada:
  - vendas no mês
  - vendas hoje
  - lucro líquido
  - margem líquida
  - série anual de vendas
  - top produtos
  - top clientes
  - DRE detalhada

## Observações
- a migração foi feita sem remover o legado ativo.
- os módulos enterprise foram ligados por rotas dedicadas para validação progressiva.
- o projeto permanece funcional com convivência entre legado e nova camada modular.
