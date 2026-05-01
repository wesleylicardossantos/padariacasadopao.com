# Correção — folha ignorando eventos inativos no valor final

## Ajustes aplicados
- Blindagem no motor da folha para considerar somente eventos ativos em `evento_salarios` e vínculos ativos em `funcionario_eventos`.
- Filtro duplo no carregamento dos eventos do funcionário para impedir desconto/provento de evento desativado.
- Normalização de status ativo para aceitar `1/S/SIM/A/ATIVO` e tratar nulos como ativos legados.
- Cascata no cadastro de eventos: ao desativar um evento salarial, os vínculos em `funcionario_eventos` passam automaticamente para inativos.

## Resultado esperado
- O campo `valor_final` só será calculado com eventos ativos.
- Eventos desativados não entram mais em desconto nem provento em novas apurações.

## Reprocessamento recomendado
Para refazer a competência já gerada, execute com sobrescrita:

```bash
php artisan rh:folha-processar 1 4 2026 --sobrescrever
```
