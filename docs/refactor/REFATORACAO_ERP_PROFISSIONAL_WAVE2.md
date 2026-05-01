# Refatoração ERP Profissional - Wave 2

## Objetivo
Transformar os módulos críticos em uma base enterprise utilizável sem reescrever o monólito inteiro.

## Escopo entregue

### Financeiro
- DTO `RegisterFinancialEntryData`
- DTO `SettleFinancialEntryData`
- UseCases:
  - `RegisterReceivableUseCase`
  - `RegisterPayableUseCase`
  - `SettleReceivableUseCase`
  - `SettlePayableUseCase`
- Serviço de fechamento:
  - `FinancialClosingService`
- Controller operacional:
  - `OperationsController`
- Novas rotas:
  - `GET /enterprise/financeiro/operations`
  - `POST /enterprise/financeiro/receivables`
  - `POST /enterprise/financeiro/payables`
  - `PATCH /enterprise/financeiro/receivables/{id}/settle`
  - `PATCH /enterprise/financeiro/payables/{id}/settle`

### Comercial
- DTO `CustomerLifecycleFilterData`
- Repositories:
  - `CustomerRepository`
  - `SalesOrderRepository`
- Serviço:
  - `CustomerLifecycleService`
- Controller:
  - `PortfolioController`
- Novas rotas:
  - `GET /enterprise/comercial/portfolio`
  - `GET /enterprise/comercial/snapshot`

## Resultado arquitetural
- O sistema agora tem uma camada transacional explícita para financeiro.
- O comercial ganhou um read-model de carteira e recorrência.
- A refatoração preserva o legado, mas desloca a inteligência nova para módulos ERP profissionais.

## Como usar
1. Rodar migrations.
2. Validar as rotas enterprise.
3. Conectar telas legadas aos novos casos de uso.
4. Criar testes de regressão antes de migrar controllers antigos.

## Limites desta entrega
- Não substitui automaticamente todas as telas antigas.
- Não reescreve fiscal, RH e PDV nessa mesma onda.
- Não força foreign keys em um banco legado sem análise de dados órfãos.
