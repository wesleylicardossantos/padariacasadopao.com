CREATE TABLE IF NOT EXISTS `rh_acl_permissoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(120) NOT NULL,
  `nome` varchar(120) NOT NULL,
  `modulo` varchar(40) NOT NULL DEFAULT 'rh',
  `descricao` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rh_acl_permissoes_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rh_acl_papeis` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `nome` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rh_acl_papeis_slug_unique` (`slug`),
  KEY `idx_rh_acl_papeis_empresa_ativo` (`empresa_id`,`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rh_acl_papel_permissoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `papel_id` bigint unsigned NOT NULL,
  `permissao_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_rh_acl_papel_permissoes` (`papel_id`,`permissao_id`),
  KEY `idx_rh_acl_papel_permissoes_perm` (`permissao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rh_acl_papel_usuarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `papel_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned NOT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_rh_acl_papel_usuarios` (`papel_id`,`usuario_id`),
  KEY `idx_rh_acl_papel_usuarios_user_emp` (`usuario_id`,`empresa_id`,`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `rh_dossie_eventos`
  ADD COLUMN `source_uid` varchar(180) NULL AFTER `origem`;
