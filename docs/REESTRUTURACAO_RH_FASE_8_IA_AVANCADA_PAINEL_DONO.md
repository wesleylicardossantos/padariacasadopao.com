# Reestruturação RH — Fase 8

## Entregas
- criação do **Painel do Dono** em `/rh/painel-dono`
- evolução da **IA de decisão** para um motor avançado com:
  - score consolidado
  - anomalias contra a série recente
  - forecast de 3 meses
  - radar de gestão
  - prioridades táticas
  - cenários de estresse e expansão
- manutenção da integração RH + Financeiro
- views em estilo mais executivo para dono/gestão

## Arquivos principais
- `app/Modules/RH/Services/RHDecisionEngineService.php`
- `app/Modules/RH/Services/RHOwnerDashboardService.php`
- `app/Modules/RH/Controllers/PainelDonoController.php`
- `resources/views/rh/ia_decisao/index.blade.php`
- `resources/views/rh/painel_dono/index.blade.php`
- `app/Modules/RH/Routes/web.php`

## Rotas novas
- `/rh/painel-dono`

## Observação técnica
O motor desta fase é **preditivo/heurístico** com base em dados reais do sistema, série histórica, score, cenários e regras de decisão. Ele não depende de API externa para funcionar.
