# Release notes - alinhamento runtime projeto + banco

## Projeto
- habilitado monitor global de slow request com persistência em `performance_events`
- compatibilidade de flags financeiras com aliases `FINANCE_BLOCK_DIRECT_MUTATIONS` e `FINANCE_BLOCK_DIRECT_SETTLEMENT_MUTATIONS`
- `infra.php` expandido para slow request monitoring
- `.env`, `.env.hostgator.example` e `.env.docker.example` alinhados para bloqueios + observabilidade
- `BANCO DE DADOS.sql` atualizado com dump alinhado ao runtime atual
- adicionados `database/sql/runtime_alignment_patch.sql` e `database/sql/wesl4494_db_saas_runtime_alinhado.sql`
- nova documentação operacional em `docs/operacao/README_BLOQUEIOS_PERFORMANCE_VPS.md`

## Banco
- incluídos `stock_write_audits`, `cache`, `cache_locks`, `performance_events`
- índices compostos críticos de financeiro e estoque adicionados via patch idempotente
- migrations correspondentes marcadas para manter `php artisan migrate --force` seguro
