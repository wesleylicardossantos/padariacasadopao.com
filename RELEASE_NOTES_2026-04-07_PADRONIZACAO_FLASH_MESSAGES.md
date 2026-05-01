# Padronização global de mensagens flash - 2026-04-07

## Entrega
- criado componente global `resources/views/components/flash-message.blade.php`
- criada view de compatibilidade `resources/views/default/flash-message.blade.php`
- padronização aplicada no layout principal `resources/views/default/layout.blade.php`
- padronização aplicada nos layouts do portal RH externo
- módulo `Controle de Salgados` atualizado para usar componente Blade

## Padrões suportados
- `success`
- `error`
- `warning`
- `info`
- `flash_sucesso`
- `flash_erro`
- `status`
- erros de validação `$errors`

## Benefício técnico
- elimina erro de view inexistente
- mantém compatibilidade com telas legadas
- reduz duplicação de alerts nas views
- estabelece padrão reutilizável para módulos futuros
