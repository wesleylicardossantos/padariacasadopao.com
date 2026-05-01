CREATE TABLE IF NOT EXISTS rh_folha_fechamentos (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    empresa_id BIGINT UNSIGNED NOT NULL,
    mes INT NOT NULL,
    ano INT NOT NULL,
    salario_base_total DECIMAL(16,2) NOT NULL DEFAULT 0,
    eventos_total DECIMAL(16,2) NOT NULL DEFAULT 0,
    descontos_total DECIMAL(16,2) NOT NULL DEFAULT 0,
    liquido_total DECIMAL(16,2) NOT NULL DEFAULT 0,
    conta_pagar_id BIGINT UNSIGNED NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'fechado',
    observacao VARCHAR(255) NULL,
    usuario_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_rh_folha_emp (empresa_id),
    KEY idx_rh_folha_comp (mes, ano)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
