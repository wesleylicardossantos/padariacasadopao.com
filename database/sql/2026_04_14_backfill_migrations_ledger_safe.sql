-- Backfill seguro do ledger de migrations com base em evidência estrutural do dump
-- Gerado automaticamente para reduzir drift entre código e banco sem reexecutar DDL já presente.
START TRANSACTION;

-- 2026_01_02_create_fiscal_documents_table
-- Evidência: Tabela fiscal_documents já existe no dump.
INSERT INTO migrations (migration, batch)
SELECT '2026_01_02_create_fiscal_documents_table', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_01_02_create_fiscal_documents_table');

-- 2026_02_24_012930_create_funcionarios_dependentes_table
-- Evidência: Tabela funcionarios_dependentes já existe no dump.
INSERT INTO migrations (migration, batch)
SELECT '2026_02_24_012930_create_funcionarios_dependentes_table', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_02_24_012930_create_funcionarios_dependentes_table');

-- 2026_03_05_000001_add_deleted_at_to_venda_caixas_table
-- Evidência: Coluna venda_caixas.deleted_at já existe no dump.
INSERT INTO migrations (migration, batch)
SELECT '2026_03_05_000001_add_deleted_at_to_venda_caixas_table', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_03_05_000001_add_deleted_at_to_venda_caixas_table');

-- 2026_03_21_133500_create_pdv_offline_syncs_table
-- Evidência: Tabela pdv_offline_syncs já existe no dump.
INSERT INTO migrations (migration, batch)
SELECT '2026_03_21_133500_create_pdv_offline_syncs_table', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_03_21_133500_create_pdv_offline_syncs_table');

-- 2026_03_21_144500_fix_pdv_offline_syncs_missing_columns
-- Evidência: Colunas-base de pdv_offline_syncs já existem no dump.
INSERT INTO migrations (migration, batch)
SELECT '2026_03_21_144500_fix_pdv_offline_syncs_missing_columns', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_03_21_144500_fix_pdv_offline_syncs_missing_columns');

-- 2026_03_21_151500_add_retry_columns_to_pdv_offline_syncs_table
-- Evidência: Colunas de retry de pdv_offline_syncs já existem no dump.
INSERT INTO migrations (migration, batch)
SELECT '2026_03_21_151500_add_retry_columns_to_pdv_offline_syncs_table', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_03_21_151500_add_retry_columns_to_pdv_offline_syncs_table');

-- 2026_03_21_235000_create_saas_plan_features_table
-- Evidência: Tabela saas_plan_features já existe no dump.
INSERT INTO migrations (migration, batch)
SELECT '2026_03_21_235000_create_saas_plan_features_table', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_03_21_235000_create_saas_plan_features_table');

-- 2026_03_21_235100_create_saas_subscription_cycles_table
-- Evidência: Tabela saas_subscription_cycles já existe no dump.
INSERT INTO migrations (migration, batch)
SELECT '2026_03_21_235100_create_saas_subscription_cycles_table', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_03_21_235100_create_saas_subscription_cycles_table');

-- 2026_03_21_235200_create_saas_usage_snapshots_table
-- Evidência: Tabela saas_usage_snapshots já existe no dump.
INSERT INTO migrations (migration, batch)
SELECT '2026_03_21_235200_create_saas_usage_snapshots_table', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_03_21_235200_create_saas_usage_snapshots_table');

-- 2026_03_21_235300_create_saas_tenant_settings_table
-- Evidência: Tabela saas_tenant_settings já existe no dump.
INSERT INTO migrations (migration, batch)
SELECT '2026_03_21_235300_create_saas_tenant_settings_table', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_03_21_235300_create_saas_tenant_settings_table');

-- 2026_03_22_020000_create_ai_business_insights_table
-- Evidência: Tabela ai_business_insights já existe no dump.
INSERT INTO migrations (migration, batch)
SELECT '2026_03_22_020000_create_ai_business_insights_table', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_03_22_020000_create_ai_business_insights_table');

-- 2026_03_22_020100_create_saas_tenant_metrics_table
-- Evidência: Tabela saas_tenant_metrics já existe no dump.
INSERT INTO migrations (migration, batch)
SELECT '2026_03_22_020100_create_saas_tenant_metrics_table', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_03_22_020100_create_saas_tenant_metrics_table');

