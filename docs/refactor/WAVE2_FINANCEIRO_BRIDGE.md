# Onda 2 — Bridge financeiro legado

## Escopo aplicado

Refatoração segura dos fluxos legados:

- `ContaReceberController`
- `ContaPagarController`

Sem mudar:

- nomes de rotas
- views Blade
- redirects
- mensagens flash
- contratos principais de request/response

## O que mudou

### 1. Controllers viraram orquestradores

Os controllers legados agora delegam para services do módulo `Financeiro`.

### 2. Filtros financeiros foram centralizados

A leitura de filtros (`start_date`, `end_date`, `type_search`, `status`, `filial_id`, fornecedor/cliente) foi movida para `FinanceFilterData`.

### 3. Resolução de empresa foi padronizada

`ResolveEmpresaId` concentra a leitura de `empresa_id` para reduzir divergência entre request, sessão e atributos.

### 4. Query de listagem saiu do controller

As consultas de listagem paginada agora ficam em repositories:

- `ReceivableRepository`
- `PayableRepository`

### 5. Escrita foi encapsulada

Criação, atualização e baixa financeira agora passam por bridge services.

## Benefícios imediatos

- menor risco em futuras mudanças no Financeiro
- controllers mais curtos
- lógica duplicada mais visível
- melhor base para testes e futura migração para actions/DTOs transacionais

## Próxima onda recomendada

1. extrair validações para Form Requests
2. adicionar testes de integração com SQLite
3. aplicar o mesmo padrão em `VendaController`, `PedidoController` e fluxos de caixa/PDV
4. consolidar auditoria financeira nas baixas e exclusões
