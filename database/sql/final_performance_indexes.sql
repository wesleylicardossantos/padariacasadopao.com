-- Índices idempotentes recomendados para produção.
-- Use apenas em ambiente MySQL/MariaDB após backup válido.
ALTER TABLE `sangria_caixas` ADD INDEX `sangria_caixas_empresa_data_registro_idx` (`empresa_id`, `data_registro`);
ALTER TABLE `sangria_caixas` ADD INDEX `sangria_caixas_empresa_created_at_idx` (`empresa_id`, `created_at`);
ALTER TABLE `clientes` ADD INDEX `clientes_empresa_cpf_cnpj_idx` (`empresa_id`, `cpf_cnpj`);
ALTER TABLE `clientes` ADD INDEX `clientes_empresa_razao_social_idx` (`empresa_id`, `razao_social`);
ALTER TABLE `venda_caixas` ADD INDEX `venda_caixas_empresa_data_deleted_idx` (`empresa_id`, `data_registro`, `deleted_at`);
ALTER TABLE `venda_caixas` ADD INDEX `venda_caixas_empresa_tipo_pagamento_idx` (`empresa_id`, `tipo_pagamento`);
ALTER TABLE `vendas` ADD INDEX `vendas_empresa_data_registro_idx` (`empresa_id`, `data_registro`);
ALTER TABLE `vendas` ADD INDEX `vendas_empresa_estado_emissao_idx` (`empresa_id`, `estado_emissao`);
