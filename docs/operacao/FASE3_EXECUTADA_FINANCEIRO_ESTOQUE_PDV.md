# Fase 3 executada - financeiro, estoque e PDV offline

## Escopo aplicado neste pacote

### Financeiro
- `LegacyReceivableBridgeService` agora registra contas novas via `RegisterReceivableUseCase`.
- `LegacyPayableBridgeService` agora registra contas novas via `RegisterPayableUseCase`.
- Foram adicionados `UpdateReceivableUseCase` e `UpdatePayableUseCase` para tirar a atualização principal do `fill()->save()` direto do bridge legado.
- Contas criadas já marcadas como pagas/recebidas passam pelo fluxo de liquidação (`Settle*UseCase`) para manter auditoria e limpeza de cache.
- `RegisterFinancialEntryData` passou a converter valor usando `__convert_value_bd()` quando disponível.

### Estoque
- `ProductController` deixou de gravar estoque inicial diretamente em `estoques` e passou a registrar entrada pelo `StockLedgerService`.
- O saldo legado continua sendo projetado pelo próprio ledger, preservando compatibilidade com telas antigas.

### PDV offline
- Mantida a blindagem já existente em `OfflineSaleSyncService`:
  - `lockForUpdate()`
  - conflito por hash de payload
  - unicidade operacional por `empresa_id + uuid_local` via migration existente
- Foi adicionada auditoria operacional da fase por comando Artisan.

## Comando novo

```bash
php artisan refactor:phase3-audit --write
```

Gera:
- `docs/operacao/refactor_phase3_audit.json`
- `docs/operacao/refactor_phase3_audit.md`

## Observação importante

Este pacote **fecha uma parte real da fase 3 no código**, mas **não substitui validação em homologação/produção**. Ainda é obrigatório validar:
- migrations aplicadas na base real
- reconcile de estoque por empresa
- smoke tests de contas a pagar/receber
- cenários reais de PDV offline
- deploy/rollback do ambiente atual
