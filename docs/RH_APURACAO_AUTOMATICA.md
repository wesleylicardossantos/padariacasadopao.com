# Automação da Apuração Mensal RH

## O que foi implementado
- botão **Gerar automática** na listagem de apuração mensal
- geração por competência (mês/ano)
- opção de sobrescrever registros já existentes
- criação de linhas em `apuracao_mensals` e `apuracao_salario_eventos`
- cálculo com base em `funcionario_eventos` ativos
- suporte a valor fixo e percentual sobre salário

## Regras
- somente funcionários da empresa atual são considerados
- somente eventos ativos são usados
- sem eventos ativos, o funcionário é ignorado
- se a competência estiver fechada, a geração é bloqueada

## Observação
A apuração automática replica a lógica da tela manual: soma eventos com condição `soma` e subtrai eventos com condição `diminui`.
