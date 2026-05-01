# Análise do Banco Enterprise

## Resumo

O dump mostra um banco de ERP horizontal com dezenas de áreas funcionais. A modelagem é ampla e viável para um ERP SaaS brasileiro, mas exige reforço em integridade, índices e fronteiras transacionais.

## Tabelas críticas para a Wave 2

### Financeiro
- `conta_recebers`
- `conta_pagars`
- `categoria_contas`
- `conta_bancarias`
- `dres`
- `boletos`

### Comercial
- `clientes`
- `vendas`
- `venda_caixas`
- `comissao_vendas`

### Tenant
- `empresas`
- `filials`
- `planos`
- `plano_empresas`

## Diagnóstico técnico

### Forças
- Quase todas as entidades têm `empresa_id`.
- Há cobertura para financeiro, comercial e fiscal real.
- Já existe no banco um volume de estrutura suficiente para suportar ERP multiempresa.

### Fragilidades
- Há padrões mistos de nomes e relacionamentos históricos.
- Muitas consultas críticas dependem de filtros por `empresa_id`, `status`, `filial_id` e data sem garantia explícita de índices dedicados.
- Parte da consistência ainda está no código, não no banco.

## Refatoração recomendada

### Índices adicionados nesta entrega
- `conta_recebers (empresa_id, status, data_vencimento)`
- `conta_recebers (empresa_id, cliente_id)`
- `conta_recebers (empresa_id, filial_id)`
- `conta_pagars (empresa_id, status, data_vencimento)`
- `conta_pagars (empresa_id, fornecedor_id)`
- `conta_pagars (empresa_id, filial_id)`
- `vendas (empresa_id, cliente_id, created_at)`
- `vendas (empresa_id, filial_id, created_at)`
- `clientes (empresa_id, inativo)`

### Próximos reforços
- Revisar foreign keys ausentes nas tabelas financeiras.
- Criar trilha de auditoria por operação crítica.
- Padronizar campos monetários com precisão consistente.
- Criar snapshots de fechamento mensal imutáveis.

## Leitura funcional do banco
- `conta_recebers` é o coração do contas a receber e já permite operação manual e derivada de venda.
- `conta_pagars` cobre contas de compra e lançamentos independentes.
- `vendas` e `clientes` sustentam um CRM operacional orientado a faturamento, recompra e inadimplência.
