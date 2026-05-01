# Release Notes — 2026-04-16

## Implementado
- Centralização dos 2 recibos no centro da página A4, mantendo margem útil de 0,8 cm.
- Linha de corte central com texto `Corte aqui` e pontilhado lateral.
- Mesma estrutura visual aplicada na view HTML e no PDF DomPDF.
- Criação do partial compartilhado `resources/views/rh/holerite/_voucher_content.blade.php` para unificar o layout.

## Motor da folha
- Inclusão do campo `faixa_irrf` no resultado do motor.
- Suporte ampliado para eventos com cálculo por:
  - valor fixo
  - percentual sobre salário base
  - quantidade x valor
  - horas x valor
  - diária x valor
- Eventos com fórmula legal (`calc_inss`, `calc_irrf`, `calc_fgts`) continuam protegidos para cálculo automático pelo motor.
- Melhor identificação da origem dos eventos calculados (`funcionario_evento`, `funcionario_percentual`, `funcionario_referencia`).

## Arquivos alterados
- `app/Services/RHFolhaEngineService.php`
- `resources/views/rh/holerite/_voucher_content.blade.php`
- `resources/views/rh/holerite/pdf.blade.php`
- `resources/views/rh/folha/recibo.blade.php`
