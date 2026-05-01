# Release Notes — Dashboard RH Executivo SaaS Premium

## O que entrou nesta rodada
- filtro por competência no topo do dashboard (`mes` e `ano`)
- modo escuro com persistência no navegador
- cards de tendência com comparação contra a competência anterior
- comparação de folha líquida, headcount, absenteísmo e movimento líquido
- suporte do controller para filtros por query string
- backend do dashboard ajustado para montar competência selecionada manualmente
- cálculo de absenteísmo do mês anterior para análise comparativa
- cache separado por empresa, mês e ano selecionados

## Arquivos principais alterados
- `app/Modules/RH/Controllers/DashboardController.php`
- `app/Modules/RH/Services/RHDashboardModuleService.php`
- `resources/views/rh/dashboard.blade.php`

## Observações
- o processamento automático da folha continua para a competência atual
- ao escolher competência passada, o dashboard não força reprocessamento automático
- o modo escuro é visual e salvo via `localStorage`
