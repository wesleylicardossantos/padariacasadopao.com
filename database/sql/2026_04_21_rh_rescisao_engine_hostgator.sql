CREATE TABLE IF NOT EXISTS rh_parametros_fiscais (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    competencia VARCHAR(7) NOT NULL UNIQUE,
    inss_faixas_json JSON NOT NULL,
    inss_teto DECIMAL(10,2) NOT NULL DEFAULT 0,
    irrf_faixas_json JSON NOT NULL,
    irrf_dependente DECIMAL(10,2) NOT NULL DEFAULT 0,
    irrf_desconto_simplificado DECIMAL(10,2) NOT NULL DEFAULT 0,
    fgts_percentual DECIMAL(5,2) NOT NULL DEFAULT 8.00,
    fgts_multa_percentual DECIMAL(5,2) NOT NULL DEFAULT 40.00,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS rh_rescisoes (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT NULL,
    funcionario_id BIGINT NOT NULL,
    desligamento_id BIGINT NULL,
    data_admissao DATE NULL,
    data_rescisao DATE NOT NULL,
    motivo VARCHAR(255) NULL,
    tipo_aviso VARCHAR(40) NULL,
    dependentes_irrf INT NOT NULL DEFAULT 0,
    descontos_extras DECIMAL(12,2) NOT NULL DEFAULT 0,
    saldo_salario DECIMAL(12,2) NOT NULL DEFAULT 0,
    ferias_vencidas DECIMAL(12,2) NOT NULL DEFAULT 0,
    ferias_proporcionais DECIMAL(12,2) NOT NULL DEFAULT 0,
    terco_ferias DECIMAL(12,2) NOT NULL DEFAULT 0,
    decimo_terceiro DECIMAL(12,2) NOT NULL DEFAULT 0,
    aviso_previo DECIMAL(12,2) NOT NULL DEFAULT 0,
    fgts_base DECIMAL(12,2) NOT NULL DEFAULT 0,
    fgts_deposito DECIMAL(12,2) NOT NULL DEFAULT 0,
    inss DECIMAL(12,2) NOT NULL DEFAULT 0,
    irrf DECIMAL(12,2) NOT NULL DEFAULT 0,
    fgts_multa DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_bruto DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_descontos DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_liquido DECIMAL(12,2) NOT NULL DEFAULT 0,
    observacoes TEXT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'processada',
    documentos_json JSON NULL,
    usuario_id BIGINT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_rh_rescisoes_empresa (empresa_id),
    INDEX idx_rh_rescisoes_funcionario (funcionario_id),
    INDEX idx_rh_rescisoes_data (data_rescisao)
);

CREATE TABLE IF NOT EXISTS rh_rescisao_itens (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    rescisao_id BIGINT NOT NULL,
    codigo VARCHAR(50) NOT NULL,
    descricao VARCHAR(150) NOT NULL,
    tipo ENUM('provento','desconto') NOT NULL,
    referencia DECIMAL(10,4) NULL,
    valor DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_rh_rescisao_itens_rescisao (rescisao_id)
);

ALTER TABLE rh_desligamentos ADD COLUMN IF NOT EXISTS rescisao_id BIGINT NULL AFTER usuario_id;

INSERT INTO rh_parametros_fiscais (
    competencia, inss_faixas_json, inss_teto, irrf_faixas_json,
    irrf_dependente, irrf_desconto_simplificado, fgts_percentual, fgts_multa_percentual, ativo, created_at, updated_at
)
SELECT '2026-01',
       JSON_ARRAY(
           JSON_OBJECT('ate', 1621.00, 'aliquota', 7.5),
           JSON_OBJECT('ate', 2902.84, 'aliquota', 9.0),
           JSON_OBJECT('ate', 4354.27, 'aliquota', 12.0),
           JSON_OBJECT('ate', 8475.55, 'aliquota', 14.0)
       ),
       8475.55,
       JSON_ARRAY(
           JSON_OBJECT('ate', 2428.80, 'aliquota', 0.0, 'deducao', 0.00),
           JSON_OBJECT('ate', 2826.65, 'aliquota', 7.5, 'deducao', 182.16),
           JSON_OBJECT('ate', 3751.05, 'aliquota', 15.0, 'deducao', 394.16),
           JSON_OBJECT('ate', 4664.68, 'aliquota', 22.5, 'deducao', 675.49),
           JSON_OBJECT('ate', 99999999.99, 'aliquota', 27.5, 'deducao', 908.73)
       ),
       189.59, 607.20, 8.00, 40.00, 1, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM rh_parametros_fiscais WHERE competencia = '2026-01');
