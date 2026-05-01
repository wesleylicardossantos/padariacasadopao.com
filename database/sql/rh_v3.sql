\
CREATE TABLE IF NOT EXISTS rh_ferias (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    empresa_id BIGINT UNSIGNED NOT NULL,
    funcionario_id BIGINT UNSIGNED NOT NULL,
    periodo_aquisitivo_inicio DATE NOT NULL,
    periodo_aquisitivo_fim DATE NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    dias INT NOT NULL DEFAULT 30,
    status VARCHAR(30) NOT NULL DEFAULT 'programada',
    observacao VARCHAR(255) NULL,
    usuario_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_rh_ferias_emp (empresa_id),
    KEY idx_rh_ferias_func (funcionario_id),
    KEY idx_rh_ferias_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
