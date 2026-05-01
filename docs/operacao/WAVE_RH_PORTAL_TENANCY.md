# Wave RH Portal Tenancy

- Gerado em: 2026-04-10 20:27:56
- Alias tenant.context registrado: sim

## Rotas auditadas

| Rota | Existe | tenant.context |
|---|---|---|
| rh.portal_funcionario.index | sim | sim |
| rh.portal_funcionario.pdf | sim | sim |
| rh.portal_externo.enviar_acesso | sim | sim |
| rh.portal_externo.configurar | sim | sim |
| rh.portal_perfis.index | sim | sim |
| rh.portal_perfis.store | sim | sim |
| rh.portal_perfis.update | sim | sim |
| rh.portal_perfis.destroy | sim | sim |

## Controllers auditados

| Controller | InteractsWithTenantContext |
|---|---|
| RHPortalAcessoController | sim |
| RHPortalFuncionarioController | sim |
| RHPortalPerfilController | sim |

- Nesta wave, o portal RH administrativo passou a resolver tenant via TenantContext nos controllers críticos.
- As rotas administrativas do portal RH receberam middleware tenant.context para endurecer o contexto por empresa.
