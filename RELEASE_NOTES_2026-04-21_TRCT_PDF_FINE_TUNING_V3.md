# TRCT PDF Fine Tuning V3 - 2026-04-21

## Objetivo
Refinar milimetricamente a saída do TRCT/TQRCT/Homologação para impressão A4, com foco em aproximação visual ao modelo escaneado/oficial.

## Ajustes aplicados
- Redução de margens de página de 4mm para 3mm.
- Ajuste fino da moldura externa, cabeçalho e faixa lateral em milímetros.
- Redução de espessura visual das bordas para melhor aderência ao formulário impresso.
- Recalibração de alturas de linhas dos blocos de identificação, contrato, recebimento, assinaturas e homologação.
- Ajuste fino de fonte e espaçamento em labels, valores e tabela de verbas.
- Permissão de quebra controlada em campos longos estratégicos (endereços, CTPS, causa do afastamento, categoria).
- Incremento do DPI do DOMPDF para 144.
- Incremento do DPI/imagem do Snappy para 144 com zoom 1 e margens de 3mm.
- Inclusão de referências automáticas mais próximas do formulário oficial:
  - saldo de salário em dias
  - 13º em avos
  - férias proporcionais em avos
  - FGTS em meses/dias

## Arquivos alterados
- app/Modules/RH/Support/RescisaoPdfDataBuilder.php
- app/Modules/RH/Support/RescisaoPdfRenderer.php
- resources/views/rh/documentos/pdf/documento_rescisao_pdf.blade.php

## Migrations
Nenhuma nova migration nesta revisão.

## SQL manual
Mantém-se sem alteração estrutural de banco.
