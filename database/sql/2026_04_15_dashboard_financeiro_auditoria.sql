-- Refatoração do dashboard financeiro executada sem alteração estrutural de schema.
-- Nenhuma migration nova foi necessária para esta entrega.
-- Este arquivo contém queries de auditoria para validar manualmente os indicadores.

-- 1) Vendas válidas do mês (exclui canceladas e rejeitadas)
SELECT COUNT(*) AS qtd_vendas_validas,
       COALESCE(SUM(valor_total), 0) AS total_vendas_validas
FROM vendas
WHERE empresa_id = :empresa_id
  AND (:filial_id IS NULL OR filial_id = :filial_id)
  AND (estado_emissao IS NULL OR estado_emissao NOT IN ('cancelado', 'rejeitado'))
  AND DATE(COALESCE(data_registro, created_at)) BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE());

-- 2) Vendas válidas do PDV no mês (exclui canceladas, rejeitadas, rascunhos e soft deleted)
SELECT COUNT(*) AS qtd_vendas_pdv_validas,
       COALESCE(SUM(valor_total), 0) AS total_vendas_pdv_validas
FROM venda_caixas
WHERE empresa_id = :empresa_id
  AND (:filial_id IS NULL OR filial_id = :filial_id)
  AND deleted_at IS NULL
  AND (estado_emissao IS NULL OR estado_emissao NOT IN ('cancelado', 'rejeitado'))
  AND (rascunho IS NULL OR rascunho = 0)
  AND DATE(COALESCE(data_registro, created_at)) BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE());

-- 3) Contas a receber em aberto com saldo residual real
SELECT COALESCE(SUM(
    CASE
        WHEN COALESCE(valor_integral, 0) - COALESCE(valor_recebido, 0) > 0
            THEN COALESCE(valor_integral, 0) - COALESCE(valor_recebido, 0)
        ELSE 0
    END
), 0) AS contas_receber_aberto_real
FROM conta_recebers
WHERE empresa_id = :empresa_id
  AND (:filial_id IS NULL OR filial_id = :filial_id)
  AND status = 0;

-- 4) Contas a pagar em aberto com saldo residual real
SELECT COALESCE(SUM(
    CASE
        WHEN COALESCE(valor_integral, 0) - COALESCE(valor_pago, 0) > 0
            THEN COALESCE(valor_integral, 0) - COALESCE(valor_pago, 0)
        ELSE 0
    END
), 0) AS contas_pagar_aberto_real
FROM conta_pagars
WHERE empresa_id = :empresa_id
  AND (:filial_id IS NULL OR filial_id = :filial_id)
  AND status = 0;

-- 5) Produtos ativos cadastrados
SELECT COUNT(*) AS produtos_ativos
FROM produtos
WHERE empresa_id = :empresa_id
  AND (inativo IS NULL OR inativo = 0)
  AND (
      :filial_id IS NULL
      OR filial_id = :filial_id
      OR locais = CAST(:filial_id AS CHAR)
      OR locais LIKE CONCAT('%"', :filial_id, '"%')
      OR locais LIKE CONCAT('%[', :filial_id, ']%')
      OR locais LIKE CONCAT(:filial_id, ',%')
      OR locais LIKE CONCAT('%,', :filial_id, ',%')
      OR locais LIKE CONCAT('%,', :filial_id)
  );
