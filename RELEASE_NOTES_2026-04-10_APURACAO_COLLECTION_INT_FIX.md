# Correção - Apuração automática / Collection -> int

## Ajustes
- blindagem no motor de competência para normalizar `evento_id` antes de gravar em `apuracao_salario_eventos` e `rh_folha_itens`
- blindagem na integração financeira para normalizar `filial_id`
- refino no serviço de eventos padrão para reaproveitar corretamente o evento existente e evitar inconsistências ao montar o mapa dos eventos base

## Objetivo
Evitar falhas do tipo:
- `Object of class Illuminate\\Database\\Eloquent\\Collection could not be converted to int`
- erros decorrentes de valores relacionais vindos como coleção em vez de ID escalar
