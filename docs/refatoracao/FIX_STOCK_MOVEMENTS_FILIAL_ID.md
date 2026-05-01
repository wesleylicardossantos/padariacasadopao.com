# Fix de compatibilidade - stock_movements.filial_id

## Problema
Em alguns bancos legados, a tabela `stock_movements` já existia antes da migration que passou a prever a coluna `filial_id`.
Por isso, a criação original foi ignorada e o código novo passou a consultar uma coluna que ainda não existia na produção.

## Sintoma
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'filial_id' in 'where clause'

## Correção aplicada
Foi adicionada uma migration de compatibilidade:
- adiciona `filial_id` se a coluna não existir
- cria índice simples em `filial_id`
- cria índice composto `stock_movements_scope_idx` se ele ainda não existir

## Execução
```bash
php artisan migrate --force
php artisan schema:drift-report --write
```
