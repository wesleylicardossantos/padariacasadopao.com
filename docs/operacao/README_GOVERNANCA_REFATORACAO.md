# Governança operacional da refatoração

Este pacote fecha a próxima etapa segura do plano enterprise sem quebrar contratos externos.

## Entregas desta etapa

- inventário técnico ampliado
- relatório de drift com foco operacional
- healthcheck com artefatos de operação
- comando de governança com checklist de deploy e smoke tests
- agendamento no Kernel para geração diária dos relatórios

## Comandos principais

```bash
php artisan project:inventory --write
php artisan schema:drift-report --write
php artisan system:healthcheck --write
php artisan refactor:governance-report --write
```

## Artefatos gerados

- `docs/arquitetura/inventario_projeto.json`
- `docs/arquitetura/inventario_projeto.md`
- `storage/app/schema_drift_report.json`
- `storage/app/schema_drift_report.md`
- `storage/app/healthcheck.json`
- `storage/app/healthcheck.md`
- `docs/operacao/governance_report.json`
- `docs/operacao/deploy_checklist.md`

## Regra desta etapa

Nada aqui deve mudar contratos HTTP, layout de banco legado ou comportamento funcional conhecido.
O objetivo é aumentar previsibilidade operacional antes de aprofundar Comercial e Fiscal.
