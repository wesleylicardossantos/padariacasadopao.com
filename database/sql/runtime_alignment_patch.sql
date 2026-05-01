-- Runtime alignment patch for project hardening/performance/VPS readiness
-- Safe to run multiple times on MySQL/MariaDB.

CREATE TABLE IF NOT EXISTS `stock_write_audits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `filial_id` bigint unsigned DEFAULT NULL,
  `produto_id` bigint unsigned DEFAULT NULL,
  `event` varchar(50) NOT NULL,
  `legacy_stock_id` bigint unsigned DEFAULT NULL,
  `before_state` longtext DEFAULT NULL,
  `after_state` longtext DEFAULT NULL,
  `guard_source` varchar(100) DEFAULT NULL,
  `guard_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `performed_by` bigint unsigned DEFAULT NULL,
  `request_path` varchar(255) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_write_audits_empresa_id_index` (`empresa_id`),
  KEY `stock_write_audits_filial_id_index` (`filial_id`),
  KEY `stock_write_audits_produto_id_index` (`produto_id`),
  KEY `stock_write_audits_event_index` (`event`),
  KEY `stock_write_audits_legacy_stock_id_index` (`legacy_stock_id`),
  KEY `stock_write_audits_guard_source_index` (`guard_source`),
  KEY `stock_write_audits_guard_allowed_index` (`guard_allowed`),
  KEY `stock_write_audits_performed_by_index` (`performed_by`),
  KEY `stock_write_audits_empresa_created_idx` (`empresa_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `performance_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `event_type` varchar(50) NOT NULL,
  `context` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `performance_events_empresa_id_index` (`empresa_id`),
  KEY `performance_events_event_type_index` (`event_type`),
  KEY `performance_events_type_created_idx` (`event_type`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Critical composite indexes used by the project runtime.
SET @sql := IF(
  EXISTS(SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'conta_recebers' AND index_name = 'cr_empresa_filial_status_receb_idx'),
  'SELECT 1',
  'ALTER TABLE `conta_recebers` ADD INDEX `cr_empresa_filial_status_receb_idx` (`empresa_id`, `filial_id`, `status`, `data_recebimento`)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS(SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'conta_pagars' AND index_name = 'cp_empresa_filial_status_pag_idx'),
  'SELECT 1',
  'ALTER TABLE `conta_pagars` ADD INDEX `cp_empresa_filial_status_pag_idx` (`empresa_id`, `filial_id`, `status`, `data_pagamento`)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  EXISTS(SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'stock_movements' AND index_name = 'stock_movements_scope_occurred_idx'),
  'SELECT 1',
  'ALTER TABLE `stock_movements` ADD INDEX `stock_movements_scope_occurred_idx` (`empresa_id`, `filial_id`, `product_id`, `occurred_at`)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Keep migrations table aligned so `php artisan migrate --force` stays idempotent.
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_04_04_000101_create_stock_write_audits_table', COALESCE(MAX(batch), 1)
FROM `migrations`
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_04_04_000101_create_stock_write_audits_table');

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_04_04_090000_create_cache_tables_for_vps', COALESCE(MAX(batch), 1)
FROM `migrations`
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_04_04_090000_create_cache_tables_for_vps');

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_04_04_090100_create_performance_events_table', COALESCE(MAX(batch), 1)
FROM `migrations`
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_04_04_090100_create_performance_events_table');
