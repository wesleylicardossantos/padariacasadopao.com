# Mapa modular enterprise

## Módulos ativos
- `app/Modules/RH`
- `app/Modules/Financeiro`
- `app/Modules/Comercial`
- `app/Modules/PDV`
- `app/Modules/BI`
- `app/Modules/SaaS`

## Endpoints técnicos criados
- `GET /enterprise/financeiro/kpis`
- `GET /enterprise/comercial/kpis`
- `GET /enterprise/pdv/audit`
- `POST /enterprise/pdv/reprocess`
- `GET /enterprise/bi/dashboard`
- `GET /enterprise/bi/dre`
- `GET /enterprise/saas/overview`

## Objetivo dos endpoints
Servem para validar a nova camada de serviços, comparar indicadores, apoiar auditoria e acelerar migração das views legadas.
