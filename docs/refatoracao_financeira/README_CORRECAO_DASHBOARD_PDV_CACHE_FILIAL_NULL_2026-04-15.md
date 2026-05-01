# Correção do dashboard financeiro — PDV, cache e filial nula

## Problema corrigido
Os cards do dashboard não refletiam imediatamente as vendas do PDV.

## Causa raiz
1. As vendas do PDV estavam sendo gravadas em `venda_caixas` com `filial_id = null`.
2. As consultas do dashboard filtravam `filial_id` de forma estrita, excluindo registros com filial nula.
3. Os snapshots do dashboard ficavam em cache sem invalidação automática após mutações em vendas, contas e produtos.

## Ajustes aplicados
- inclusão de fallback `orWhereNull('filial_id')` nas consultas de vendas quando a visão está filtrada por filial
- invalidação versionada do cache do dashboard por `empresa_id`
- observer genérico para invalidar o dashboard em mutações de:
  - `Venda`
  - `VendaCaixa`
  - `ContaReceber`
  - `ContaPagar`
  - `Produto`
- endurecimento do filtro de vendas válidas:
  - exclui `cancelado`
  - exclui `rejeitado`
  - exclui `rascunho = 1`
  - exclui `consignado = 1` quando existir
- alinhamento das consultas auxiliares do BI e da auditoria PDV com a mesma regra de vendas válidas

## Arquivos alterados
- `app/Services/DashboardService.php`
- `app/Observers/DashboardCacheObserver.php`
- `app/Providers/AppServiceProvider.php`
- `tests/Feature/Financeiro/DashboardIndicatorsTest.php`

## Resultado esperado
- venda feita no PDV passa a entrar nos cards do dashboard
- atualização dos cards ocorre sem esperar expiração natural do cache
- métricas do mês, do dia, ticket médio e quantidade de vendas ficam coerentes com o PDV
