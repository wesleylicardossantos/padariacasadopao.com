CREATE TABLE IF NOT EXISTS funcionarios_ficha_admissao (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  funcionario_id BIGINT UNSIGNED NOT NULL,
  empresa_id BIGINT UNSIGNED NULL,
  matricula VARCHAR(255) NULL,
  matricula_social VARCHAR(255) NULL,
  nome_pai VARCHAR(255) NULL,
  nome_mae VARCHAR(255) NULL,
  naturalidade VARCHAR(255) NULL,
  nacionalidade VARCHAR(255) NULL,
  uf_naturalidade VARCHAR(2) NULL,
  data_nascimento DATE NULL,
  deficiencia_fisica TINYINT(1) NOT NULL DEFAULT 0,
  raca_cor VARCHAR(255) NULL,
  sexo VARCHAR(255) NULL,
  estado_civil VARCHAR(255) NULL,
  grau_instrucao VARCHAR(255) NULL,
  ctps_numero VARCHAR(255) NULL,
  ctps_serie VARCHAR(255) NULL,
  ctps_uf VARCHAR(2) NULL,
  ctps_data_expedicao DATE NULL,
  pis_numero VARCHAR(255) NULL,
  pis_data_cadastro DATE NULL,
  rg_orgao_emissor VARCHAR(255) NULL,
  rg_data_emissao DATE NULL,
  titulo_eleitor VARCHAR(255) NULL,
  titulo_zona VARCHAR(255) NULL,
  titulo_secao VARCHAR(255) NULL,
  certificado_reservista VARCHAR(255) NULL,
  cnh_numero VARCHAR(255) NULL,
  cnh_categoria VARCHAR(255) NULL,
  cnh_validade DATE NULL,
  cnh_primeira_habilitacao DATE NULL,
  data_admissao DATE NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY funcionarios_ficha_admissao_funcionario_id_index (funcionario_id),
  KEY funcionarios_ficha_admissao_empresa_id_index (empresa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pdv_offline_syncs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  empresa_id BIGINT UNSIGNED NOT NULL,
  usuario_id BIGINT UNSIGNED NULL,
  uuid_local VARCHAR(120) NOT NULL,
  venda_caixa_id BIGINT UNSIGNED NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'pendente',
  payload_hash VARCHAR(64) NULL,
  request_payload JSON NULL,
  response_payload JSON NULL,
  erro TEXT NULL,
  tentativas INT UNSIGNED NOT NULL DEFAULT 0,
  ultima_tentativa_em TIMESTAMP NULL,
  sincronizado_em TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY pdv_offline_syncs_empresa_uuid_unique (empresa_id, uuid_local),
  KEY pdv_offline_syncs_venda_caixa_id_index (venda_caixa_id),
  KEY pdv_offline_syncs_status_index (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @has_status := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'status');
SET @has_estado := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'estado');
SET @sql := IF(@has_status = 0 AND @has_estado = 1, 'ALTER TABLE payments ADD COLUMN status VARCHAR(60) NULL AFTER empresa_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql := IF(@has_estado = 0 AND @has_status = 1, 'ALTER TABLE payments ADD COLUMN estado VARCHAR(60) NULL AFTER status', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
UPDATE payments SET status = estado WHERE status IS NULL AND estado IS NOT NULL;
UPDATE payments SET estado = status WHERE estado IS NULL AND status IS NOT NULL;
