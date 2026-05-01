# Fechamento do Financeiro — 2026-04-05

Implementações aplicadas no projeto:

- validação preventiva central em `app/Modules/Financeiro/Support/FinancialEntryValidator.php`
- hardening dos DTOs de lançamento e liquidação
- validação de pertencimento por empresa para cliente, fornecedor e categoria
- bloqueio de update/liquidação cruzada entre empresas
- remoção de exclusão direta em controllers; exclusão agora passa por use cases com auditoria
- inclusão de `DeleteReceivableUseCase` e `DeletePayableUseCase`
- auditoria financeira expandida para popular colunas legadas e novas (`acao/action`, `entidade/entity_type`, `antes/before_payload`, `depois/after_payload`, `motivo/reason`, `filial_id`)
- controllers financeiros passaram a validar update antes de persistir
- bridges financeiras passaram a usar `SettleFinancialEntryData::fromRequest()` e assert de ownership por empresa

Arquivos principais alterados:

- `app/Modules/Financeiro/Support/FinancialEntryValidator.php`
- `app/Modules/Financeiro/UseCases/DeleteReceivableUseCase.php`
- `app/Modules/Financeiro/UseCases/DeletePayableUseCase.php`
- `app/Modules/Financeiro/DTOs/RegisterFinancialEntryData.php`
- `app/Modules/Financeiro/DTOs/SettleFinancialEntryData.php`
- `app/Modules/Financeiro/UseCases/UpdateReceivableUseCase.php`
- `app/Modules/Financeiro/UseCases/UpdatePayableUseCase.php`
- `app/Modules/Financeiro/UseCases/SettleReceivableUseCase.php`
- `app/Modules/Financeiro/UseCases/SettlePayableUseCase.php`
- `app/Modules/Financeiro/Services/LegacyBridge/LegacyReceivableBridgeService.php`
- `app/Modules/Financeiro/Services/LegacyBridge/LegacyPayableBridgeService.php`
- `app/Modules/Financeiro/Services/FinancialAuditService.php`
- `app/Http/Controllers/ContaReceberController.php`
- `app/Http/Controllers/ContaPagarController.php`

Validação executada neste ambiente:

- `php -l` em todos os arquivos alterados

Limitações do pacote:

- não executei fluxo HTTP completo
- não rodei migrações nem testes integrados contra banco em runtime neste ambiente
- o fechamento aplicado é técnico e pronto para homologação
