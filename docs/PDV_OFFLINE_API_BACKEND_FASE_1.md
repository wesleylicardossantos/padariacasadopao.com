# PDV Offline - Backend Laravel (Fase 1)

## O que esta fase entrega
- Login do PDV offline em `/api/pdv/login`
- Bootstrap inicial em `/api/pdv/bootstrap`
- Sincronização idempotente de vendas em `/api/pdv/vendas/sincronizar`
- Consulta de status de sincronização em `/api/pdv/sync/status`
- Tabela `pdv_offline_syncs` para rastrear UUID local e evitar duplicidade

## Como aplicar
1. Copie os arquivos do patch por cima do projeto.
2. Rode as migrations:
   - `php artisan migrate`
3. Limpe caches:
   - `php artisan optimize:clear`
   - `composer dump-autoload`

## Formato aceito em `/api/pdv/vendas/sincronizar`
```json
{
  "vendas": [
    {
      "uuid_local": "3f0d1ea4-c20f-49dd-8852-7c0f4d59f1ce",
      "criado_em": "2026-03-21 14:10:00",
      "cliente_id": 1,
      "cliente_nome": "Cliente Balcão",
      "pagamento_principal": "01",
      "total": 125.90,
      "desconto": 5,
      "acrescimo": 0,
      "troco": 4.10,
      "itens": [
        {"produto_id": 10, "quantidade": 2, "valor": 50},
        {"produto_id": 12, "quantidade": 1, "valor": 30.90}
      ],
      "pagamentos": [
        {"forma_pagamento": "01", "valor": 125.90}
      ]
    }
  ]
}
```

## Resposta de login
O endpoint mantém compatibilidade com o PDV legado e também devolve:
- `empresa`
- `operador`
- `terminal_token`

## Observações
- O token continua compatível com o middleware `authPdv`.
- A sincronização usa `uuid_local` por empresa para garantir idempotência.
- A baixa de estoque segue o mesmo helper do PDV já existente.
