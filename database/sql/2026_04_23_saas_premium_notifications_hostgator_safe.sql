CREATE TABLE IF NOT EXISTS `saas_premium_notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `empresa_id` BIGINT UNSIGNED NULL,
  `channel` VARCHAR(50) NULL,
  `level` VARCHAR(30) NULL,
  `title` VARCHAR(255) NULL,
  `message` TEXT NULL,
  `payload_json` LONGTEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_saas_premium_notifications_empresa_id` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
