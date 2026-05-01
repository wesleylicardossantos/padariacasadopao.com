CREATE TABLE IF NOT EXISTS rh_faltas (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    empresa_id BIGINT UNSIGNED NOT NULL,
    funcionario_id BIGINT UNSIGNED NOT NULL,
    tipo VARCHAR(30) NOT NULL,
    data_referencia DATE NOT NULL,
    quantidade_horas VARCHAR(20) NULL,
    descricao VARCHAR(255) NULL,
    usuario_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_rh_faltas_emp (empresa_id),
    KEY idx_rh_faltas_func (funcionario_id),
    KEY idx_rh_faltas_tipo (tipo),
    KEY idx_rh_faltas_data (data_referencia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rh_desligamentos (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    empresa_id BIGINT UNSIGNED NOT NULL,
    funcionario_id BIGINT UNSIGNED NOT NULL,
    data_desligamento DATE NOT NULL,
    motivo VARCHAR(255) NOT NULL,
    tipo VARCHAR(100) NOT NULL,
    observacao VARCHAR(255) NULL,
    usuario_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_rh_desl_emp (empresa_id),
    KEY idx_rh_desl_func (funcionario_id),
    KEY idx_rh_desl_data (data_desligamento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
