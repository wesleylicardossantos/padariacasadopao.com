# Release Notes — RH RBAC + Dashboard Executivo + Automação do Dossiê

## Entregas
- RBAC próprio do RH com papéis e permissões
- Middleware `rh.permission` aplicado ao Dashboard Executivo e ao Dossiê
- Automação do dossiê com sync idempotente por `source_uid`
- Dashboard Executivo com métricas reais do dossiê
- Command agendado `rh:dossie-sync`
- Hardening adicional do upload do dossiê

## Novas estruturas
- `rh_acl_permissoes`
- `rh_acl_papeis`
- `rh_acl_papel_permissoes`
- `rh_acl_papel_usuarios`
- `rh_dossie_eventos.source_uid`

## Comando manual
```bash
php artisan rh:dossie-sync
```

## Scheduler
Executa diariamente às 06:05.

## Permissões seed padrão
- `rh.dashboard.visualizar`
- `rh.dashboard.executivo`
- `rh.dossie.visualizar`
- `rh.dossie.documentos.gerenciar`
- `rh.dossie.documentos.excluir`
- `rh.dossie.eventos.gerenciar`
- `rh.dossie.automacao.executar`
- `rh.acl.gerenciar`
