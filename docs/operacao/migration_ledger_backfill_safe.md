# Backfill seguro do ledger de migrations

Este pacote insere na tabela `migrations` apenas entradas cuja estrutura correspondente já foi detectada no dump analisado.

## Migrations elegíveis

- `2026_01_02_create_fiscal_documents_table` — Tabela fiscal_documents já existe no dump.
- `2026_02_24_012930_create_funcionarios_dependentes_table` — Tabela funcionarios_dependentes já existe no dump.
- `2026_03_05_000001_add_deleted_at_to_venda_caixas_table` — Coluna venda_caixas.deleted_at já existe no dump.
- `2026_03_21_133500_create_pdv_offline_syncs_table` — Tabela pdv_offline_syncs já existe no dump.
- `2026_03_21_144500_fix_pdv_offline_syncs_missing_columns` — Colunas-base de pdv_offline_syncs já existem no dump.
- `2026_03_21_151500_add_retry_columns_to_pdv_offline_syncs_table` — Colunas de retry de pdv_offline_syncs já existem no dump.
- `2026_03_21_235000_create_saas_plan_features_table` — Tabela saas_plan_features já existe no dump.
- `2026_03_21_235100_create_saas_subscription_cycles_table` — Tabela saas_subscription_cycles já existe no dump.
- `2026_03_21_235200_create_saas_usage_snapshots_table` — Tabela saas_usage_snapshots já existe no dump.
- `2026_03_21_235300_create_saas_tenant_settings_table` — Tabela saas_tenant_settings já existe no dump.
- `2026_03_22_020000_create_ai_business_insights_table` — Tabela ai_business_insights já existe no dump.
- `2026_03_22_020100_create_saas_tenant_metrics_table` — Tabela saas_tenant_metrics já existe no dump.
- `2026_03_22_113000_add_branding_columns_to_empresas_table` — Colunas de branding em empresas já existem no dump.
- `2026_03_27_000001_create_jobs_table` — Tabela jobs já existe no dump.
- `2026_03_27_000002_create_rh_holerite_envio_lotes_table` — Tabela rh_holerite_envio_lotes já existe no dump.
- `2026_03_27_000003_create_rh_holerite_envios_table` — Tabela rh_holerite_envios já existe no dump.
- `2026_03_27_100000_create_rh_portal_funcionarios_table` — Tabela rh_portal_funcionarios já existe no dump.
- `2026_03_27_120000_add_mercadopago_enterprise_support` — Estruturas de Mercado Pago já existem no dump.
- `2026_03_27_120100_create_queue_tables_if_not_exists` — Tabelas jobs e failed_jobs já existem no dump.
- `2026_03_29_210000_create_stock_movements_table` — Tabela stock_movements já existe no dump.
- `2026_04_02_000001_add_filial_id_to_stock_movements_if_missing` — Coluna stock_movements.filial_id já existe no dump.
- `2026_04_02_010000_harden_pdv_offline_syncs_for_idempotency` — Coluna pdv_offline_syncs.payload_hash já existe no dump.
- `2026_04_03_100000_expand_pdv_offline_statuses` — Coluna response_payload já existe no dump, sugerindo hardening aplicado.
- `2026_04_03_100100_add_updated_at_to_financial_audits_if_missing` — Coluna financial_audits.updated_at já existe no dump.

## Arquivo SQL gerado
- `database/sql/2026_04_14_backfill_migrations_ledger_safe.sql`

## Observação
Este pacote **não** substitui as migrations ausentes de fato. Ele apenas regulariza o ledger para estruturas já presentes no banco.