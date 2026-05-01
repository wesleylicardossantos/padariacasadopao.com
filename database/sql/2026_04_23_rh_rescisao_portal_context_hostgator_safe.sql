-- Consolidação RH/Rescisão/Portal - execução manual segura em Hostgator
ALTER TABLE rh_desligamentos ADD COLUMN IF NOT EXISTS rescisao_id BIGINT NULL AFTER usuario_id;
ALTER TABLE rh_portal_funcionarios ADD COLUMN IF NOT EXISTS perfil_id BIGINT NULL AFTER empresa_id;
ALTER TABLE rh_portal_funcionarios ADD COLUMN IF NOT EXISTS permissoes_extras JSON NULL AFTER perfil_id;

SET @idx_exists := (
    SELECT COUNT(1)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'rh_desligamentos'
      AND index_name = 'idx_rh_desligamentos_rescisao_id'
);
SET @sql := IF(@idx_exists = 0,
    'ALTER TABLE rh_desligamentos ADD INDEX idx_rh_desligamentos_rescisao_id (rescisao_id)',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists := (
    SELECT COUNT(1)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'rh_portal_funcionarios'
      AND index_name = 'idx_rh_portal_funcionarios_perfil_id'
);
SET @sql := IF(@idx_exists = 0,
    'ALTER TABLE rh_portal_funcionarios ADD INDEX idx_rh_portal_funcionarios_perfil_id (perfil_id)',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
