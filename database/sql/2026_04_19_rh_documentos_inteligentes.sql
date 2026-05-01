-- RH Documentos Inteligentes
-- Compatível com MySQL/MariaDB em implantação manual no servidor.

CREATE TABLE IF NOT EXISTS `rh_document_templates` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `empresa_id` BIGINT UNSIGNED NULL,
  `nome` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(160) NULL,
  `categoria` VARCHAR(60) NULL,
  `tipo_documento` VARCHAR(80) NULL,
  `descricao` VARCHAR(255) NULL,
  `conteudo_html` LONGTEXT NULL,
  `conteudo_texto` LONGTEXT NULL,
  `usa_ia` TINYINT(1) NOT NULL DEFAULT 1,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `versao` VARCHAR(20) NOT NULL DEFAULT '1.0',
  `created_by` BIGINT UNSIGNED NULL,
  `updated_by` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rh_document_templates_empresa_id_index` (`empresa_id`),
  KEY `rh_document_templates_slug_index` (`slug`),
  KEY `rh_document_templates_categoria_index` (`categoria`),
  KEY `rh_document_templates_tipo_documento_index` (`tipo_documento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rh_documento_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `empresa_id` BIGINT UNSIGNED NULL,
  `documento_id` BIGINT UNSIGNED NULL,
  `funcionario_id` BIGINT UNSIGNED NULL,
  `acao` VARCHAR(60) NOT NULL,
  `usuario_id` BIGINT UNSIGNED NULL,
  `detalhes` TEXT NULL,
  `payload_resumo` JSON NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rh_documento_logs_empresa_id_index` (`empresa_id`),
  KEY `rh_documento_logs_documento_id_index` (`documento_id`),
  KEY `rh_documento_logs_funcionario_id_index` (`funcionario_id`),
  KEY `rh_documento_logs_acao_index` (`acao`),
  KEY `rh_documento_logs_usuario_id_index` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @has_template_id := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'rh_documentos' AND COLUMN_NAME = 'template_id');
SET @sql := IF(@has_template_id = 0, 'ALTER TABLE `rh_documentos` ADD COLUMN `template_id` BIGINT UNSIGNED NULL AFTER `funcionario_id`, ADD INDEX `rh_documentos_template_id_index` (`template_id`);', 'SELECT 1;');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_conteudo_html := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'rh_documentos' AND COLUMN_NAME = 'conteudo_html');
SET @sql := IF(@has_conteudo_html = 0, 'ALTER TABLE `rh_documentos` ADD COLUMN `conteudo_html` LONGTEXT NULL AFTER `arquivo`;', 'SELECT 1;');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_conteudo_texto := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'rh_documentos' AND COLUMN_NAME = 'conteudo_texto');
SET @sql := IF(@has_conteudo_texto = 0, 'ALTER TABLE `rh_documentos` ADD COLUMN `conteudo_texto` LONGTEXT NULL AFTER `conteudo_html`;', 'SELECT 1;');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_status := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'rh_documentos' AND COLUMN_NAME = 'status');
SET @sql := IF(@has_status = 0, 'ALTER TABLE `rh_documentos` ADD COLUMN `status` VARCHAR(40) NULL AFTER `origem`, ADD INDEX `rh_documentos_status_index` (`status`);', 'SELECT 1;');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_hash := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'rh_documentos' AND COLUMN_NAME = 'hash_conteudo');
SET @sql := IF(@has_hash = 0, 'ALTER TABLE `rh_documentos` ADD COLUMN `hash_conteudo` VARCHAR(64) NULL AFTER `status`, ADD INDEX `rh_documentos_hash_conteudo_index` (`hash_conteudo`);', 'SELECT 1;');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_ia_provider := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'rh_documentos' AND COLUMN_NAME = 'ia_provider');
SET @sql := IF(@has_ia_provider = 0, 'ALTER TABLE `rh_documentos` ADD COLUMN `ia_provider` VARCHAR(40) NULL AFTER `hash_conteudo`;', 'SELECT 1;');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_ia_model := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'rh_documentos' AND COLUMN_NAME = 'ia_model');
SET @sql := IF(@has_ia_model = 0, 'ALTER TABLE `rh_documentos` ADD COLUMN `ia_model` VARCHAR(80) NULL AFTER `ia_provider`;', 'SELECT 1;');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

INSERT INTO `rh_document_templates` (`empresa_id`,`nome`,`slug`,`categoria`,`tipo_documento`,`descricao`,`conteudo_html`,`conteudo_texto`,`usa_ia`,`ativo`,`versao`,`created_at`,`updated_at`)
SELECT NULL,'Contrato de Trabalho CLT','contrato-trabalho-clt','contrato','contrato_trabalho','Contrato base para admissão de colaborador.','<p><strong>{{empresa_nome}}</strong>, inscrita no CNPJ {{empresa_cnpj}}, estabelece o presente contrato de trabalho com <strong>{{funcionario_nome}}</strong>, CPF {{funcionario_cpf}}, para exercer a função de <strong>{{funcionario_cargo}}</strong>, com salário de <strong>{{funcionario_salario}}</strong>.</p><p>O ingresso do colaborador ocorreu em {{funcionario_data_admissao}}, observadas as regras internas, a CLT e as políticas vigentes da empresa.</p><p>{{observacoes_adicionais}}</p><div class="assinatura"><div class="linha"></div><div>{{usuario_responsavel}}</div><div>Responsável pela empresa</div></div>','Contrato de Trabalho CLT',1,1,'1.0',NOW(),NOW()
WHERE NOT EXISTS (SELECT 1 FROM `rh_document_templates` WHERE `slug`='contrato-trabalho-clt');

INSERT INTO `rh_document_templates` (`empresa_id`,`nome`,`slug`,`categoria`,`tipo_documento`,`descricao`,`conteudo_html`,`conteudo_texto`,`usa_ia`,`ativo`,`versao`,`created_at`,`updated_at`)
SELECT NULL,'Termo de Rescisão','termo-rescisao','rescisao','rescisao','Termo base para desligamento.','<p>Fica formalizado o desligamento de <strong>{{funcionario_nome}}</strong>, CPF {{funcionario_cpf}}, ocupante do cargo de {{funcionario_cargo}}, admitido em {{funcionario_data_admissao}}, com rescisão em {{data_rescisao}}.</p><p>Tipo de rescisão: <strong>{{tipo_rescisao}}</strong>. Motivo registrado: <strong>{{motivo_documento}}</strong>.</p><p>As verbas, documentos complementares e orientações finais seguirão o fechamento oficial da folha e dos procedimentos internos da empresa.</p><p>{{observacoes_adicionais}}</p><div class="assinatura"><div class="linha"></div><div>{{usuario_responsavel}}</div><div>Responsável RH</div></div>','Termo de Rescisão',1,1,'1.0',NOW(),NOW()
WHERE NOT EXISTS (SELECT 1 FROM `rh_document_templates` WHERE `slug`='termo-rescisao');

UPDATE `rh_document_templates`
SET `nome`='Contrato Individual de Trabalho',
    `descricao`='Contrato profissional com cláusulas condicionais por tipo e regime.',
    `conteudo_html`='<div class="contrato-rh"><h1 class="titulo-documento">CONTRATO INDIVIDUAL DE TRABALHO</h1><p class="subtitulo-documento">({{tipo_contrato_label}}) · Regime {{regime_trabalho}}</p><p>Pelo presente instrumento particular, de um lado <strong>{{empresa_nome}}</strong>, {{empresa_tipo_pessoa}}, inscrita no CPF/CNPJ sob o n.º <strong>{{empresa_cnpj}}</strong>, com sede em <strong>{{empresa_endereco}}</strong>, neste ato representada por <strong>{{empresa_representante_legal}}</strong>, CPF <strong>{{empresa_representante_cpf}}</strong>, doravante denominada <strong>EMPREGADORA</strong>.</p><p>E de outro lado <strong>{{funcionario_nome}}</strong>, {{funcionario_nacionalidade}}, {{funcionario_estado_civil}}, {{funcionario_profissao}}, portador(a) do CPF inscrito sob o nº <strong>{{funcionario_cpf}}</strong>, RG <strong>{{funcionario_rg}}</strong>, CTPS <strong>{{funcionario_ctps}}</strong>, série <strong>{{funcionario_ctps_serie}}</strong>, residente e domiciliado(a) em <strong>{{funcionario_endereco}}</strong>, doravante denominado(a) <strong>EMPREGADO(A)</strong>.</p><p>As partes, de comum acordo, contratam o presente <strong>CONTRATO INDIVIDUAL DE TRABALHO</strong>, que será regido pela Consolidação das Leis do Trabalho – CLT e pelas cláusulas seguintes:</p><h2>CLÁUSULA PRIMEIRA – DO OBJETO</h2><p>1.1. O presente contrato tem por objeto a prestação de serviços pelo(a) <strong>EMPREGADO(A)</strong>, que integra o quadro funcional da EMPREGADORA.</p><h2>CLÁUSULA SEGUNDA – DA FUNÇÃO</h2><p>2.1. O(A) EMPREGADO(A) exercerá a função de <strong>{{funcionario_cargo}}</strong>, comprometendo-se a desempenhar as seguintes atividades: {{funcionario_atividades}}.</p><p>2.2. O(A) EMPREGADO(A) poderá ser designado(a) para outra função compatível com sua condição pessoal, observadas as disposições legais.</p><h2>CLÁUSULA TERCEIRA – DA REMUNERAÇÃO</h2><p>3.1. Pela prestação de serviços, a EMPREGADORA pagará ao(à) EMPREGADO(A) o salário de <strong>R$ {{funcionario_salario}}</strong>, a ser pago {{periodicidade_pagamento}}, sujeito aos descontos legais e a eventuais adiantamentos.</p><p>3.2. O pagamento será realizado por {{forma_pagamento_documento}}, até o 5º dia útil do mês subsequente ao vencido.</p><p>3.3. Dados bancários: Banco {{banco}}, Agência {{agencia}}, Conta Corrente {{conta_corrente}}.</p><p>3.4. Benefícios concedidos: {{beneficios_descricao}}</p><h2>CLÁUSULA QUARTA – DOS DESCONTOS</h2><p>4.1. O(A) EMPREGADO(A) {{autoriza_contribuicao_sindical}} autoriza a contribuição sindical quando legalmente cabível e autorizada, bem como os demais descontos previstos em lei, norma coletiva ou reparação de danos na forma do art. 462 da CLT.</p><h2>CLÁUSULA QUINTA – DA JORNADA DE TRABALHO</h2><p>5.1. A jornada de trabalho observará o seguinte regime: {{jornada_descricao}}.</p><p>5.2. Serão assegurados o descanso semanal remunerado, os intervalos legais e, quando houver horas extras, sua remuneração ou compensação na forma da lei.</p>{{#presencial}}<h2>CLÁUSULA SEXTA – DO LOCAL DE TRABALHO</h2><p>6.1. O(A) EMPREGADO(A) prestará serviços em <strong>{{local_trabalho}}</strong>, em regime <strong>{{regime_trabalho}}</strong>.</p><p>6.2. Qualquer alteração relevante de local ou de regime obedecerá à legislação aplicável e, quando necessário, será formalizada por aditivo contratual.</p>{{/presencial}}{{#teletrabalho}}<h2>CLÁUSULA SEXTA – DO TELETRABALHO</h2><p>6.1. As atividades do(a) EMPREGADO(A) serão exercidas preponderantemente fora das dependências da EMPREGADORA, em regime de <strong>{{regime_trabalho}}</strong>, com uso de tecnologias de informação e comunicação.</p><p>6.2. A EMPREGADORA disponibilizará ou reembolsará os recursos necessários ao desempenho das atividades, na forma ajustada entre as partes e da legislação aplicável.</p>{{/teletrabalho}}{{#indeterminado}}<h2>CLÁUSULA SÉTIMA – DO PRAZO DO CONTRATO</h2><p>7.1. O presente contrato é firmado por <strong>prazo indeterminado</strong>, iniciando-se em {{funcionario_data_admissao}}.</p>{{/indeterminado}}{{#determinado}}<h2>CLÁUSULA SÉTIMA – DO PRAZO DO CONTRATO</h2><p>7.1. O presente contrato é firmado por <strong>prazo determinado</strong>.</p><p>7.2. {{prazo_contrato_descricao}}</p>{{/determinado}}{{#intermitente}}<h2>CLÁUSULA SÉTIMA – DO PRAZO DO CONTRATO</h2><p>7.1. O presente contrato é firmado na modalidade de <strong>trabalho intermitente</strong>, com alternância de períodos de prestação de serviços e de inatividade, na forma da lei.</p><p>7.2. {{prazo_contrato_descricao}}</p>{{/intermitente}}<h2>CLÁUSULA OITAVA – DA CONFIDENCIALIDADE</h2><p>8.1. O(A) EMPREGADO(A) compromete-se a manter sigilo sobre informações confidenciais da EMPREGADORA durante e após a vigência deste contrato.</p><p>8.2. A violação do dever de confidencialidade poderá ensejar sanções disciplinares, rescisão por justa causa e reparação por perdas e danos. {{confidencialidade_multa}}</p><h2>CLÁUSULA NONA – DA RESCISÃO</h2><p>9.1. A rescisão contratual observará as disposições legais aplicáveis, com pagamento das verbas devidas e entrega dos documentos obrigatórios.</p><h2>CLÁUSULA DÉCIMA – DAS CONSIDERAÇÕES FINAIS</h2><p>10.1. Este contrato poderá ser alterado apenas mediante acordo escrito entre as partes.</p><p>10.2. Fica eleito o foro da comarca de {{foro_cidade}}, com renúncia a qualquer outro, para dirimir dúvidas ou questões decorrentes deste contrato.</p><p>E, por estarem justas e contratadas, as partes assinam o presente instrumento em 2 (duas) vias de igual teor e forma.</p><p class="data-direita">{{foro_cidade}}, {{data_hoje_extenso}}.</p><table class="assinaturas sem-quebra"><tr><td><div class="linha-assinatura"></div><div class="assinatura-nome">{{funcionario_nome}}</div><div class="assinatura-papel">EMPREGADO(A)</div></td><td><div class="linha-assinatura"></div><div class="assinatura-nome">{{empresa_nome}}</div><div class="assinatura-papel">EMPREGADORA</div></td></tr></table></div>',
    `conteudo_texto`='Contrato Individual de Trabalho',
    `versao`='2.0',
    `updated_at`=NOW()
WHERE `slug`='contrato-trabalho-clt';
