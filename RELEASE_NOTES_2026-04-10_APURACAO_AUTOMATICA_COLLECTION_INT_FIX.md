# Correção — Apuração automática / Collection could not be converted to int

## Ajustes aplicados
- normalização defensiva do resultado do motor de cálculo antes de persistir a apuração
- normalização defensiva de listas de eventos da competência antes de gravar `apuracao_salario_eventos` e `rh_folha_itens`
- blindagem adicional na integração financeira para resolver categoria e conta como `Model` único, evitando uso acidental de `Collection`
- reforço no serviço de eventos padrão para reutilizar apenas um `EventoSalario` válido por chave lógica
- normalização extra de `filial_id`, `categoria_id` e `conta_pagar_id`

## Objetivo
Eliminar a falha:
`Object of class Illuminate\\Database\\Eloquent\\Collection could not be converted to int`
na geração automática da apuração mensal.
