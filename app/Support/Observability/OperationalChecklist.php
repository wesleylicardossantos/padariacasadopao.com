<?php

namespace App\Support\Observability;

class OperationalChecklist
{
    public static function deploy(): array
    {
        return [
            'before' => [
                'Gerar backup validado de arquivos, banco e .env.',
                'Executar project:inventory --write e schema:drift-report --write.',
                'Executar system:healthcheck --write e revisar falhas antes do deploy.',
                'Validar espaço em disco, permissões de storage e bootstrap/cache.',
                'Confirmar janela de baixo uso para migrations não destrutivas.',
            ],
            'during' => [
                'Subir código preservando public/index.php e .htaccess da publicação.',
                'Executar php artisan migrate --force.',
                'Executar php artisan config:clear, route:clear e cache:clear.',
                'Executar php artisan config:cache após validação.',
                'Executar smoke tests críticos: login, financeiro, PDV offline e estoque.',
            ],
            'after' => [
                'Executar system:healthcheck --write e comparar com baseline anterior.',
                'Validar criação e liquidação financeira com auditoria registrada.',
                'Validar sync offline do PDV com cenários sincronizado, duplicado e conflito.',
                'Executar stock:reconcile {empresa_id} e revisar divergências.',
                'Registrar incidentes, rollback efetuado ou aceite operacional do deploy.',
            ],
        ];
    }

    public static function smoke(): array
    {
        return [
            'auth' => [
                'Login com usuário de empresa válida.',
                'Navegação inicial sem perda de contexto do tenant.',
            ],
            'financeiro' => [
                'Criar conta a receber.',
                'Liquidar conta a receber parcialmente e totalmente.',
                'Criar conta a pagar e validar auditoria.',
            ],
            'pdv' => [
                'Sincronizar venda offline inédita.',
                'Reenviar payload idêntico e validar status duplicado.',
                'Reenviar payload alterado e validar conflito_payload.',
            ],
            'estoque' => [
                'Registrar movimento no ledger via fluxo modernizado.',
                'Executar stock:reconcile e inspecionar divergências.',
            ],
        ];
    }
}
