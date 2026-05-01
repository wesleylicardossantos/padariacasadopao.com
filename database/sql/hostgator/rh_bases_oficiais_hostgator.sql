-- Base oficial RH para HostGator / MySQL 5.6/5.7
-- Cria tabelas auxiliares para Categoria do Trabalhador, Tipo de Contrato,
-- Natureza da Atividade, CBO e Departamentos internos.

CREATE TABLE IF NOT EXISTS `rh_official_worker_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `grupo` varchar(120) DEFAULT NULL,
  `inicio_vigencia` date DEFAULT NULL,
  `fim_vigencia` date DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `fonte` varchar(120) DEFAULT NULL,
  `fonte_url` varchar(255) DEFAULT NULL,
  `fonte_atualizada_em` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rh_official_worker_categories_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rh_official_contract_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `fonte` varchar(120) DEFAULT NULL,
  `fonte_url` varchar(255) DEFAULT NULL,
  `fonte_atualizada_em` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rh_official_contract_types_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rh_official_nature_activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `fonte` varchar(120) DEFAULT NULL,
  `fonte_url` varchar(255) DEFAULT NULL,
  `fonte_atualizada_em` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rh_official_nature_activities_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rh_official_cbo_occupations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `titulo_normalizado` varchar(255) DEFAULT NULL,
  `fonte` varchar(120) DEFAULT NULL,
  `fonte_url` varchar(255) DEFAULT NULL,
  `fonte_atualizada_em` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rh_official_cbo_occupations_codigo_unique` (`codigo`),
  KEY `rh_official_cbo_occupations_codigo_index` (`codigo`),
  KEY `rh_official_cbo_occupations_titulo_normalizado_index` (`titulo_normalizado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rh_department_references` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `descricao` varchar(120) NOT NULL,
  `ordem` int(10) unsigned NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rh_department_references_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `rh_official_contract_types`;
INSERT INTO `rh_official_contract_types`
(`codigo`,`descricao`,`ativo`,`fonte`,`fonte_url`,`fonte_atualizada_em`,`created_at`,`updated_at`)
VALUES
('1','Prazo indeterminado',1,'eSocial leiaute S-1.3','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-versao-s-1-3-nt-06-2026-rev-09-04-2026/index.html',NOW(),NOW(),NOW()),
('2','Prazo determinado, definido em dias',1,'eSocial leiaute S-1.3','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-versao-s-1-3-nt-06-2026-rev-09-04-2026/index.html',NOW(),NOW(),NOW()),
('3','Prazo determinado, vinculado à ocorrência de um fato',1,'eSocial leiaute S-1.3','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-versao-s-1-3-nt-06-2026-rev-09-04-2026/index.html',NOW(),NOW(),NOW());

DELETE FROM `rh_official_nature_activities`;
INSERT INTO `rh_official_nature_activities`
(`codigo`,`descricao`,`ativo`,`fonte`,`fonte_url`,`fonte_atualizada_em`,`created_at`,`updated_at`)
VALUES
('1','Trabalho urbano',1,'eSocial leiaute S-1.3','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-versao-s-1-3-nt-06-2026-rev-09-04-2026/index.html',NOW(),NOW(),NOW()),
('2','Trabalho rural',1,'eSocial leiaute S-1.3','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-versao-s-1-3-nt-06-2026-rev-09-04-2026/index.html',NOW(),NOW(),NOW());

DELETE FROM `rh_department_references`;
INSERT INTO `rh_department_references`
(`codigo`,`descricao`,`ordem`,`ativo`,`created_at`,`updated_at`)
VALUES
('ADM','ADMINISTRATIVO',10,1,NOW(),NOW()),
('COM','COMERCIAL',20,1,NOW(),NOW()),
('FIN','FINANCEIRO',30,1,NOW(),NOW()),
('RHU','RH',40,1,NOW(),NOW()),
('LOG','LOGISTICA',50,1,NOW(),NOW()),
('OPE','OPERACIONAL',60,1,NOW(),NOW()),
('PRD','PRODUCAO',70,1,NOW(),NOW()),
('SUP','SUPORTE',80,1,NOW(),NOW()),
('TEC','TECNOLOGIA',90,1,NOW(),NOW()),
('OUT','OUTROS',100,1,NOW(),NOW());

DELETE FROM `rh_official_worker_categories`;
INSERT INTO `rh_official_worker_categories`
(`codigo`,`descricao`,`grupo`,`inicio_vigencia`,`fim_vigencia`,`ativo`,`fonte`,`fonte_url`,`fonte_atualizada_em`,`created_at`,`updated_at`)
VALUES
('101','Empregado - Geral, inclusive o empregado público da administração direta ou indireta contratado pela CLT','Empregado e Trabalhador Temporário','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('102','Empregado - Trabalhador rural por pequeno prazo da Lei 11.718/2008','Empregado e Trabalhador Temporário','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('103','Empregado - Aprendiz','Empregado e Trabalhador Temporário','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('104','Empregado - Doméstico','Empregado e Trabalhador Temporário','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('105','Empregado - Contrato a termo firmado nos termos da Lei 9.601/1998','Empregado e Trabalhador Temporário','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('106','Trabalhador temporário - Contrato nos termos da Lei 6.019/1974','Empregado e Trabalhador Temporário','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('107','Empregado - Contrato de trabalho Verde e Amarelo - sem acordo para antecipação mensal da multa rescisória do FGTS','Empregado e Trabalhador Temporário','2020-01-01','2022-12-31',0,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('108','Empregado - Contrato de trabalho Verde e Amarelo - com acordo para antecipação mensal da multa rescisória do FGTS','Empregado e Trabalhador Temporário','2020-01-01','2022-12-31',0,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('111','Empregado - Contrato de trabalho intermitente','Empregado e Trabalhador Temporário','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('201','Trabalhador avulso portuário','Avulso','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('202','Trabalhador avulso não portuário','Avulso','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('301','Servidor público titular de cargo efetivo, magistrado, ministro de Tribunal de Contas, conselheiro de Tribunal de Contas e membro do Ministério Público','Agente Público','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('302','Servidor público ocupante de cargo exclusivo em comissão','Agente Público','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('303','Exercente de mandato eletivo','Agente Público','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('304','Servidor público exercente de mandato eletivo, inclusive com exercício de cargo em comissão','Agente Público','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('305','Servidor público indicado para conselho ou órgão deliberativo, na condição de representante do governo, órgão ou entidade da administração pública','Agente Público','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('306','Servidor público contratado por tempo determinado, sujeito a regime administrativo especial definido em lei própria','Agente Público','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('307','Militar dos Estados e Distrito Federal','Agente Público','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('308','Conscrito','Agente Público','2014-01-01','2023-04-25',0,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('309','Agente público - Outros','Agente Público','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('310','Servidor público eventual','Agente Público','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('311','Ministros, juízes, procuradores, promotores ou oficiais de justiça à disposição da Justiça Eleitoral','Agente Público','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('312','Auxiliar local','Agente Público','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('313','Servidor público exercente de atividade de instrutoria, curso ou concurso, convocado para pareceres técnicos, depoimentos ou aditância no exterior','Agente Público','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('314','Militar das Forças Armadas','Agente Público','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('401','Dirigente sindical - Informação prestada pelo sindicato','Cessão','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('410','Trabalhador cedido/exercício em outro órgão/juiz auxiliar - Informação prestada pelo cessionário/destino','Cessão','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('501','Dirigente sindical - Segurado especial','Segurado Especial','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('701','Contribuinte individual - Autônomo em geral, exceto se enquadrado em uma das demais categorias de contribuinte individual','Contribuinte Individual','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('711','Contribuinte individual - Transportador autônomo de passageiros','Contribuinte Individual','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('712','Contribuinte individual - Transportador autônomo de carga','Contribuinte Individual','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('721','Contribuinte individual - Diretor não empregado, com FGTS','Contribuinte Individual','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('722','Contribuinte individual - Diretor não empregado, sem FGTS','Contribuinte Individual','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('723','Contribuinte individual - Empresário, sócio e membro de conselho de administração ou fiscal','Contribuinte Individual','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('731','Contribuinte individual - Cooperado que presta serviços por intermédio de cooperativa de trabalho','Contribuinte Individual','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('734','Contribuinte individual - Transportador cooperado que presta serviços por intermédio de cooperativa de trabalho','Contribuinte Individual','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('738','Contribuinte individual - Cooperado filiado a cooperativa de produção','Contribuinte Individual','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('741','Contribuinte individual - Microempreendedor individual','Contribuinte Individual','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('751','Contribuinte individual - Magistrado classista temporário da Justiça do Trabalho ou da Justiça Eleitoral que seja aposentado de qualquer regime previdenciário','Contribuinte Individual','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('761','Contribuinte individual - Associado eleito para direção de cooperativa, associação ou entidade de classe de qualquer natureza ou finalidade, bem como o síndico ou administrador eleito para exercer atividade de direção condominial, desde que recebam remuneração','Contribuinte Individual','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('771','Contribuinte individual - Membro de conselho tutelar, nos termos da Lei 8.069/1990','Contribuinte Individual','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('781','Ministro de confissão religiosa ou membro de vida consagrada, de congregação ou de ordem religiosa','Contribuinte Individual','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('901','Estagiário','Bolsista','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('902','Médico residente, residente em área profissional de saúde ou médico em curso de formação','Bolsista','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('903','Bolsista','Bolsista','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('904','Participante de curso de formação, como etapa de concurso público, sem vínculo de emprego/estatutário','Bolsista','2014-01-01',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW()),
('906','Beneficiário do Programa Nacional de Prestação de Serviço Civil Voluntário','Bolsista','2022-01-28',NULL,1,'eSocial Tabela 01','https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html',NOW(),NOW(),NOW());

-- A tabela de CBO é criada vazia.
-- No sistema, a primeira abertura da ficha do funcionário ou o comando
-- php artisan rh:sync-official-labor-data --force
-- fará o carregamento automático da base oficial de ocupações.


-- Indicativos de admissão oficiais do eSocial
CREATE TABLE IF NOT EXISTS rh_official_admission_indicators (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    fonte VARCHAR(120) NULL,
    fonte_url VARCHAR(255) NULL,
    fonte_atualizada_em TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY rh_official_admission_indicators_codigo_unique (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO rh_official_admission_indicators
(codigo, descricao, ativo, fonte, fonte_url, fonte_atualizada_em, created_at, updated_at)
SELECT '1', 'Admissão normal', 1, 'eSocial S-2200/S-2300', 'https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-versao-s-1-3-nt-06-2026-rev-09-04-2026/index.html', NOW(), NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM rh_official_admission_indicators WHERE codigo = '1');

INSERT INTO rh_official_admission_indicators
(codigo, descricao, ativo, fonte, fonte_url, fonte_atualizada_em, created_at, updated_at)
SELECT '2', 'Decorrente de ação fiscal', 1, 'eSocial S-2200/S-2300', 'https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-versao-s-1-3-nt-06-2026-rev-09-04-2026/index.html', NOW(), NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM rh_official_admission_indicators WHERE codigo = '2');

INSERT INTO rh_official_admission_indicators
(codigo, descricao, ativo, fonte, fonte_url, fonte_atualizada_em, created_at, updated_at)
SELECT '3', 'Decorrente de decisão judicial', 1, 'eSocial S-2200/S-2300', 'https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-versao-s-1-3-nt-06-2026-rev-09-04-2026/index.html', NOW(), NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM rh_official_admission_indicators WHERE codigo = '3');


-- Funções oficiais padronizadas a partir da base CBO/eSocial
CREATE TABLE IF NOT EXISTS `rh_official_functions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `descricao_normalizada` varchar(255) DEFAULT NULL,
  `cbo_codigo` varchar(10) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `fonte` varchar(120) DEFAULT NULL,
  `fonte_url` varchar(255) DEFAULT NULL,
  `fonte_atualizada_em` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rh_official_functions_codigo_unique` (`codigo`),
  KEY `rh_official_functions_descricao_normalizada_index` (`descricao_normalizada`),
  KEY `rh_official_functions_cbo_codigo_index` (`cbo_codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Popular após carregar a CBO oficial.
-- Exemplo compatível com HostGator/MySQL 5.6/5.7:
-- INSERT INTO rh_official_functions (codigo, descricao, descricao_normalizada, cbo_codigo, ativo, fonte, fonte_url, fonte_atualizada_em, created_at, updated_at)
-- SELECT CONCAT('FUNC-', codigo), titulo, LOWER(titulo), codigo, 1, 'CBO 2002 / eSocial (descrição do cargo/função)',
--        'https://www.gov.br/trabalho-e-emprego/pt-br/assuntos/cbo/servicos/downloads/downloads', NOW(), NOW(), NOW()
-- FROM rh_official_cbo_occupations;
