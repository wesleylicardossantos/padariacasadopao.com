# Release notes — Dashboard RH SaaS executivo

## Entrega aplicada
- correção da resolução automática de `empresa_id` no dashboard RH, RH V5 e processamento da folha
- fallback inteligente para localizar a empresa correta pelo contexto da sessão, auth e base real
- ativação de gráficos reais com dados de admissões, desligamentos, folha e headcount
- dashboard executivo com KPIs de competência, saúde da folha, alertas, férias e movimentações
- leitura da competência mais recente processada quando a competência atual estiver vazia
- fallback para `rh_folha_fechamentos` quando a apuração mensal ainda não estiver completa
- folha automática no dashboard: se a competência atual não tiver apuração e houver funcionários ativos, o motor tenta processar automaticamente
- manutenção da compatibilidade com bancos legados e colunas opcionais

## Arquivos principais alterados
- `app/Modules/RH/Services/RHDashboardModuleService.php`
- `app/Modules/RH/Controllers/DashboardController.php`
- `app/Http/Controllers/RHController.php`
- `app/Http/Controllers/RHV5DashboardController.php`
- `app/Http/Controllers/RHFolhaProcessamentoController.php`
- `resources/views/rh/dashboard.blade.php`

## Após publicar
Execute:

```bash
php artisan optimize:clear
```

Se quiser forçar uma nova carga do painel e da competência:

```bash
php artisan cache:clear
```
