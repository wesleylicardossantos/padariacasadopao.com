# Bloqueios, performance e preparo para VPS

## Flags canônicas

```env
STOCK_BLOCK_DIRECT_LEGACY_WRITES=true
FINANCE_MONITOR_DIRECT_MUTATIONS=true
FINANCE_BLOCK_DIRECT_SETTLEMENT_MUTATIONS=true
FINANCE_BLOCK_DIRECT_MUTATIONS=true
PERFORMANCE_SLOW_QUERY_MONITOR_ENABLED=true
PERFORMANCE_SLOW_QUERY_THRESHOLD_MS=350
PERFORMANCE_SLOW_REQUEST_MONITOR_ENABLED=true
PERFORMANCE_SLOW_REQUEST_THRESHOLD_MS=1200
PERFORMANCE_STORE_DATABASE_EVENTS=true
QUEUE_ENABLED=true
REPORTS_FORCE_ASYNC_GENERATION=true
REDIS_ENABLED=false
```

## Ordem operacional

1. aplicar banco alinhado
2. `php artisan migrate --force`
3. `php artisan optimize:clear`
4. validar dashboard, venda, estoque e financeiro
5. manter bloqueios ativos e observar `stock_write_audits`, `financial_audits` e `performance_events`
6. só então mover para VPS com Redis e workers dedicados

## Consultas rápidas

```sql
SELECT COUNT(*) AS total FROM stock_write_audits;
SELECT COUNT(*) AS total FROM financial_audits;
SELECT COUNT(*) AS total FROM performance_events;
SELECT * FROM performance_events ORDER BY id DESC LIMIT 20;
```

## Rollback imediato

```env
STOCK_BLOCK_DIRECT_LEGACY_WRITES=false
FINANCE_BLOCK_DIRECT_SETTLEMENT_MUTATIONS=false
FINANCE_BLOCK_DIRECT_MUTATIONS=false
```

Depois rode:

```bash
php artisan config:cache
```
