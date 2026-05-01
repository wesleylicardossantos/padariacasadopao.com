-- Consolidação documental RH / Portal / Dossiê
-- Execução manual segura para Hostgator

ALTER TABLE rh_documentos
    ADD COLUMN IF NOT EXISTS status VARCHAR(40) NULL AFTER origem,
    ADD COLUMN IF NOT EXISTS hash_conteudo VARCHAR(120) NULL AFTER metadata_json;

ALTER TABLE rh_dossie_eventos
    ADD COLUMN IF NOT EXISTS visibilidade_portal TINYINT(1) NOT NULL DEFAULT 0 AFTER data_evento;
