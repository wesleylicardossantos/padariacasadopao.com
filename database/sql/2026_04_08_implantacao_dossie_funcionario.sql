CREATE TABLE IF NOT EXISTS `rh_dossies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `funcionario_id` bigint unsigned NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'ativo',
  `ultima_atualizacao_em` timestamp NULL DEFAULT NULL,
  `observacoes_internas` text NULL,
  `metadata_json` json NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_rh_dossies_empresa_funcionario` (`empresa_id`,`funcionario_id`),
  KEY `idx_rh_dossies_empresa_id` (`empresa_id`),
  KEY `idx_rh_dossies_funcionario_id` (`funcionario_id`),
  KEY `idx_rh_dossies_status` (`status`),
  KEY `idx_rh_dossies_ultima_atualizacao_em` (`ultima_atualizacao_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rh_dossie_eventos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `dossie_id` bigint unsigned NOT NULL,
  `funcionario_id` bigint unsigned NOT NULL,
  `categoria` varchar(40) NOT NULL,
  `titulo` varchar(120) NOT NULL,
  `descricao` text NULL,
  `origem` varchar(40) NOT NULL DEFAULT 'manual',
  `origem_id` bigint unsigned NULL,
  `data_evento` date NOT NULL,
  `visibilidade_portal` tinyint(1) NOT NULL DEFAULT 0,
  `payload_json` json NULL,
  `usuario_id` bigint unsigned NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_rh_dossie_eventos_empresa_id` (`empresa_id`),
  KEY `idx_rh_dossie_eventos_dossie_id` (`dossie_id`),
  KEY `idx_rh_dossie_eventos_funcionario_id` (`funcionario_id`),
  KEY `idx_rh_dossie_eventos_categoria` (`categoria`),
  KEY `idx_rh_dossie_eventos_data_evento` (`data_evento`),
  KEY `idx_rh_dossie_eventos_emp_func_cat` (`empresa_id`,`funcionario_id`,`categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `rh_documentos`
  ADD COLUMN IF NOT EXISTS `categoria` varchar(60) NULL AFTER `tipo`,
  ADD COLUMN IF NOT EXISTS `origem` varchar(40) NULL AFTER `observacao`,
  ADD COLUMN IF NOT EXISTS `metadata_json` json NULL AFTER `origem`,
  ADD COLUMN IF NOT EXISTS `usuario_id` bigint unsigned NULL AFTER `metadata_json`;
