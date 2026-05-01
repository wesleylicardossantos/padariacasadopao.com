# System Healthcheck

- timestamp: 2026-04-23T11:32:56-03:00
- app_env: production
- app_debug: false
- app_key_present: true
- queue_connection: database
- database: false
- database_error: could not find driver (Connection: mysql, SQL: select 1)
- storage_writable: true
- bootstrap_cache_writable: true
## required_tables
- 0: pdv_offline_syncs
- 1: stock_movements
- 2: financial_audits
- 3: job_batches
- 4: fiscal_documents

## missing_tables

- migrations_table_exists: false
## storage_reports
- schema_drift_report: true
- healthcheck_report: false
- stock_reconcile_report: false

- schedule_list_ok: false
- schedule_list_error: could not find driver (Connection: mysql, SQL: update `cache_locks` set `owner` = jqXqNtrJfnyPfPJW, `expiration` = 1777041176 where `key` = w_l_dos_santos_cache_framework/schedule-8e2338b80312329746bd0adad34eaf87ef3a59e2 and (`owner` = jqXqNtrJfnyPfPJW or `expiration` <= 1776954776))
