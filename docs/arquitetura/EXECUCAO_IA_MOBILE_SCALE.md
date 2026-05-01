# Execução IA + PDV Mobile + Escala 1.000 clientes

## Entregas aplicadas
- módulo `AI` com previsões, recomendações e anomalias do PDV
- PWA `PDV Mobile` em `public/mobile-pdv`
- rotas mobile API reutilizando bootstrap e sincronização do PDV
- score de prontidão SaaS para escala
- health check do tenant
- comandos Artisan para insights e snapshot de escala
- migrations para persistência futura de insights e métricas SaaS

## Rotas principais
- `GET /enterprise/ai`
- `GET /enterprise/ai/overview`
- `GET /enterprise/ai/forecast`
- `GET /enterprise/ai/recommendations`
- `GET /enterprise/ai/anomalies`
- `GET /enterprise/pdv/mobile`
- `GET /api/mobile/pdv/bootstrap`
- `POST /api/mobile/pdv/vendas/sincronizar`
- `GET /enterprise/saas/scale-readiness`
- `GET /enterprise/saas/tenant-health`
- `GET /enterprise/saas/platform-overview`

## Comandos
- `php artisan ai:generate-insights EMPRESA_ID`
- `php artisan saas:scale-snapshot --limit=100`

## Observação
A camada de IA entregue usa heurísticas internas e dados do próprio ERP. Não é um modelo generativo treinado externamente; é uma camada analítica aplicada para previsão e decisão operacional.
