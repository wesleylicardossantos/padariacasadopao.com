# Refatoração aplicada no dashboard financeiro

## Escopo executado
- Endurecimento da regra de vendas válidas no `app/Services/DashboardService.php`.
- Exclusão de vendas canceladas/rejeitadas do faturamento executivo.
- Exclusão de rascunhos do PDV e respeito ao soft delete em `venda_caixas`.
- Ajuste dos cards de contas a receber e contas a pagar para saldo residual real.
- Ajuste do card de produtos para considerar apenas itens ativos.
- Inclusão de testes automatizados de regressão em `tests/Feature/Financeiro/DashboardIndicatorsTest.php`.
- Inclusão de SQL de auditoria em `database/sql/2026_04_15_dashboard_financeiro_auditoria.sql`.

## Arquivos alterados
- `app/Services/DashboardService.php`
- `tests/Feature/Financeiro/DashboardIndicatorsTest.php`
- `database/sql/2026_04_15_dashboard_financeiro_auditoria.sql`

## Observação importante
Esta entrega não exigiu migration nova. A correção foi feita na camada de regra de negócio e agregação dos indicadores.

## Validação recomendada
1. Conferir o card `Vendas no mês` com as queries SQL de auditoria.
2. Conferir `Qtd. vendas`, `Ticket médio`, `Contas a receber`, `Contas a pagar` e `Saldo projetado` no dashboard.
3. Executar a suíte de testes em um ambiente com extensões PHP de teste habilitadas (`dom`, `mbstring`, `xml`, `xmlwriter`).
