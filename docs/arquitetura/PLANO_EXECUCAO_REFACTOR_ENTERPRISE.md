# Plano de execução da refatoração enterprise

## O que foi consolidado nesta entrega
- Registro central de rotas por prioridade em `App\Support\Routing\RouteFileRegistry`.
- Inclusão dos módulos `Financeiro`, `Comercial`, `PDV`, `BI` e `SaaS` em `app/Modules`.
- Serviços transversais por domínio para métricas, fluxo de caixa, DRE, auditoria PDV e billing.
- Endpoints técnicos de governança em `/enterprise/*` para validação rápida da nova arquitetura.
- Comando `php artisan project:inventory --write` para materializar o inventário técnico da base.
- Proteção da rota `/clear-all` com middleware dedicado para ambientes de produção.

## Cronograma implementado na base
1. Inventário e governança técnica.
2. Consolidação da malha de rotas.
3. Padronização modular por domínio.
4. Camada única para KPI financeiro, comercial e BI.
5. Auditoria e reprocessamento inicial do PDV offline.
6. Estrutura inicial para SaaS, planos e billing.

## Próximas evoluções sugeridas dentro da mesma linha
- Migrar controllers legados de Financeiro e Comercial para dentro dos módulos.
- Encapsular consultas complexas em repositories dedicados.
- Substituir views críticas por dashboards modulares.
- Adicionar testes automatizados por domínio usando os endpoints `/enterprise/*`.
