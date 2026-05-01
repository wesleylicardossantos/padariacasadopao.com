# Plano operacional VPS + Redis + filas

## Ativações aplicadas no projeto

- `STOCK_BLOCK_DIRECT_LEGACY_WRITES=true`
- `FINANCE_BLOCK_DIRECT_SETTLEMENT_MUTATIONS=true`
- monitor de slow query com persistência em `performance_events`
- tabelas de `cache` / `cache_locks` prontas para driver database ou fallback controlado
- índices adicionais para PDV offline, financeiro e stock movements
- invalidação explícita de cache financeiro por empresa/filial

## Recomendações de runtime

### Host compartilhado (transição)
- `QUEUE_CONNECTION=database`
- `CACHE_DRIVER=file` ou `database`
- `SESSION_DRIVER=file`
- `REDIS_ENABLED=false`

### VPS
- `QUEUE_CONNECTION=redis`
- `CACHE_DRIVER=redis`
- `SESSION_DRIVER=redis` (se houver múltiplos workers / app servers)
- `REDIS_ENABLED=true`
- `QUEUE_ENABLED=true`
- `REPORTS_FORCE_ASYNC_GENERATION=true`

## Workers sugeridos

- `critical`: operações críticas e integrações sensíveis
- `default`: tarefas padrão
- `reports`: exportações e relatórios pesados
- `integrations`: integrações externas

## Scheduler

Executar a cada minuto:

```bash
php artisan schedule:run
```

## Cutover sugerido

1. subir VPS com Redis
2. publicar `.env` com `QUEUE_CONNECTION=redis` e `CACHE_DRIVER=redis`
3. rodar migrations
4. subir workers por fila
5. validar logs de `performance_events`
6. acompanhar `stock_write_audits` e `financial_audits` após corte