-- 2026_03_22_113000_add_branding_columns_to_empresas_table
-- Evidência: Colunas de branding em empresas já existem no dump.
INSERT INTO migrations (migration, batch)
SELECT '2026_03_22_113000_add_branding_columns_to_empresas_table', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_03_22_113000_add_branding_columns_to_empresas_table');

-- 2026_03_27_000001_create_jobs_table
-- Evidência: Tabela jobs já existe no dump.
INSERT INTO migrations (migration, batch)
SELECT '2026_03_27_000001_create_jobs_table', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_03_27_000001_create_jobs_table');

-- 2026_03_27_000002_create_rh_holerite_envio_lotes_table
-- Evidência: Tabela rh_holerite_envio_lotes já existe no dump.
INSERT INTO migrations (migration, batch)
SELECT '2026_03_27_000002_create_rh_holerite_envio_lotes_table', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_03_27_000002_create_rh_holerite_envio_lotes_table');

-- 2026_03_27_000003_create_rh_holerite_envios_table
-- Evidência: Tabela rh_holerite_envios já existe no dump.
INSERT INTO migrations (migration, batch)
SELECT '2026_03_27_000003_create_rh_holerite_envios_table', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_03_27_000003_create_rh_holerite_envios_table');

-- 2026_03_27_100000_create_rh_portal_funcionarios_table
-- Evidência: Tabela rh_portal_funcionarios já existe no dump.
INSERT INTO migrations (migration, batch)
SELECT '2026_03_27_100000_create_rh_portal_funcionarios_table', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_03_27_100000_create_rh_portal_funcionarios_table');

-- 2026_03_27_120000_add_mercadopago_enterprise_support
-- Evidência: Estruturas de Mercado Pago já existem no dump.
INSERT INTO migrations (migration, batch)
SELECT '2026_03_27_120000_add_mercadopago_enterprise_support', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_03_27_120000_add_mercadopago_enterprise_support');

-- 2026_03_27_120100_create_queue_tables_if_not_exists
-- Evidência: Tabelas jobs e failed_jobs já existem no dump.
INSERT INTO migrations (migration, batch)
SELECT '2026_03_27_120100_create_queue_tables_if_not_exists', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_03_27_120100_create_queue_tables_if_not_exists');

-- 2026_03_29_210000_create_stock_movements_table
-- Evidência: Tabela stock_movements já existe no dump.
INSERT INTO migrations (migration, batch)
SELECT '2026_03_29_210000_create_stock_movements_table', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_03_29_210000_create_stock_movements_table');

-- 2026_04_02_000001_add_filial_id_to_stock_movements_if_missing
-- Evidência: Coluna stock_movements.filial_id já existe no dump.
INSERT INTO migrations (migration, batch)
SELECT '2026_04_02_000001_add_filial_id_to_stock_movements_if_missing', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_02_000001_add_filial_id_to_stock_movements_if_missing');

-- 2026_04_02_010000_harden_pdv_offline_syncs_for_idempotency
-- Evidência: Coluna pdv_offline_syncs.payload_hash já existe no dump.
INSERT INTO migrations (migration, batch)
SELECT '2026_04_02_010000_harden_pdv_offline_syncs_for_idempotency', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_02_010000_harden_pdv_offline_syncs_for_idempotency');

-- 2026_04_03_100000_expand_pdv_offline_statuses
-- Evidência: Coluna response_payload já existe no dump, sugerindo hardening aplicado.
INSERT INTO migrations (migration, batch)
SELECT '2026_04_03_100000_expand_pdv_offline_statuses', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_03_100000_expand_pdv_offline_statuses');

-- 2026_04_03_100100_add_updated_at_to_financial_audits_if_missing
-- Evidência: Coluna financial_audits.updated_at já existe no dump.
INSERT INTO migrations (migration, batch)
SELECT '2026_04_03_100100_add_updated_at_to_financial_audits_if_missing', COALESCE((SELECT MAX(batch) + 1 FROM migrations m2), 1)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_03_100100_add_updated_at_to_financial_audits_if_missing');

COMMIT;
