# Wave Runtime Hardening de TenantContext

- Gerado em: 2026-04-10 21:48:41
- Alias tenant.context registrado: sim

## Controllers auditados

| Controller | InteractsWithTenantContext | Middleware explícito |
|---|---|---|
| RHPortalFuncionarioController | sim | sim |
| RHPortalPerfilController | sim | sim |
| RHPortalAcessoController | sim | sim |
| FinanceiroController | sim | sim |
| NfseController | sim | sim |
| BoletoController | sim | sim |
| RemessaBoletoController | sim | sim |
| ContigenciaController | sim | sim |
| StockController | sim | sim |
| ProdutoController | sim | sim |
| OfflineBootstrapController | sim | sim |
| OfflineVendaSyncController | sim | sim |
| OfflineSyncMonitorController | sim | sim |

- Nesta wave, controllers críticos que já usavam TenantContext passaram a forçar tenant.context também no runtime via construtor.
- Isso reduz dependência da ordem de carregamento de arquivos de rota e estabiliza o tenancy em ambiente legado.
