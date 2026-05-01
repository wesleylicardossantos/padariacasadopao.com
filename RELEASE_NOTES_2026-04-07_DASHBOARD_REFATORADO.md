# RELEASE NOTES — Dashboard RH Refatorado (2026-04-07)

## O que foi atualizado

### 1) Refatoração do serviço do dashboard RH
Arquivo: `app/Modules/RH/Services/RHDashboardModuleService.php`

- Consolidada a montagem do dashboard em um único serviço mais robusto.
- Adicionados indicadores operacionais e financeiros:
  - total de funcionários
  - ativos / inativos
  - percentual de ativos
  - folha mensal
  - custo médio por colaborador
  - admissões e desligamentos do mês
  - faltas, atrasos e atestados
  - turnover
  - proventos, descontos e líquido da competência
  - ticket médio da folha
  - última competência encontrada
- Adicionado histórico das últimas 6 competências.
- Adicionados alertas prioritários (CNH, ASO e férias).
- Corrigida a carga de férias próximas com `data_fim`.
- Adicionado cache curto para melhorar performance.
- Adicionadas proteções com `Schema::hasTable()` e `Schema::hasColumn()` para evitar quebra em bases com estrutura parcial.

### 2) Unificação do dashboard V5
Arquivo: `app/Http/Controllers/RHV5DashboardController.php`

- O controller V5 agora reaproveita o serviço central do dashboard RH.
- Resultado: dashboard mais consistente e manutenção mais simples.

### 3) Nova interface do dashboard principal
Arquivo: `resources/views/rh/dashboard.blade.php`

- Layout totalmente modernizado.
- Hero principal com atalhos rápidos.
- Cards de KPI mais claros e organizados.
- Blocos separados para:
  - fluxo de pessoas
  - alertas prioritários
  - evolução da folha líquida
  - top salários
  - férias próximas
  - movimentações recentes
- Melhor leitura visual e navegação para operação diária.

## Benefícios

- Dashboard mais confiável mesmo em bases incompletas.
- Melhor performance por cache e queries consolidadas.
- Estrutura pronta para evolução futura sem duplicar regra entre versões.
- Experiência visual mais profissional para operação RH.

## Arquivos principais alterados

- `app/Modules/RH/Services/RHDashboardModuleService.php`
- `app/Http/Controllers/RHV5DashboardController.php`
- `resources/views/rh/dashboard.blade.php`
- `RELEASE_NOTES_2026-04-07_DASHBOARD_REFATORADO.md`
