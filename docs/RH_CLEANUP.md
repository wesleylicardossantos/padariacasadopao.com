# Limpeza do módulo RH

## Aplicado
- centralização da resolução de `empresa_id` em `App\Modules\RH\Support\RHContext`
- correção dos controllers críticos de RH para parar de depender apenas de `request()->empresa_id`
- padronização das rotas canônicas de RH:
  - `rh.funcionarios.index`
  - `rh.eventos.index`
  - `rh.funcionario_eventos.index`
  - `rh.apuracao_mensal.index`
- blindagem das telas de Funcionários, Eventos, Funcionários x Eventos e Apuração Mensal para multiempresa

## Objetivo
Remover a dependência frágil do legado em torno do tenant e estabilizar o RH sem regressão no menu e no layout.
