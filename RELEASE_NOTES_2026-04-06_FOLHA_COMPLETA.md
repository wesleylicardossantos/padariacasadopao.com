# Release Notes - Implementação completa de folha RH

## Entregas
- criação do motor mensal por competência (`rh_competencias`)
- persistência detalhada dos itens calculados (`rh_folha_itens`)
- padronização da tabela `evento_salarios` para engine de folha
- sincronização automática dos eventos base: SALARIO, INSS, IRRF e FGTS
- atualização automática dos vínculos em `funcionario_eventos` ao cadastrar/editar funcionário
- geração automática da apuração mensal usando o novo motor
- consolidação no fechamento em `rh_folha_fechamentos`
- comando Artisan novo: `php artisan rh:folha-processar EMPRESA MES ANO --sobrescrever --integrar-financeiro`

## Arquivos principais
- `app/Services/RHDefaultPayrollEventService.php`
- `app/Services/RHFolhaCompetenciaService.php`
- `app/Models/RHCompetencia.php`
- `app/Models/RHFolhaItem.php`
- `app/Console/Commands/RHFolhaProcessarCompetenciaCommand.php`
- `app/Modules/RH/Application/Funcionario/FuncionarioService.php`
- `app/Modules/RH/Application/ApuracaoMensal/ApuracaoMensalService.php`
- `database/migrations/2026_04_06_235000_create_rh_competencias_table.php`
- `database/migrations/2026_04_06_235100_create_rh_folha_itens_table.php`
- `database/migrations/2026_04_06_235200_extend_evento_salarios_for_payroll_engine.php`
- `database/migrations/2026_04_06_235300_extend_funcionario_eventos_for_payroll_engine.php`
- `database/migrations/2026_04_06_235400_extend_apuracao_mensals_for_payroll_engine.php`
- `database/migrations/2026_04_06_235500_seed_default_payroll_events_and_sync_salary.php`

## Passos após publicar
```bash
php artisan migrate
php artisan rh:folha-processar 1 4 2026 --sobrescrever
```

## Observações
- o processamento usa as tabelas reais do projeto: `evento_salarios`, `funcionario_eventos`, `apuracao_mensals`, `apuracao_salario_eventos`, `rh_folha_fechamentos`
- a geração automática existente agora usa o novo motor mensal
- a integração financeira continua opcional
