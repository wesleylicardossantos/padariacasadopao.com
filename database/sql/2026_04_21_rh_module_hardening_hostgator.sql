-- RH module hardening patch - Hostgator safe SQL
-- Corrige inconsistências do módulo RH sem perder dados existentes.

SET @db := DATABASE();

-- 1) rh_document_templates: colunas de auditoria e texto espelho
SET @sql := IF (
    EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = @db AND table_name = 'rh_document_templates')
    AND NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = @db AND table_name = 'rh_document_templates' AND column_name = 'conteudo_texto'),
    'ALTER TABLE rh_document_templates ADD COLUMN conteudo_texto LONGTEXT NULL AFTER conteudo_html',
    'SELECT ''rh_document_templates.conteudo_texto ok'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF (
    EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = @db AND table_name = 'rh_document_templates')
    AND NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = @db AND table_name = 'rh_document_templates' AND column_name = 'created_by'),
    'ALTER TABLE rh_document_templates ADD COLUMN created_by BIGINT UNSIGNED NULL AFTER versao',
    'SELECT ''rh_document_templates.created_by ok'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF (
    EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = @db AND table_name = 'rh_document_templates')
    AND NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = @db AND table_name = 'rh_document_templates' AND column_name = 'updated_by'),
    'ALTER TABLE rh_document_templates ADD COLUMN updated_by BIGINT UNSIGNED NULL AFTER created_by',
    'SELECT ''rh_document_templates.updated_by ok'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2) rh_ferias: campos do período aquisitivo
SET @sql := IF (
    EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = @db AND table_name = 'rh_ferias')
    AND NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = @db AND table_name = 'rh_ferias' AND column_name = 'periodo_aquisitivo_inicio'),
    'ALTER TABLE rh_ferias ADD COLUMN periodo_aquisitivo_inicio DATE NULL AFTER funcionario_id',
    'SELECT ''rh_ferias.periodo_aquisitivo_inicio ok'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF (
    EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = @db AND table_name = 'rh_ferias')
    AND NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = @db AND table_name = 'rh_ferias' AND column_name = 'periodo_aquisitivo_fim'),
    'ALTER TABLE rh_ferias ADD COLUMN periodo_aquisitivo_fim DATE NULL AFTER periodo_aquisitivo_inicio',
    'SELECT ''rh_ferias.periodo_aquisitivo_fim ok'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3) rh_portal_perfis: cria tabela se não existir
SET @sql := IF (
    NOT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = @db AND table_name = 'rh_portal_perfis'),
    'CREATE TABLE rh_portal_perfis (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        empresa_id BIGINT UNSIGNED NULL,
        nome VARCHAR(100) NOT NULL,
        slug VARCHAR(120) NULL,
        descricao VARCHAR(255) NULL,
        permissoes LONGTEXT NULL,
        ativo TINYINT(1) NOT NULL DEFAULT 1,
        escopo VARCHAR(50) NULL,
        created_at TIMESTAMP NULL DEFAULT NULL,
        updated_at TIMESTAMP NULL DEFAULT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'SELECT ''rh_portal_perfis table ok'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF (
    EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = @db AND table_name = 'rh_portal_perfis')
    AND NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = @db AND table_name = 'rh_portal_perfis' AND column_name = 'empresa_id'),
    'ALTER TABLE rh_portal_perfis ADD COLUMN empresa_id BIGINT UNSIGNED NULL AFTER id',
    'SELECT ''rh_portal_perfis.empresa_id ok'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF (
    EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = @db AND table_name = 'rh_portal_perfis')
    AND NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = @db AND table_name = 'rh_portal_perfis' AND column_name = 'slug'),
    'ALTER TABLE rh_portal_perfis ADD COLUMN slug VARCHAR(120) NULL AFTER nome',
    'SELECT ''rh_portal_perfis.slug ok'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF (
    EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = @db AND table_name = 'rh_portal_perfis')
    AND NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = @db AND table_name = 'rh_portal_perfis' AND column_name = 'descricao'),
    'ALTER TABLE rh_portal_perfis ADD COLUMN descricao VARCHAR(255) NULL AFTER slug',
    'SELECT ''rh_portal_perfis.descricao ok'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF (
    EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = @db AND table_name = 'rh_portal_perfis')
    AND NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = @db AND table_name = 'rh_portal_perfis' AND column_name = 'permissoes'),
    'ALTER TABLE rh_portal_perfis ADD COLUMN permissoes LONGTEXT NULL AFTER descricao',
    'SELECT ''rh_portal_perfis.permissoes ok'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF (
    EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = @db AND table_name = 'rh_portal_perfis')
    AND NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = @db AND table_name = 'rh_portal_perfis' AND column_name = 'ativo'),
    'ALTER TABLE rh_portal_perfis ADD COLUMN ativo TINYINT(1) NOT NULL DEFAULT 1 AFTER permissoes',
    'SELECT ''rh_portal_perfis.ativo ok'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF (
    EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = @db AND table_name = 'rh_portal_perfis')
    AND NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = @db AND table_name = 'rh_portal_perfis' AND column_name = 'escopo'),
    'ALTER TABLE rh_portal_perfis ADD COLUMN escopo VARCHAR(50) NULL AFTER ativo',
    'SELECT ''rh_portal_perfis.escopo ok'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
