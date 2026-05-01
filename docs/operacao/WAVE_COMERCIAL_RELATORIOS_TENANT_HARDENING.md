# Wave Comercial/Relatórios Tenant Hardening

- Gerado em: 2026-04-10 21:26:17
- Alias tenant.context registrado: sim

## Rotas auditadas

| Rota | Existe | tenant.context | verificaEmpresa |
|---|---|---|---|
| dre.index | sim | sim | sim |
| dre.list | sim | sim | sim |
| dre.imprimir | sim | sim | sim |
| relatorios.index | sim | sim | sim |
| relatorios.soma-vendas | sim | sim | sim |
| relatorios.vendaProdutos | sim | sim | sim |
| produtos.index | sim | sim | sim |
| produtos.movimentacao | sim | sim | sim |
| produtos.duplicar | sim | sim | sim |
| vendas.index | sim | sim | sim |
| vendas.clone | sim | sim | sim |
| vendas.print | sim | sim | sim |
| pedidos.index | sim | sim | sim |
| pedidos.verMesa | sim | sim | sim |
| pedidos.finalizar | sim | sim | sim |

## Controllers auditados

| Controller | InteractsWithTenantContext |
|---|---|
| RelatorioController | sim |
| DreController | sim |
| ProductController | sim |
| VendaController | sim |
| PedidoController | sim |

- Nesta wave, Relatórios, DRE, Produtos, Vendas e Pedidos passaram a endurecer tenancy por middleware e controllers centrais.
- Buscas por ID críticas foram blindadas com escopo por empresa para reduzir risco cross-tenant.
