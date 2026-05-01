# PDV Offline - fase produção

## Entregas
- status padronizados: `pendente`, `sincronizando`, `sincronizado`, `duplicado`, `erro`
- timestamp de sincronização também em duplicidade
- rastreio de tentativas (`tentativas`, `ultima_tentativa_em`)
- persistência segura de `request_payload` e `response_payload`
- lock pessimista no registro `uuid_local` durante a sincronização
- endpoint de dashboard para monitoramento rápido
- validação mínima do payload para reduzir erro silencioso

## Endpoint novo
- `GET|POST /pdv/sync/dashboard`

## Rotas úteis
- `POST /pdv/vendas/sincronizar`
- `GET|POST /pdv/sync/status`
- `GET|POST /pdv/sync/dashboard`

## Pós deploy
```bash
php artisan migrate
php artisan optimize:clear
```

## Fallback sem artisan
Executar:
- `database/sql/pdv_offline_syncs_full_fix.sql`
- `database/sql/pdv_offline_syncs_retry_patch.sql`
