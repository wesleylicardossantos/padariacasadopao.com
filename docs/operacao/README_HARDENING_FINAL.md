# Hardening final da refatoração

Esta etapa consolida o fechamento seguro do plano enterprise sem quebrar contratos externos.

## Entregáveis
- `php artisan hardening:final-report --write`
- `php artisan deadcode:candidates-report --write`
- migration de índices não destrutivos para tabelas críticas
- agendamento no `Kernel`

## Objetivo
- revisar superfície pública exposta
- medir cobertura básica de índices em tabelas críticas
- identificar candidatos a código temporário/duplicado para corte gradual
- garantir que os artefatos operacionais da refatoração estão sendo gerados

## Regras
- não remover código morto automaticamente em produção
- não bloquear endpoints sem evidência operacional
- usar os relatórios para decidir o corte gradual
