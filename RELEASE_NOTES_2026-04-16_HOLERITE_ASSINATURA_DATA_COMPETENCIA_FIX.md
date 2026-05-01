# Release Notes — 2026-04-16

## Correções aplicadas

### Holerite / recibo em 2 vias
- corrigido corte visual na área de assinatura e data
- reduzidas alturas internas e espaçamentos do layout
- substituído o bloco final por estrutura mais estável para DomPDF
- ajustado o posicionamento das 2 vias dentro da página A4 com margem de 0,8 cm
- mantida a mesma estrutura para HTML e PDF

### Competência
- corrigida a busca da apuração para aceitar mês salvo como:
  - nome (`janeiro`, `fevereiro`, etc.)
  - número (`1`, `2`, etc.)
  - número com zero à esquerda (`01`, `02`, etc.)
- corrigido o filtro do portal para não falhar quando o mês vier em formatos diferentes
- mantida a exibição padronizada em `MM/AAAA`

## Arquivos alterados
- resources/views/rh/holerite/pdf.blade.php
- resources/views/rh/folha/recibo.blade.php
- app/Modules/RH/Services/RHFolhaModuleService.php
- app/Http/Controllers/RHPortalFuncionarioController.php
