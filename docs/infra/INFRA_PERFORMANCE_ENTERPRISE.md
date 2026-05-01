# Infra + Performance Enterprise

## Entradas desta fase
- Docker Compose com PHP 8.2, Nginx, MySQL 8, Redis e worker de fila
- Configurações de OPcache e PHP para produção
- Script de deploy seguro
- Script de worker e warmup de cache
- Teste de carga inicial com k6
- Canais de log dedicados para financeiro, PDV, BI e SaaS
- Cache aplicado nas camadas enterprise de Financeiro, BI, DRE e auditoria PDV
- Middleware `tenant.context` para resolver o contexto da empresa via header, request ou sessão
- Migration de índices críticos para Financeiro, Vendas e PDV

## Passos recomendados
1. Copiar `.env` para o servidor e ajustar Redis/MySQL
2. Subir containers com `docker compose up -d --build`
3. Rodar `php artisan migrate`
4. Rodar `bash scripts/deploy-production.sh`
5. Configurar execução contínua do worker ou usar o serviço `queue`
6. Rodar `k6 run tests/load/enterprise-dashboard.js`

## Variáveis recomendadas no .env
```env
APP_ENV=production
APP_DEBUG=false
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=redis
REDIS_PORT=6379
LOG_CHANNEL=stack
LOG_LEVEL=info
```

## Observações
- O legado foi mantido para compatibilidade.
- Os caches expiram em janelas curtas para reduzir inconsistências de leitura.
- Os índices novos são defensivos e pensados para os painéis enterprise e PDV offline.
