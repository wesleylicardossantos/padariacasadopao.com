# Wave PDV/Estoque Tenant Hardening

- Gerado em: 2026-04-10 20:46:02
- Alias tenant.context registrado: sim

## Rotas auditadas

| Rota | Existe | tenant.context | verificaEmpresa |
|---|---|---|---|
| estoque.index | sim | sim | sim |
| estoque.store | sim | sim | sim |
| estoque.apontamentoManual | sim | sim | sim |
| estoque.listaApontamento | sim | sim | sim |
| estoque.apontamentoProducao | sim | sim | sim |
| estoque.todosApontamentos | sim | sim | sim |
| estoque.storeApontamento | sim | sim | sim |
| estoque.set-estoque-local | sim | sim | sim |
| pdv.offline.monitor | sim | sim | sim |
| pdv.offline.monitor.data | sim | sim | sim |
| pdv.offline.monitor.reenviar_pendentes | sim | sim | sim |
| pdv.offline.monitor.reenviar_erros | sim | sim | sim |

## Rotas mobile/pdv

| URI | tenant.context |
|---|---|

## Controllers auditados

| Controller | InteractsWithTenantContext |
|---|---|
| StockController | sim |
| ProdutoController | sim |
| OfflineBootstrapController | sim |
| OfflineVendaSyncController | sim |
| OfflineSyncMonitorController | sim |

- Nesta wave, StockController e endpoints PDV offline/mobile passaram a endurecer tenancy por middleware e controllers centrais.
- Buscas por produto e filial em ajustes de estoque agora respeitam a empresa atual.
