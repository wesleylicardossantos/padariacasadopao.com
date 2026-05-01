# Correção — Apuração mensal com eventos inativos

## Problema
Eventos legais desativados (INSS, IRRF, FGTS) continuavam impactando o valor final da apuração mensal, mesmo sem estarem ativos na lista de eventos da empresa.

## Causa
O motor da folha calculava descontos legais a partir das bases de incidência, independentemente da existência/atividade do evento correspondente.

## Correção aplicada
- INSS só é calculado quando o evento INSS está ativo.
- IRRF só é calculado quando o evento IRRF está ativo.
- FGTS só é calculado quando o evento FGTS está ativo.
- A base do IRRF só desconta INSS quando o evento INSS está ativo.
- Com eventos legais inativos e sem lançamentos manuais, o valor final fica igual ao salário base.

## Arquivo alterado
- app/Services/RHFolhaEngineService.php
