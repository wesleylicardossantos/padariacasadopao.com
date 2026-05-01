# Reestruturação RH - Fase 6

## Objetivo
Corrigir a inteligência da folha e consolidar a base de cálculo no módulo RH.

## Entregas
- busca robusta da apuração mensal por competência numérica ou textual
- leitura consistente de descontos da competência
- ajuste automático quando o líquido apurado indica descontos não refletidos nos itens
- separação entre descontos manuais e legais
- resumo financeiro da folha calculado pelo serviço modular
- correção do cadastro de apuração mensal para gravar o valor correto de cada evento
- correção do filtro de datas da listagem de apuração

## Arquivos principais
- `app/Services/RHFolhaCalculoService.php`
- `app/Modules/RH/Services/RHFolhaModuleService.php`
- `app/Modules/RH/Services/RHAnalyticsModuleService.php`
- `app/Http/Controllers/ApuracaoMensalController.php`
- `resources/views/rh/folha/index.blade.php`

## Resultado esperado
A tela `RH V6 - Folha Básica` passa a exibir descontos com mais consistência, inclusive quando a apuração foi salva com o mês em formato textual e quando o líquido registrado exige complemento de desconto.
