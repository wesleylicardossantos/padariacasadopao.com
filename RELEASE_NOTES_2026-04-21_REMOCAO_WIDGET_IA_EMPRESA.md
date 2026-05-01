# RELEASE NOTES — 2026-04-21 — Remoção do widget flutuante IA da Empresa

## Objetivo
Remover o box flutuante global "IA da Empresa" que estava sobrepondo o sistema e integrar essa leitura dentro do Dashboard RH Executivo.

## Alterações aplicadas
- Removido include global `@include('rh.ia_widget')` de `resources/views/default/layout.blade.php`.
- Mantido o monitoramento dentro de `resources/views/rh/dashboard.blade.php`.
- Renomeado o card lateral de alertas para `IA da Empresa` no Dashboard RH Executivo.
- Adicionado atalho interno `#ia-empresa-painel` no bloco de atalhos do Dashboard RH Executivo.

## Resultado
- O widget não aparece mais flutuando em telas como Financeiro, Gráficos e demais módulos.
- A leitura automática continua disponível dentro do Dashboard RH Executivo, sem atrapalhar a visualização do sistema.
