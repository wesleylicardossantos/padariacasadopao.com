# RELEASE NOTES - 2026-04-21 - TRCT PDF FINE TUNING V2

## Escopo
Ajuste fino milimétrico do layout do TRCT/TQRCT/Homologação para saída mais próxima do modelo impresso, mantendo compatibilidade com DOMPDF e Snappy PDF.

## Ajustes aplicados
- refinamento de medidas em milímetros no template PDF
- redução e equalização de tipografia dos rótulos e valores
- ajuste de alturas de linha por bloco
- ajuste fino da faixa lateral vertical e do cabeçalho
- estabilização de fundos e impressão no Snappy
- aumento do DPI do DOMPDF para melhorar a nitidez da saída

## Arquivos impactados
- resources/views/rh/documentos/pdf/documento_rescisao_pdf.blade.php
- app/Modules/RH/Support/RescisaoPdfRenderer.php

## Banco de dados
Sem alteração de schema.
Sem migration nova.
