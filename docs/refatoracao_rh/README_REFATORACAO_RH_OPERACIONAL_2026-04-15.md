# Refatoração operacional do módulo RH — 2026-04-15

## Escopo implementado nesta entrega

Esta entrega aplica a primeira onda operacional segura da refatoração do RH, preservando compatibilidade com o monólito atual e sem exigir migration estrutural.

### Itens implementados
- DTOs e Actions para ciclo de vida do funcionário
- Queries oficiais para funcionários ativos, arquivo morto, timeline do dossiê, portal e competência de folha
- Enum de status do funcionário (`EmployeeStatus`)
- Endurecimento de tenancy no `RHContext` com suporte a `filial_id`
- Escopos adicionais no model `Funcionario`
- Refactor do `FuncionarioController` para usar Actions/DTOs
- Observador `FuncionarioObserver` para:
  - histórico automático
  - sincronização do dossiê
  - desativação do portal quando o funcionário é arquivado/inativado
- Endurecimento do middleware do portal do funcionário
- Ampliação do `RHPortalAcessoService` para ativação/desativação e relatórios efetivos

## Arquivos principais alterados
- `app/Models/Funcionario.php`
- `app/Modules/RH/Support/RHContext.php`
- `app/Modules/RH/Application/Funcionario/FuncionarioService.php`
- `app/Http/Controllers/FuncionarioController.php`
- `app/Http/Middleware/VerificaPortalFuncionario.php`
- `app/Services/RHPortalAcessoService.php`
- `app/Providers/AppServiceProvider.php`
- `app/Observers/FuncionarioObserver.php`

## Novos artefatos
- `app/Modules/RH/Application/DTOs/*`
- `app/Modules/RH/Application/Actions/*`
- `app/Modules/RH/Application/Queries/*`
- `app/Modules/RH/Support/Enums/EmployeeStatus.php`
- `tests/Unit/RH/*`

## O que ficou preparado para próximas ondas
- motor de folha mais desacoplado por calculators
- RBAC de RH com políticas unificadas por recurso
- timeline de dossiê integrada na UI administrativa
- dashboards RH com queries nomeadas e cache inteligente
- hardening final de tenant por empresa/filial em todos os subfluxos

## Banco de dados
Nenhuma migration estrutural nova foi necessária nesta onda.
