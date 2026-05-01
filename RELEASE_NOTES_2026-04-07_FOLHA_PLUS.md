# Release Notes - 2026-04-07 - Folha Plus

## Entregas desta atualização

- cálculo oficial atualizado de INSS para 2026
- cálculo oficial atualizado de IRRF para 2026
- suporte ao desconto simplificado mensal da Receita Federal
- suporte à redução mensal de IRRF válida em 2026
- consideração automática de dependentes no cálculo do IRRF
- portal do funcionário com ações separadas de visualizar e baixar PDF
- holerite com bases tributárias e referências legais no layout
- processamento da folha com foco em integração financeira automática
- dashboard RH ampliado com:
  - proventos do mês
  - líquido do mês
  - evolução da folha por 6 competências
  - top salários

## Arquivos principais alterados

- `config/rh_payroll.php`
- `app/Services/RHFolhaEngineService.php`
- `app/Services/RHFolhaCompetenciaService.php`
- `app/Modules/RH/Controllers/HoleriteController.php`
- `app/Http/Controllers/RHPortalFuncionarioController.php`
- `app/Modules/RH/Services/RHDashboardModuleService.php`
- `app/Http/Controllers/RHFolhaProcessamentoController.php`
- `resources/views/rh/dashboard.blade.php`
- `resources/views/rh/folha_processamento/index.blade.php`
- `resources/views/rh/portal_funcionario/holerites_externo.blade.php`

## Observações

- não foi necessária migration nova para essa etapa
- a integração financeira passou a ser tratada como padrão do motor
- o portal continua usando a base existente de apurações e holerites
