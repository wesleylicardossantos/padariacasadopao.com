-- Patch do motor de folha RH
-- Compatível com HostGator / cPanel / phpMyAdmin / MySQL 5.6+ / MariaDB antigos
-- Objetivo:
-- 1) adicionar referencia e tipo_calculo em evento_salarios e funcionario_eventos sem usar ADD COLUMN IF NOT EXISTS
-- 2) preencher defaults úteis para folha
-- 3) alinhar eventos padrão para cálculo automático
-- 4) evitar erros de sintaxe em hospedagem compartilhada

-- =========================================================
-- BLOCO 1 - evento_salarios.referencia
-- =========================================================
SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'evento_salarios'
              AND COLUMN_NAME = 'referencia'
        ),
        'SELECT "COLUNA evento_salarios.referencia JA EXISTE" AS msg',
        'ALTER TABLE evento_salarios ADD COLUMN referencia DECIMAL(10,2) NULL AFTER tipo_valor'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =========================================================
-- BLOCO 2 - evento_salarios.tipo_calculo
-- =========================================================
SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'evento_salarios'
              AND COLUMN_NAME = 'tipo_calculo'
        ),
        'SELECT "COLUNA evento_salarios.tipo_calculo JA EXISTE" AS msg',
        'ALTER TABLE evento_salarios ADD COLUMN tipo_calculo VARCHAR(50) NULL AFTER referencia'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =========================================================
-- BLOCO 3 - funcionario_eventos.referencia
-- =========================================================
SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'funcionario_eventos'
              AND COLUMN_NAME = 'referencia'
        ),
        'SELECT "COLUNA funcionario_eventos.referencia JA EXISTE" AS msg',
        'ALTER TABLE funcionario_eventos ADD COLUMN referencia DECIMAL(10,2) NULL AFTER valor'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =========================================================
-- BLOCO 4 - funcionario_eventos.tipo_calculo
-- =========================================================
SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'funcionario_eventos'
              AND COLUMN_NAME = 'tipo_calculo'
        ),
        'SELECT "COLUNA funcionario_eventos.tipo_calculo JA EXISTE" AS msg',
        'ALTER TABLE funcionario_eventos ADD COLUMN tipo_calculo VARCHAR(50) NULL AFTER referencia'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =========================================================
-- BLOCO 5 - defaults para evento_salarios.tipo_calculo
-- =========================================================
UPDATE evento_salarios
SET tipo_calculo = 'SALARIO_BASE'
WHERE UPPER(TRIM(nome)) = 'SALARIO'
  AND (tipo_calculo IS NULL OR tipo_calculo = '');

UPDATE evento_salarios
SET tipo_calculo = 'DIAS_TRABALHADOS'
WHERE UPPER(TRIM(nome)) IN ('DIAS TRABALHADO', 'DIAS TRABALHADOS')
  AND (tipo_calculo IS NULL OR tipo_calculo = '');

UPDATE evento_salarios
SET tipo_calculo = 'INSS'
WHERE UPPER(TRIM(nome)) = 'INSS'
  AND (tipo_calculo IS NULL OR tipo_calculo = '');

UPDATE evento_salarios
SET tipo_calculo = 'IRRF'
WHERE UPPER(TRIM(nome)) = 'IRRF'
  AND (tipo_calculo IS NULL OR tipo_calculo = '');

UPDATE evento_salarios
SET tipo_calculo = 'FGTS'
WHERE UPPER(TRIM(nome)) = 'FGTS'
  AND (tipo_calculo IS NULL OR tipo_calculo = '');

UPDATE evento_salarios
SET tipo_calculo = 'HORA_EXTRA_50'
WHERE UPPER(TRIM(nome)) IN ('HORA EXTRA 50', 'HORA EXTRA 50%')
  AND (tipo_calculo IS NULL OR tipo_calculo = '');

UPDATE evento_salarios
SET tipo_calculo = 'HORA_EXTRA_100'
WHERE UPPER(TRIM(nome)) IN ('HORA EXTRA 100', 'HORA EXTRA 100%')
  AND (tipo_calculo IS NULL OR tipo_calculo = '');

UPDATE evento_salarios
SET tipo_calculo = 'FALTA'
WHERE UPPER(TRIM(nome)) IN ('FALTA', 'FALTAS')
  AND (tipo_calculo IS NULL OR tipo_calculo = '');

UPDATE evento_salarios
SET tipo_calculo = 'DSR_HE'
WHERE UPPER(TRIM(nome)) IN ('DSR', 'DSR HE', 'DSR SOBRE HORA EXTRA', 'DSR SOBRE HORA EXTRA')
  AND (tipo_calculo IS NULL OR tipo_calculo = '');

