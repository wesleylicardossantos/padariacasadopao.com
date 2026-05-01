# Release Notes - Holerite PDF + Portal + Processamento Admin

## Entregas
- tela dedicada de holerites no portal externo do funcionário
- geração e download de holerite PDF já integrada ao portal
- central administrativa para processamento da folha por competência
- atalhos da apuração mensal para a nova tela administrativa
- ações de processar, fechar e reabrir competência a partir da interface

## Arquivos principais
- app/Http/Controllers/RHPortalFuncionarioController.php
- app/Http/Controllers/RHFolhaProcessamentoController.php
- resources/views/rh/portal_funcionario/index_externo.blade.php
- resources/views/rh/portal_funcionario/holerites_externo.blade.php
- resources/views/rh/folha_processamento/index.blade.php
- resources/views/apuracao_mensal/index.blade.php
- routes/web.php
- routes/web1.php
- routes/web2.php
- routes/legacy/web1.php
- routes/legacy/web2.php
