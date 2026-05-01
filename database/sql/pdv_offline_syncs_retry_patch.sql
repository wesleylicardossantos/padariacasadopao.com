ALTER TABLE `pdv_offline_syncs`
    ADD COLUMN `tentativas` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `sincronizado_em`,
    ADD COLUMN `ultima_tentativa_em` TIMESTAMP NULL DEFAULT NULL AFTER `tentativas`;
