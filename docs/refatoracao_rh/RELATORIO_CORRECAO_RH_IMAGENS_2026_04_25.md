# Relatório de correção - RH / SaaS / Hostgator

## Erros identificados nas imagens

1. `SQLSTATE[42S22]: Unknown column 'referencia_tipo' in 'field list'`
   - Arquivo envolvido: `app/Services/RH/RHAdminAuditService.php`.
   - Causa: código gravava `referencia_tipo` e `referencia_id`, mas o banco real possui `alvo_tipo` e `alvo_id` em `rh_admin_action_audits`.
   - Correção: serviço agora detecta as colunas reais da tabela e grava em `referencia_*` ou `alvo_*`, conforme existir. Auditoria não derruba mais dashboard/RH em caso de divergência de schema.

2. `View [layouts.app] not found`
   - Arquivos envolvidos nas imagens: `resources/views/enterprise/saas/scale.blade.php` e `resources/views/enterprise/saas/observability.blade.php`.
   - Causa: views SaaS usam `@extends('layouts.app')`, mas o layout não existia no pacote/servidor.
   - Correção: criado `resources/views/layouts/app.blade.php` com layout Bootstrap seguro e compatível.

## Arquivos criados/alterados

- `app/Services/RH/RHAdminAuditService.php`
- `app/Models/RH/RHAdminActionAudit.php`
- `database/migrations/2026_04_25_233000_harden_rh_admin_action_audits_compatibility.php`
- `database/sql/hostgator/RODAR_AGORA_CORRECAO_RH_AUDIT_E_LAYOUTS.sql`
- `resources/views/layouts/app.blade.php`
- `resources/views/enterprise/saas/scale.blade.php`
- `resources/views/enterprise/saas/observability.blade.php`
- `resources/views/enterprise/saas/executive.blade.php`

## SQL para rodar agora

Rode no phpMyAdmin:

`database/sql/hostgator/RODAR_AGORA_CORRECAO_RH_AUDIT_E_LAYOUTS.sql`

## Validação após aplicar

- Abrir Dashboard SaaS Executivo.
- Abrir SaaS Scale Ops Center.
- Abrir Observability Center.
- Abrir Dashboard HR Executivo.
- Abrir menus RH principais.
- Confirmar que não aparece mais erro de coluna `referencia_tipo`.
- Confirmar que não aparece mais erro `View [layouts.app] not found`.
