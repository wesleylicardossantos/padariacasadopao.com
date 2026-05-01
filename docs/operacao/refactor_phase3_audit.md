# Auditoria da Fase 3

Gerado em: 2026-04-05 10:02:39

## Resumo
- Financeiro via use cases: OK
- Hotspots residuais de estoque: 7
- PDV offline blindado: OK

## Financeiro
```json
{
    "legacy_receivable_bridge_uses_register_usecase": true,
    "legacy_receivable_bridge_uses_update_usecase": true,
    "legacy_payable_bridge_uses_register_usecase": true,
    "legacy_payable_bridge_uses_update_usecase": true,
    "conta_receber_controller_uses_bridge": true,
    "conta_pagar_controller_uses_bridge": true
}
```

## Estoque
```json
{
    "product_controller_uses_stock_ledger": true,
    "remaining_direct_estoque_creates": 4,
    "remaining_direct_estoque_update_or_create": 3
}
```

## PDV offline
```json
{
    "sync_service_uses_lock_for_update": true,
    "sync_service_checks_payload_hash_conflict": true,
    "uuid_empresa_unique_migration_present": true
}
```
