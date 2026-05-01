# Release Notes — Precificação: validação automática de margem e travas de segurança

## Entregue
- menu `Precificação` após `Controle`
- `Painel de Precificação`
- `Sugestões de Preço`
- `Dashboard Executivo`
- validação automática de margem
- cálculo de CMV
- cálculo de preço mínimo operacional
- travas de segurança para publicação
- exigência de justificativa para aprovar itens em alerta
- blindagem contra schema parcial com `PrecificacaoSchema`

## Regras aplicadas
- bloqueia custo inválido ou zerado
- bloqueia preço sugerido inválido
- bloqueia produto sem ficha técnica
- bloqueia produto sem vínculo com o legado
- bloqueia produto com insumos sem custo
- bloqueia preço abaixo do preço mínimo operacional
- alerta quando a margem está abaixo da meta
- alerta quando o CMV está acima do máximo desejado

## Arquivos principais
- `app/Support/PrecificacaoSchema.php`
- `app/Services/PrecificacaoValidacaoService.php`
- `app/Services/PrecificacaoAutoPricingService.php`
- `app/Services/PrecificacaoDashboardExecutivoService.php`
- `app/Http/Controllers/PrecificacaoController.php`
- `app/Http/Controllers/PrecificacaoSugestaoController.php`
- `app/Http/Controllers/PrecificacaoDashboardExecutivoController.php`
- `resources/views/precificacao/index.blade.php`
- `resources/views/precificacao/sugestoes/index.blade.php`
- `resources/views/precificacao/dashboard_executivo/index.blade.php`
