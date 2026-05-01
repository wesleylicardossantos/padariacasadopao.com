# Release Notes - Motor de Cálculo Completo da Folha

## Implementado
- criação de um motor central `RHFolhaEngineService` para cálculo consistente da folha mensal
- cálculo progressivo de INSS com teto previdenciário
- cálculo de IRRF com comparação entre base legal e desconto simplificado mensal
- cálculo automático de FGTS como encargo separado do líquido
- centralização do processamento da competência em `RHFolhaCompetenciaService`
- compatibilidade com o schema real do banco (`evento_salarios`, `funcionario_eventos`, `apuracao_mensals`, `rh_competencias`, `rh_folha_itens`)
- compatibilidade com os nomes reais das colunas de incidência (`incide_*` e `sistema_padrao`)
- sincronização automática dos eventos base do funcionário (`SALARIO`, `INSS`, `IRRF`, `FGTS`)
- suporte no comando Artisan para `--vencimento` e `--filial_id`
- migration de compatibilidade para complementar `rh_competencias` quando a tabela já existe sem colunas de processamento

## Arquivos principais alterados
- `app/Services/RHFolhaEngineService.php`
- `app/Services/RHFolhaCompetenciaService.php`
- `app/Services/RHFolhaCalculoService.php`
- `app/Services/RHDefaultPayrollEventService.php`
- `app/Models/EventoSalario.php`
- `app/Console/Commands/RHFolhaProcessarCompetenciaCommand.php`
- `database/migrations/2026_04_07_000000_extend_rh_competencias_table_if_needed.php`

## Observações
- o motor usa os vínculos ativos de `funcionario_eventos`
- eventos percentuais são calculados sobre o salário base
- eventos com `condicao = diminui` entram como descontos manuais
- `FGTS` é salvo na memória de cálculo e nos itens da folha, mas não reduz o líquido
- a gravação em `rh_folha_itens` e `apuracao_mensals` respeita as colunas existentes no banco
