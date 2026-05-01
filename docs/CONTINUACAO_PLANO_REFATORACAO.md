# Continuação do plano de refatoração

Este patch continua as fases recomendadas no PDF original, com foco em:

1. **Governança**
   - corrige `RouteFileRegistry::priorityDirectories()`
   - agenda inventário e relatório de drift no `Kernel`

2. **Tenancy**
   - cria `TenantContext` para centralizar empresa, filial e usuário
   - simplifica `ResolveEmpresaId`

3. **PDV offline**
   - adiciona estados `conflito_payload`, `erro_recuperavel` e `erro_fatal`
   - impede reentrada quando o sync já está em processamento
   - melhora observabilidade e classificação de erro

4. **Financeiro**
   - adiciona auditoria em `RegisterReceivableUseCase` e `SettleReceivableUseCase`

5. **Estoque**
   - adiciona `stock:reconcile`

6. **Comercial/Fiscal**
   - substitui placeholders por serviços mínimos reais, sem quebrar contrato externo

7. **Testes mínimos**
   - adiciona testes simples para tenancy, inventário e status de sync

## Ordem de aplicação

1. aplicar o patch sobre o projeto atual
2. rodar `php artisan migrate --force`
3. rodar `php artisan project:inventory --write`
4. rodar `php artisan schema:drift-report --write`
5. rodar `php artisan stock:reconcile {empresa_id}` em homologação
