# Wave executiva RH + Tenancy + RBAC

- Gerado em: 2026-04-10 20:08:26
- Rotas RH encontradas: 98
- Alias tenant.context registrado: sim
- Alias rh.permission registrado: sim

## Rotas auditadas

| Rota | Existe | tenant.context | rh.permission |
|---|---|---|---|
| rh.dashboard_executivo.index | sim | sim | sim |
| rh.painel_dono.index | sim | sim | sim |
| rh.dossie.show | sim | sim | sim |
| rh.dossie.documentos.store | sim | sim | sim |
| rh.dossie.documentos.destroy | sim | sim | sim |
| rh.dossie.eventos.store | sim | sim | sim |
| rh.dossie.eventos.destroy | sim | sim | sim |

## Objetivo
Aplicar uma wave de baixo risco que fortalece isolamento por empresa e explicita permissões em pontos críticos do módulo RH sem reescrever o fluxo legado.

## Observações
- O middleware tenant.context foi consolidado no grupo /rh modular.
- As rotas sensíveis de dossiê, documentos e fechamento/reabertura de folha receberam permissão explícita.
- A política atual permanece fail-open quando a infraestrutura ACL ainda não está pronta, reduzindo risco operacional durante migração gradual.
