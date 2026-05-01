# Kit avançado padaria - 2026-04-17

## Implementado
- ação para implantar o kit avançado de padaria pelo painel de precificação
- criação/atualização automática de insumos padrão
- criação/atualização automática de fichas técnicas base e finais
- criação/atualização automática de produtos de precificação
- criação/atualização automática de regras de margem, CMV e arredondamento
- suporte a sub-receitas no cálculo de custo automático

## Entradas do kit
### Bases
- MASSA BASE PÃO
- MASSA BASE SALGADOS
- RECHEIO FRANGO PADRÃO
- RECHEIO BRIGADEIRO PADRÃO

### Produtos finais
- BROA DE MILHO
- PÃO FRANCÊS
- COXINHA PROFISSIONAL
- BOLO DE CHOCOLATE FATIA
- BISCOITO CASEIRO

## Arquivos principais
- app/Services/PrecificacaoPadariaKitService.php
- app/Services/PrecificacaoAutoPricingService.php
- app/Http/Controllers/PrecificacaoController.php
- app/Models/PrecificacaoReceitaItem.php
- resources/views/precificacao/index.blade.php
- resources/views/precificacao/insumos/index.blade.php
- resources/views/precificacao/receitas/index.blade.php
- routes/web.php
