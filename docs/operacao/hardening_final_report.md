# Hardening final da refatoração

Gerado em: 2026-04-23T11:32:05-03:00

## Flags
- stock_monitor_direct_legacy_writes: true
- stock_block_direct_legacy_writes: true
- hardening_enforce_public_surface_review: true
- hardening_index_review_enabled: true

## Superfície pública
- Nenhum achado crítico mapeado.

## Security headers
- middleware_exists: true
- kernel_registered: true
- x_frame_options: SAMEORIGIN
- referrer_policy: strict-origin-when-cross-origin
- permissions_policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()
- csp_report_only_enabled: true
- hsts_enabled: false

## Cobertura de índices
### pdv_offline_syncs
- tabela ausente
### stock_movements
- tabela ausente
### financial_audits
- tabela ausente
### commercial_audits
- tabela ausente
### fiscal_documents
- tabela ausente
### fiscal_audits
- tabela ausente

## Candidatos a código morto/temporário
- app/Console/Commands_register_patch_tudo_nivel_maximo.php — Nome sugere código temporário/duplicado: _patch_
- app/Console/Kernel_patch_tudo_nivel_maximo.php — Nome sugere código temporário/duplicado: _patch_
- app/Modules/RH/Application/Financeiro/FolhaFinanceiroService.php.bak — Nome sugere código temporário/duplicado: .bak
- app/Services/RHDefaultPayrollEventService.php.bak — Nome sugere código temporário/duplicado: .bak
- app/Services/RHFolhaCompetenciaService.php.bak — Nome sugere código temporário/duplicado: .bak
- routes/ultimate_patch_routes.php — Nome sugere código temporário/duplicado: _patch_
- routes/web_patch_ia_aprendizado.php — Nome sugere código temporário/duplicado: _patch_
- routes/web_patch_ia_aprendizado_method.php — Nome sugere código temporário/duplicado: _patch_
- routes/web_patch_ia_aprovacao.php — Nome sugere código temporário/duplicado: _patch_
- routes/web_patch_ia_aprovacao_method.php — Nome sugere código temporário/duplicado: _patch_
- routes/web_patch_ia_autonoma.php — Nome sugere código temporário/duplicado: _patch_
- routes/web_patch_nivel_absurdo_maximo.php — Nome sugere código temporário/duplicado: _patch_
- routes/web_patch_nivel_maximo.php — Nome sugere código temporário/duplicado: _patch_
- routes/web_patch_preditivo_alertas.php — Nome sugere código temporário/duplicado: _patch_
- routes/web_patch_snippet.php — Nome sugere código temporário/duplicado: _patch_
- routes/web_patch_tudo_nivel_maximo.php — Nome sugere código temporário/duplicado: _patch_

## Artefatos esperados
- docs/operacao/governance_report.json: ok
- docs/operacao/schema_drift_report.json: missing
- docs/operacao/system_healthcheck.json: missing
- docs/operacao/stock_governance_report.json: missing
- docs/operacao/fiscal_operations_report.json: missing
