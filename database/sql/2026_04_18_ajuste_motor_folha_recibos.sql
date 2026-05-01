-- Ajuste corretivo para recibos/holerites: sincroniza o codigo do evento nos itens persistidos.
-- Execute somente se a tabela existir no seu banco.

UPDATE rh_folha_itens fi
JOIN evento_salarios es ON es.id = fi.evento_id
SET fi.evento_codigo = es.codigo
WHERE fi.evento_id IS NOT NULL
  AND (
    fi.evento_codigo IS NULL
    OR fi.evento_codigo = ''
    OR UPPER(fi.evento_codigo) = UPPER(fi.descricao)
  );
