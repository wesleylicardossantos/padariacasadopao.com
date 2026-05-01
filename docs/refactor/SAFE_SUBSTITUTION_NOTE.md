# Nota de substituição segura

Este pacote foi preparado com mudanças **estruturais de baixo risco**. Ele é adequado como ponto de partida para continuação da refatoração, mas **não deve ser tratado como uma substituição final validada do ERP inteiro** sem:

- instalar dependências
- rodar migrations em ambiente de homologação
- executar testes
- validar fluxos fiscais, financeiros, PDV e RH
- comparar resultados com a base atual

## Mudanças incluídas

- centralização do catálogo de módulos
- geração automatizada de inventário técnico
- testes arquiteturais básicos
- documentação operacional da refatoração

## Mudanças deliberadamente não incluídas

- alteração de regras de negócio financeiras
- alteração de emissão fiscal
- alteração de sincronização PDV
- mudança estrutural de banco de dados
- substituição massiva de controllers legados

Esses blocos exigem validação funcional e execução assistida em ambiente real.
