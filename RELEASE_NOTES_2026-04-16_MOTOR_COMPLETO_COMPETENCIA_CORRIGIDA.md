# Release Notes - Motor completo + competência corrigida

## Implementado
- consolidação do motor de folha mensal usando `RHFolhaEngineService` + `RHFolhaCompetenciaService`
- cálculo automático de salário base, proventos, descontos manuais, INSS progressivo, IRRF e FGTS
- processamento por competência com gravação em `apuracao_mensals` e `rh_folha_itens`
- leitura prioritária do `json_calculo` da apuração ao montar o recibo/holerite, preservando os valores históricos da competência processada

## Correções da competência
- correção da competência exibida no portal e no PDF quando `apuracao_mensals.mes` está salvo por nome (`janeiro`, `fevereiro`, etc.)
- correção da geração do PDF do portal, que estava convertendo o mês textual para `0`
- correção da ordenação do histórico de holerites por mês real
- padronização do formato `MM/AAAA` em HTML e PDF

## Arquivos principais alterados
- `app/Support/RHCompetenciaHelper.php`
- `app/Services/RHFolhaCalculoService.php`
- `app/Services/RHFolhaCompetenciaService.php`
- `app/Services/RHHoleritePdfService.php`
- `app/Modules/RH/Services/RHFolhaModuleService.php`
- `app/Http/Controllers/RHFolhaProcessamentoController.php`
- `app/Http/Controllers/RHPortalFuncionarioController.php`
- `resources/views/rh/holerite/pdf.blade.php`
- `resources/views/rh/folha/recibo.blade.php`
- `resources/views/rh/portal_funcionario/index.blade.php`
- `resources/views/rh/portal_funcionario/index_externo.blade.php`
- `resources/views/rh/portal_funcionario/holerites_externo.blade.php`

## Observação operacional
- para refletir os valores corretos por competência antiga, gere/regenere a competência no processamento da folha quando necessário
- o PDF agora usa o mês correto mesmo quando o banco guarda o mês por extenso
