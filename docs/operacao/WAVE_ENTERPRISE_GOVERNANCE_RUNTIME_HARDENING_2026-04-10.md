# Wave Enterprise Governance Runtime Hardening

- Gerado em: 2026-04-10 22:05:08
- Alias tenant.context registrado: sim

## Controllers auditados

| Controller | InteractsWithTenantContext | Middleware explícito |
|---|---|---|
| GovernanceController | sim | sim |
| GovernanceController | sim | sim |
| GovernanceController | sim | sim |
| GovernanceController | sim | sim |
| GovernanceController | sim | sim |
| GovernanceController | sim | sim |
| GovernanceController | sim | sim |
| MobileController | sim | sim |
| GovernanceController | sim | sim |

## Rotas auditadas

| Rota | Existe | tenant.context | enterpriseAccess | verificaEmpresa | throttle:enterprise |
|---|---|---|---|---|---|
| enterprise.ai.index | sim | sim | sim | sim | sim |
| enterprise.bi.index | sim | sim | sim | sim | sim |
| enterprise.comercial.kpis | sim | sim | sim | sim | sim |
| enterprise.estoque.index | sim | sim | sim | sim | sim |
| enterprise.financeiro.index | sim | sim | sim | sim | sim |
| enterprise.fiscal.snapshot | sim | sim | sim | sim | sim |
| enterprise.pdv.index | sim | sim | sim | sim | sim |
| enterprise.pdv.mobile | sim | sim | sim | sim | sim |
| enterprise.saas.index | sim | sim | sim | sim | sim |

- Nesta wave, a governança enterprise passou a forçar tenant.context no runtime também no nível dos controllers.
- Isso reduz dependência da ordem de carregamento de rotas e unifica a resolução de empresa entre AI, BI, Comercial, Estoque, Financeiro, Fiscal, PDV e SaaS.