-- =========================================================
-- BLOCO 6 - referência padrão do salário = 30
-- =========================================================
UPDATE evento_salarios
SET referencia = 30.00
WHERE UPPER(TRIM(nome)) = 'SALARIO'
  AND referencia IS NULL;

-- =========================================================
-- BLOCO 7 - sincronizar tipo_calculo do vínculo com o cadastro do evento
-- =========================================================
UPDATE funcionario_eventos fe
INNER JOIN evento_salarios es ON es.id = fe.evento_id
SET fe.tipo_calculo = es.tipo_calculo
WHERE (fe.tipo_calculo IS NULL OR fe.tipo_calculo = '')
  AND es.tipo_calculo IS NOT NULL
  AND es.tipo_calculo <> '';

-- =========================================================
-- BLOCO 8 - sincronizar referência do vínculo com o cadastro do evento
-- =========================================================
UPDATE funcionario_eventos fe
INNER JOIN evento_salarios es ON es.id = fe.evento_id
SET fe.referencia = es.referencia
WHERE fe.referencia IS NULL
  AND es.referencia IS NOT NULL;

-- =========================================================
-- BLOCO 9 - referência padrão para salário nos vínculos
-- =========================================================
UPDATE funcionario_eventos fe
INNER JOIN evento_salarios es ON es.id = fe.evento_id
SET fe.referencia = 30.00
WHERE UPPER(TRIM(es.nome)) = 'SALARIO'
  AND fe.referencia IS NULL;

-- =========================================================
-- BLOCO 10 - criar eventos padrão faltantes sem duplicar
-- =========================================================
INSERT INTO evento_salarios (nome, tipo, metodo, condicao, tipo_valor, ativo, empresa_id, referencia, tipo_calculo, created_at, updated_at)
SELECT 'HORA EXTRA 50', 'mensal', 'informado', 'soma', 'fixo', 1, e.id, NULL, 'HORA_EXTRA_50', NOW(), NOW()
FROM empresas e
WHERE NOT EXISTS (
    SELECT 1 FROM evento_salarios es
    WHERE es.empresa_id = e.id
      AND UPPER(TRIM(es.nome)) IN ('HORA EXTRA 50', 'HORA EXTRA 50%')
);

INSERT INTO evento_salarios (nome, tipo, metodo, condicao, tipo_valor, ativo, empresa_id, referencia, tipo_calculo, created_at, updated_at)
SELECT 'HORA EXTRA 100', 'mensal', 'informado', 'soma', 'fixo', 1, e.id, NULL, 'HORA_EXTRA_100', NOW(), NOW()
FROM empresas e
WHERE NOT EXISTS (
    SELECT 1 FROM evento_salarios es
    WHERE es.empresa_id = e.id
      AND UPPER(TRIM(es.nome)) IN ('HORA EXTRA 100', 'HORA EXTRA 100%')
);

INSERT INTO evento_salarios (nome, tipo, metodo, condicao, tipo_valor, ativo, empresa_id, referencia, tipo_calculo, created_at, updated_at)
SELECT 'FALTA', 'mensal', 'informado', 'diminui', 'fixo', 1, e.id, NULL, 'FALTA', NOW(), NOW()
FROM empresas e
WHERE NOT EXISTS (
    SELECT 1 FROM evento_salarios es
    WHERE es.empresa_id = e.id
      AND UPPER(TRIM(es.nome)) IN ('FALTA', 'FALTAS')
);

INSERT INTO evento_salarios (nome, tipo, metodo, condicao, tipo_valor, ativo, empresa_id, referencia, tipo_calculo, created_at, updated_at)
SELECT 'DSR HE', 'mensal', 'informado', 'soma', 'fixo', 1, e.id, NULL, 'DSR_HE', NOW(), NOW()
FROM empresas e
WHERE NOT EXISTS (
    SELECT 1 FROM evento_salarios es
    WHERE es.empresa_id = e.id
      AND UPPER(TRIM(es.nome)) IN ('DSR HE', 'DSR', 'DSR SOBRE HORA EXTRA', 'DSR SOBRE HORAS EXTRAS')
);

-- =========================================================
-- BLOCO 11 - opcional: converter referencia para texto
-- Use apenas se precisar gravar '220h', '12 dias' ou '%'
-- Descomente manualmente se desejar.
-- =========================================================
-- ALTER TABLE evento_salarios MODIFY COLUMN referencia VARCHAR(30) NULL;
-- ALTER TABLE funcionario_eventos MODIFY COLUMN referencia VARCHAR(30) NULL;

-- Fim do patch HostGator.
