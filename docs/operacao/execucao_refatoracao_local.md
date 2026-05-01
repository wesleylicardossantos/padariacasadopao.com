# Execução local da etapa final de refatoração

## O que foi aplicado no código

- TenantContext ampliado para expor `userLogged()` e resolver `empresa_id`, `filial_id` e `user_id` também via container da aplicação e atributos do request.
- Middleware `ResolveTenantContext` endurecido para propagar `empresa_id`, `filial_id` e `user_id` de forma consistente no request, sessão e container.
- `app/Modules/Estoque/Controllers/GovernanceController.php` ajustado para consumir `ResolveEmpresaId` em vez de depender de `session('user_logged')`.
- `app/Modules/PDV/Controllers/GovernanceController.php` ajustado para consumir `ResolveEmpresaId` em vez de depender de `session('user_logged')`.
- Bridges legadas do Financeiro (`LegacyReceivableBridgeService` e `LegacyPayableBridgeService`) ajustadas para usar `RuntimeConfig::pagination()` no lugar de `env('PAGINACAO')`.
- Inventário técnico regenerado em `docs/arquitetura`.
- Relatório local de drift entre código e dump gerado em `docs/operacao/schema_drift_dump_report.{md,json}`.

## Evidências geradas

- `docs/arquitetura/inventario_projeto.md`
- `docs/arquitetura/inventario_projeto.json`
- `docs/operacao/schema_drift_dump_report.md`
- `docs/operacao/schema_drift_dump_report.json`

## Limites desta execução local

- Não foi possível executar reconciliação real de estoque, healthcheck com banco ativo, schedule:list operacional e validação de migrations aplicadas em produção, porque o ambiente entregue no container não possui driver MySQL disponível nem acesso ao banco de produção/HostGator.
- O dump SQL foi usado para validar drift entre artefatos, mas isso não substitui a conferência final diretamente na produção.
- A suíte PHPUnit não pôde ser executada neste container porque faltam extensões PHP obrigatórias (`dom`, `mbstring`, `xml`, `xmlwriter`).

## Próximo passo obrigatório em produção

1. Subir este pacote em homologação idêntica à hospedagem atual.
2. Rodar `project:inventory --write`.
3. Rodar `schema:drift-report --write` com o banco real.
4. Rodar `system:healthcheck --write` com cache/queue configurados para o ambiente.
5. Rodar `stock:reconcile EMPRESA_ID --write` por empresa crítica.
6. Validar fluxos de financeiro, estoque, PDV offline, comercial mínimo e fiscal mínimo antes do deploy final.
