SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `mercadopago_webhook_events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `topic` VARCHAR(50) NULL,
  `resource_id` VARCHAR(100) NULL,
  `action` VARCHAR(100) NULL,
  `event_hash` VARCHAR(255) NOT NULL,
  `headers` LONGTEXT NULL,
  `payload` LONGTEXT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'received',
  `error_message` TEXT NULL,
  `processed_at` DATETIME NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_mp_webhook_event_hash` (`event_hash`),
  KEY `idx_mp_webhook_resource_id` (`resource_id`),
  KEY `idx_mp_webhook_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP PROCEDURE IF EXISTS `sp_add_column_if_not_exists`;
DELIMITER $$
CREATE PROCEDURE `sp_add_column_if_not_exists`(IN p_table VARCHAR(128), IN p_column VARCHAR(128), IN p_definition TEXT)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = p_table AND COLUMN_NAME = p_column
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', p_table, '` ADD COLUMN `', p_column, '` ', p_definition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS `sp_add_index_if_not_exists`;
DELIMITER $$
CREATE PROCEDURE `sp_add_index_if_not_exists`(IN p_table VARCHAR(128), IN p_index VARCHAR(128), IN p_index_sql TEXT)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = p_table AND INDEX_NAME = p_index
    ) THEN
        SET @sql = p_index_sql;
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

CALL `sp_add_column_if_not_exists`('payments', 'external_reference', 'VARCHAR(100) NULL AFTER `transacao_id`');
CALL `sp_add_column_if_not_exists`('payments', 'notification_url', 'VARCHAR(255) NULL AFTER `external_reference`');
CALL `sp_add_column_if_not_exists`('payments', 'raw_response', 'LONGTEXT NULL');
CALL `sp_add_column_if_not_exists`('payments', 'paid_at', 'DATETIME NULL');
CALL `sp_add_column_if_not_exists`('payments', 'mp_status_last_sync_at', 'DATETIME NULL');
CALL `sp_add_index_if_not_exists`('payments', 'idx_payments_external_reference', 'ALTER TABLE `payments` ADD INDEX `idx_payments_external_reference` (`external_reference`)');

CALL `sp_add_column_if_not_exists`('saas_subscription_cycles', 'mp_payment_id', 'VARCHAR(100) NULL');
CALL `sp_add_column_if_not_exists`('saas_subscription_cycles', 'payment_status', 'VARCHAR(50) NULL');
CALL `sp_add_column_if_not_exists`('saas_subscription_cycles', 'paid_at', 'DATETIME NULL');
CALL `sp_add_index_if_not_exists`('saas_subscription_cycles', 'idx_ssc_mp_payment_id', 'ALTER TABLE `saas_subscription_cycles` ADD INDEX `idx_ssc_mp_payment_id` (`mp_payment_id`)');

CALL `sp_add_column_if_not_exists`('plano_empresas', 'status_pagamento', 'VARCHAR(50) NULL');
CALL `sp_add_column_if_not_exists`('plano_empresas', 'data_pagamento', 'DATETIME NULL');

CALL `sp_add_column_if_not_exists`('conta_recebers', 'mp_payment_id', 'VARCHAR(100) NULL');
CALL `sp_add_column_if_not_exists`('conta_recebers', 'valor_pago', 'DECIMAL(16,7) NULL DEFAULT NULL');
CALL `sp_add_column_if_not_exists`('conta_recebers', 'data_pagamento', 'DATETIME NULL');
CALL `sp_add_column_if_not_exists`('conta_recebers', 'status_pagamento', 'VARCHAR(50) NULL');
CALL `sp_add_index_if_not_exists`('conta_recebers', 'idx_conta_recebers_mp_payment_id', 'ALTER TABLE `conta_recebers` ADD INDEX `idx_conta_recebers_mp_payment_id` (`mp_payment_id`)');
CALL `sp_add_index_if_not_exists`('conta_recebers', 'idx_conta_recebers_status_pagamento', 'ALTER TABLE `conta_recebers` ADD INDEX `idx_conta_recebers_status_pagamento` (`status_pagamento`)');

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` VARCHAR(255) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `attempts` TINYINT UNSIGNED NOT NULL,
  `reserved_at` INT UNSIGNED NULL,
  `available_at` INT UNSIGNED NOT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(255) NOT NULL,
  `connection` TEXT NOT NULL,
  `queue` TEXT NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `exception` LONGTEXT NOT NULL,
  `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP PROCEDURE IF EXISTS `sp_add_column_if_not_exists`;
DROP PROCEDURE IF EXISTS `sp_add_index_if_not_exists`;

SET FOREIGN_KEY_CHECKS = 1;
