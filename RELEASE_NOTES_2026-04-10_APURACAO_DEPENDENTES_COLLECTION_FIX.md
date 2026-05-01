# Correção — Apuração automática / dependentes como Collection

## Causa raiz
O erro `Object of class Illuminate\\Database\\Eloquent\\Collection could not be converted to int` estava sendo disparado em:

- `app/Services/RHFolhaEngineService.php`

O banco registrava o erro na linha do cálculo de dependentes, onde o código tentava converter:

- `$funcionario->dependentes`

em inteiro. Como `dependentes` é uma relação Eloquent (`hasMany`), o retorno é uma `Collection`, não um número.

## Correção aplicada
- substituição da conversão direta por resolução segura da quantidade de dependentes
- suporte a coluna numérica `qtd_dependentes`, se existir
- suporte a coluna numérica `dependentes`, se existir e não for relação
- fallback para contagem da relação `dependentes()` quando necessário
- proteção extra para evitar nova falha em ambiente com esquema parcial

## Resultado
A geração automática da apuração deixa de quebrar ao calcular IRRF / dedução por dependentes.
