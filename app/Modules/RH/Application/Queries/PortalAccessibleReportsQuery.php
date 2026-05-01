<?php

namespace App\Modules\RH\Application\Queries;

use App\Models\RHPortalFuncionario;

final class PortalAccessibleReportsQuery
{
    public function execute(?RHPortalFuncionario $acesso): array
    {
        if (!$acesso) {
            return [];
        }

        $reports = [
            'dashboard' => $acesso->hasPermission('dashboard.visualizar'),
            'holerites' => $acesso->hasPermission('holerites.visualizar'),
            'produtos' => $acesso->hasPermission('produtos.visualizar'),
            'documentos' => $acesso->hasPermission('documentos.visualizar'),
            'comissoes' => $acesso->hasPermission('comissoes.visualizar'),
            'pedidos' => $acesso->hasPermission('pedidos.visualizar'),
            'dossie' => $acesso->hasPermission('dossie.visualizar'),
        ];

        return array_keys(array_filter($reports));
    }
}
