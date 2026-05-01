# Cutover seguro e baseline de performance

## Objetivo

Fechar a fase pós-hardening com dois artefatos operacionais:

- `php artisan legacy:cutoff-readiness-report --write`
- `php artisan performance:baseline-report --write`

## Uso recomendado

1. Rodar os relatórios em homologação.
2. Corrigir violações recentes de escrita direta no legado.
3. Confirmar ausência de scripts públicos sensíveis sem proteção.
4. Só então avaliar ativar `STOCK_BLOCK_DIRECT_LEGACY_WRITES=true`.

## Regras

- não ativar bloqueios sem relatório limpo
- não remover código candidato a morto sem janela de observação
- não aplicar cache de rotas se houver compatibilidade duvidosa
