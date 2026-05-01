# Hardening final aplicado

## Entregas desta fase
- Configuração central de hardening em `config/hardening.php`
- `RuntimeConfig` para reduzir `env()` direto em runtime crítico
- `system:healthcheck` para validar banco, tabelas críticas e permissões
- `system:safe-optimize` para cache seguro em hospedagem compartilhada
- índices adicionais para PDV offline, estoque, fiscal e auditoria
- cron sugerido para HostGator/cPanel
- checklist de pós-deploy

## Cron recomendado
```bash
* * * * * php /home/USUARIO/public_html/artisan schedule:run >> /dev/null 2>&1
```

## Comandos de validação
```bash
php artisan migrate --force
php artisan system:healthcheck --write
php artisan system:safe-optimize
php artisan stock:reconcile 1
php artisan schema:drift-report --write
```

## Gate de saída
- healthcheck sem falhas
- sem duplicidade em `pdv_offline_syncs`
- estoque reconciliado
- fiscal com tabelas e índices aplicados
- caches recompostos sem erro
