-- Padronização segura da tabela rh_ferias para o schema RH V3
-- Compatível com bases antigas

ALTER TABLE rh_ferias
    ADD COLUMN periodo_aquisitivo_inicio DATE NULL AFTER funcionario_id,
    ADD COLUMN periodo_aquisitivo_fim DATE NULL AFTER periodo_aquisitivo_inicio,
    ADD COLUMN dias INT NOT NULL DEFAULT 30 AFTER data_fim,
    ADD COLUMN usuario_id BIGINT UNSIGNED NULL AFTER observacao;

-- Preencher dias automaticamente nas linhas antigas
UPDATE rh_ferias
SET dias = DATEDIFF(data_fim, data_inicio) + 1
WHERE (dias IS NULL OR dias = 0)
  AND data_inicio IS NOT NULL
  AND data_fim IS NOT NULL;

-- Padronizar status legado em maiúsculo para minúsculo
UPDATE rh_ferias
SET status = 'programada'
WHERE status IN ('PROGRAMADA', 'Programada');

UPDATE rh_ferias
SET status = 'concluida'
WHERE status IN ('CONCLUIDA', 'Concluída', 'concluída');

UPDATE rh_ferias
SET status = 'gozo'
WHERE status IN ('EM GOZO', 'Gozo', 'gozo');

UPDATE rh_ferias
SET status = 'pendente'
WHERE status IN ('PENDENTE', 'Pendente');

-- Opcional: depois de revisar os dados, você pode tornar os campos obrigatórios manualmente.
-- Exemplo futuro:
-- ALTER TABLE rh_ferias
--   MODIFY COLUMN periodo_aquisitivo_inicio DATE NOT NULL,
--   MODIFY COLUMN periodo_aquisitivo_fim DATE NOT NULL;
