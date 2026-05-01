# Mercado Pago Enterprise

## O que foi integrado
- Webhook em `/api/webhooks/mercadopago`
- Fila com job `ProcessMercadoPagoWebhookJob`
- Log de eventos em `mercadopago_webhook_events`
- Atualização da tabela `payments`
- Renovação automática de `plano_empresas`
- Sincronização de `saas_subscription_cycles`
- Tentativa de baixa em `conta_recebers`

## Arquivos principais
- `app/Http/Controllers/API/MercadoPagoWebhookController.php`
- `app/Jobs/ProcessMercadoPagoWebhookJob.php`
- `app/Services/MercadoPago/MercadoPagoWebhookService.php`
- `app/Models/MercadoPagoWebhookEvent.php`
- `database/migrations/2026_03_27_120000_add_mercadopago_enterprise_support.php`
- `database/migrations/2026_03_27_120100_create_queue_tables_if_not_exists.php`
- `database/sql/mercadopago_webhook_enterprise.sql`
- `.env.hostgator.example`

## Variáveis no .env
```env
QUEUE_CONNECTION=database
MERCADOPAGO_ACCESS_TOKEN=TEST-...
MERCADOPAGO_ACCESS_TOKEN_PRODUCAO=APP_USR-...
MERCADOPAGO_WEBHOOK_SECRET=trocar
MERCADOPAGO_WEBHOOK_URL=https://seu-dominio.com/api/webhooks/mercadopago
```

## Webhook no Mercado Pago
Configure no painel a URL pública HTTPS:
`https://padariacasadopao.com/api/webhooks/mercadopago`
