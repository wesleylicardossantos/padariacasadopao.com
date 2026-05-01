# Reestruturação completa aplicada

## O que foi migrado
- Criação do módulo `app/Modules/RH`
- Serviço modular para folha
- Repositório modular de funcionários
- Rotas modulares RH carregadas após `routes/web.php` para assumir os endpoints do RH sem quebrar o legado
- Correção estrutural para o controller ausente `App\Http\Controllers\Cardapio\PedidoController`

## Endpoints RH assumidos pelo módulo
- `/rh`
- `/rh/folha`
- `/rh/financeiro`
- `/rh/recibo/{id}`
- `/rh/dre-inteligente`
- `/rh/dre-preditivo`
- `/rh/ia-decisao`
- `/rh/dre-folha`

## Estratégia aplicada
A migração foi feita em modo de compatibilidade: o módulo novo responde pelas rotas do RH, mas continua reutilizando as views e controllers legados onde isso reduz risco. Assim o sistema passa a ter uma base modular sem exigir remoção imediata do legado.

## Observação
O endpoint de cardápio que estava quebrando o `route:list` agora possui controller para impedir falha fatal do sistema. O método `save` foi deixado em modo de resposta controlada (`501`) porque não havia implementação original no projeto enviado.
