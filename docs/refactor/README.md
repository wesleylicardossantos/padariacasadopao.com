# Refatoração segura — baseline executável

Este pacote **não substitui uma refatoração completa validada em produção**. Ele entrega a base para uma migração segura:

- registro canônico de módulos via `App\Modules\ModuleRegistry`
- inventário técnico via `php artisan refactor:inventory --write`
- testes arquiteturais mínimos para proteger a malha modular
- documentação operacional para planejar a migração por domínio

## Objetivo

Criar uma camada de governança antes de mover regras críticas do legado.

## Como usar

1. Rode `composer dump-autoload`
2. Rode `php artisan refactor:inventory --write`
3. Rode `php artisan route:list`
4. Rode `php artisan test --filter=Architecture`

## Próximas ondas

1. cercar fluxos críticos com testes de fumaça
2. classificar controllers em `legacy`, `bridge` e `module`
3. extrair regras financeiras e PDV para services transacionais
4. reduzir helpers globais com adapters
5. consolidar tenancy e billing


## Onda 2 — bridge financeiro legado

Esta entrega já move o miolo dos controllers legados `ContaReceberController` e `ContaPagarController` para a camada modular `App\Modules\Financeiro`, preservando views, rotas e flashes.

Novos pontos centrais:

- `App\Support\Tenancy\ResolveEmpresaId`
- `App\Modules\Financeiro\Data\FinanceFilterData`
- `App\Modules\Financeiro\Repositories\ReceivableRepository`
- `App\Modules\Financeiro\Repositories\PayableRepository`
- `App\Modules\Financeiro\Services\LegacyBridge\LegacyReceivableBridgeService`
- `App\Modules\Financeiro\Services\LegacyBridge\LegacyPayableBridgeService`

Resultado: a regra de listagem, criação, atualização e baixa financeira começa a sair do legado sem alterar contrato HTTP.
