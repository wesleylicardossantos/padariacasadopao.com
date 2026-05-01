# Análise do Projeto Enterprise

## Visão geral

O projeto é um monólito Laravel 10 com forte acoplamento a regras de negócio ERP brasileiras. A base contém **13.666 arquivos** e mistura operações fiscais, comercial, financeiro, RH, PDV, delivery, e-commerce e SaaS em uma única aplicação.

## Pontos estruturais identificados

### Stack e arquitetura
- Framework: Laravel 10
- PHP: ^8.0.2
- Modelo principal: monólito modularizado parcialmente em `app/Modules`
- Padrão legado dominante: Controllers + Models com regras embutidas
- Início de modernização já existente: módulos `Financeiro`, `Comercial`, `BI`, `PDV`, `SaaS`

### Riscos principais
- Regras de negócio espalhadas em Models estáticos, Helpers e Controllers.
- Forte dependência de `session('user_logged')` como contexto de tenant.
- Grande volume de consultas ad hoc e duplicação de filtros.
- Risco alto de regressão caso tente reescrever tudo de uma vez.
- Banco com amplitude funcional alta, mas sem uma camada transacional enterprise consistente para os módulos críticos.

## Inventário funcional observado
- Fiscal: NFe, NFCe, CTe, MDF-e, boletos, remessas, contingência.
- Comercial: clientes, vendas, orçamentos, pedidos, comissões.
- Financeiro: contas a pagar/receber, DRE, categorias, contas bancárias.
- RH: folha, desligamento, férias, faltas, portal do funcionário.
- SaaS: planos, limites, ciclos, saúde do tenant, snapshots de uso.
- PDV e Mobile: bootstrap offline, governança, ponte com legado.
- E-commerce e delivery: produtos, banners, pedidos, clientes e endereços.

## Estratégia recomendada

### O que não fazer
- Não reescrever o monólito inteiro em um único passo.
- Não mover o banco inteiro para um novo schema sem uma camada anti-corrupção.

### O que fazer
- Consolidar o ERP em módulos de domínio com transações claras.
- Criar casos de uso para operações críticas.
- Tratar `empresa_id` como contexto obrigatório em todas as operações.
- Padronizar filtros, repositórios e consultas de fechamento.
- Isolar relatórios e dashboards em serviços read-model.

## O que entrou nesta Wave 2
- `Financeiro` ganhou DTOs transacionais, UseCases de lançamento e liquidação e fechamento mensal.
- `Comercial` ganhou análise de carteira, funil e saúde de clientes.
- Adição de rotas enterprise operacionais e de carteira.
- Adição de migration com índices para tabelas financeiras e comerciais mais consultadas.

## Próxima onda recomendada
1. Migrar `FinanceiroController` legado para delegar ao módulo enterprise.
2. Extrair casos de uso de `Vendas`, `Orçamentos` e `Clientes`.
3. Criar testes de regressão para liquidação, fechamento e carteira.
4. Padronizar eventos/auditoria por tenant.
