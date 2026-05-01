ALTER TABLE rh_folha_fechamentos
    ADD COLUMN fechado_por BIGINT UNSIGNED NULL AFTER usuario_id,
    ADD COLUMN reaberto_por BIGINT UNSIGNED NULL AFTER fechado_por;
