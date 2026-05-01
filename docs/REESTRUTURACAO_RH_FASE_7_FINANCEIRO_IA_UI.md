# Reestruturação RH - Fase 7

## Entregas
- UI principal do RH modernizada em estilo SaaS/Conta Azul nas telas:
  - `resources/views/rh/folha/index.blade.php`
  - `resources/views/rh/folha/resumo_financeiro.blade.php`
  - `resources/views/rh/ia_decisao/index.blade.php`
- Integração real entre Financeiro e RH via serviço:
  - `app/Modules/RH/Services/RHFinanceiroIntegrationService.php`
- Motor de decisão com score, drivers, cenários e recomendações:
  - `app/Modules/RH/Services/RHDecisionEngineService.php`
- Refatoração analítica para usar a nova base integrada:
  - `app/Modules/RH/Services/RHAnalyticsModuleService.php`
  - `app/Modules/RH/Services/RHFolhaModuleService.php`

## O que a IA de decisão agora considera
- receita prevista
- receita efetivamente recebida
- despesas previstas e pagas
- folha líquida
- RH total (salários + eventos + encargos + benefícios + provisões)
- peso da folha sobre receita e caixa
- capital comprometido
- cobertura da folha pelo caixa
- simulação de contratação
- simulação de reajuste geral

## Observações
- O motor de decisão é heurístico e transparente; não depende de API externa.
- As recomendações são explicáveis com base nos indicadores exibidos na tela.
- A integração foi feita usando tabelas já existentes do projeto (`conta_recebers`, `conta_pagars`, `funcionarios`, apuração/folha).
