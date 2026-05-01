-- Correção segura RH/Admin Audit + registro de migrations finais
-- Compatível com MySQL 8 / phpMyAdmin / Hostgator. Não apaga dados.

CREATE TABLE IF NOT EXISTS `rh_admin_action_audits` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint UNSIGNED DEFAULT NULL,
  `usuario_id` bigint UNSIGNED DEFAULT NULL,
  `modulo` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `acao` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alvo_tipo` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alvo_id` bigint UNSIGNED DEFAULT NULL,
  `referencia_tipo` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referencia_id` bigint UNSIGNED DEFAULT NULL,
  `payload_json` longtext COLLATE utf8mb4_unicode_ci,
  `ip` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rh_admin_action_audits_empresa_modulo_idx` (`empresa_id`,`modulo`),
  KEY `rh_admin_action_audits_usuario_created_idx` (`usuario_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `rh_admin_action_audits`
  ADD COLUMN IF NOT EXISTS `empresa_id` bigint UNSIGNED DEFAULT NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `usuario_id` bigint UNSIGNED DEFAULT NULL AFTER `empresa_id`,
  ADD COLUMN IF NOT EXISTS `modulo` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `usuario_id`,
  ADD COLUMN IF NOT EXISTS `acao` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `modulo`,
  ADD COLUMN IF NOT EXISTS `alvo_tipo` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `acao`,
  ADD COLUMN IF NOT EXISTS `alvo_id` bigint UNSIGNED DEFAULT NULL AFTER `alvo_tipo`,
  ADD COLUMN IF NOT EXISTS `referencia_tipo` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `alvo_id`,
  ADD COLUMN IF NOT EXISTS `referencia_id` bigint UNSIGNED DEFAULT NULL AFTER `referencia_tipo`,
  ADD COLUMN IF NOT EXISTS `payload_json` longtext COLLATE utf8mb4_unicode_ci AFTER `referencia_id`,
  ADD COLUMN IF NOT EXISTS `ip` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `payload_json`,
  ADD COLUMN IF NOT EXISTS `user_agent` text COLLATE utf8mb4_unicode_ci AFTER `ip`,
  ADD COLUMN IF NOT EXISTS `created_at` timestamp NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `updated_at` timestamp NULL DEFAULT NULL;

UPDATE `rh_admin_action_audits`
SET `referencia_tipo` = COALESCE(`referencia_tipo`, `alvo_tipo`),
    `referencia_id` = COALESCE(`referencia_id`, `alvo_id`)
WHERE `referencia_tipo` IS NULL OR `referencia_id` IS NULL;

INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES
('2026_04_25_233000_harden_rh_admin_action_audits_compatibility', 999);
