CREATE TABLE IF NOT EXISTS `controle_salgados` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `empresa_id` BIGINT UNSIGNED NULL,
  `data` DATE NOT NULL,
  `dia` VARCHAR(60) NULL,
  `observacoes` TEXT NULL,
  `created_by` BIGINT UNSIGNED NULL,
  `updated_by` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `controle_salgados_empresa_id_index` (`empresa_id`),
  KEY `controle_salgados_data_index` (`data`),
  KEY `idx_controle_salgados_empresa_data` (`empresa_id`, `data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `controle_salgado_itens` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `controle_salgado_id` BIGINT UNSIGNED NOT NULL,
  `periodo` ENUM('manha', 'tarde') NOT NULL,
  `ordem` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `descricao` VARCHAR(255) NOT NULL,
  `qtd` INT NULL,
  `termino` VARCHAR(30) NULL,
  `saldo` INT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `controle_salgado_itens_periodo_index` (`periodo`),
  KEY `idx_controle_salgado_itens_lookup` (`controle_salgado_id`, `periodo`, `ordem`),
  CONSTRAINT `fk_controle_salgado_itens_controle`
    FOREIGN KEY (`controle_salgado_id`) REFERENCES `controle_salgados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
