CREATE TABLE IF NOT EXISTS `rh_admin_action_audits` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `empresa_id` BIGINT UNSIGNED NULL,
  `usuario_id` BIGINT UNSIGNED NULL,
  `acao` VARCHAR(120) NOT NULL,
  `modulo` VARCHAR(80) NOT NULL,
  `referencia_tipo` VARCHAR(120) NULL,
  `referencia_id` BIGINT UNSIGNED NULL,
  `payload_json` LONGTEXT NULL,
  `ip` VARCHAR(64) NULL,
  `user_agent` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_rh_admin_action_audits_empresa_modulo` (`empresa_id`,`modulo`),
  KEY `idx_rh_admin_action_audits_usuario_created` (`usuario_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
