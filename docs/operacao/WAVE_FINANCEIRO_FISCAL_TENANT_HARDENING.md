# Wave Financeiro/Fiscal Tenant Hardening

- Gerado em: 2026-04-10 20:42:32
- Alias tenant.context registrado: sim

## Rotas auditadas

| Rota | Existe | tenant.context | verificaEmpresa |
|---|---|---|---|
| financeiro.index | sim | sim | sim |
| financeiro.list | sim | sim | sim |
| nfse.index | sim | sim | sim |
| nfse.imprimir | sim | sim | sim |
| boletos.index | sim | sim | sim |
| boletos.print | sim | sim | sim |
| boletos.store-issue | sim | sim | sim |
| remessa-boletos.index | sim | sim | sim |
| remessa.sem-remessa | sim | sim | sim |
| remessa-boletos.download | sim | sim | sim |
| contigencia.index | sim | sim | sim |
| contigencia.desactive | sim | sim | sim |
| compraFiscal.index | sim | sim | sim |
| compraFiscal.store | sim | sim | sim |
| compraFiscal.import | sim | sim | sim |

## Controllers auditados

| Controller | InteractsWithTenantContext |
|---|---|
| FinanceiroController | sim |
| NfseController | sim |
| ContigenciaController | sim |
| BoletoController | sim |
| RemessaBoletoController | sim |

- Nesta wave, listagens e buscas por ID em Financeiro/Fiscal passaram a respeitar TenantContext nos controllers críticos.
- As rotas web sensíveis de Financeiro, NFSe, boletos, remessas, contingência e compra fiscal receberam tenant.context.
- Corrigido vazamento de tenancy em FinanceiroController::list, que antes listava pagamentos sem filtrar empresa.
