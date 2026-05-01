# Release Notes — Dashboard RH Correção Preventiva

Data: 2026-04-07

## Ajustes aplicados
- validação preventiva de colunas antes de montar cards, KPIs e listas do dashboard
- fallback automático quando o banco não possui `cargo`
- fallback adicional quando `funcao`, `salario`, `ativo` ou `empresa_id` estiverem ausentes em bases legadas
- consultas de alertas adaptadas para só usar colunas realmente existentes
- seleção dinâmica de colunas em férias e ficha de admissão para evitar `Unknown column`
- ordenação segura da lista de maiores salários com fallback por nome/id

## Objetivo
Permitir que o dashboard funcione também em bancos legados ou parcialmente migrados, reduzindo quebras por divergência de schema.

## Recomendação após atualizar
- rodar `php artisan optimize:clear`
- rodar as migrations pendentes
- revisar se o banco local está alinhado com a estrutura esperada do módulo RH
