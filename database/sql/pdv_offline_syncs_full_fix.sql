ALTER TABLE `pdv_offline_syncs`
    ADD COLUMN `venda_caixa_id` BIGINT UNSIGNED NULL AFTER `status`,
    ADD COLUMN `request_payload` LONGTEXT NULL AFTER `venda_caixa_id`,
    ADD COLUMN `response_payload` LONGTEXT NULL AFTER `request_payload`,
    ADD COLUMN `erro` TEXT NULL AFTER `response_payload`,
    ADD COLUMN `sincronizado_em` TIMESTAMP NULL AFTER `erro`;

ALTER TABLE `pdv_offline_syncs`
    ADD INDEX `pdv_offline_syncs_venda_caixa_id_index` (`venda_caixa_id`),
    ADD UNIQUE `pdv_offline_syncs_empresa_uuid_unique` (`empresa_id`, `uuid_local`);
