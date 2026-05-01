<?php

namespace Tests\Unit\RH;

use App\Models\RHPortalFuncionario;
use App\Models\RHPortalPerfil;
use App\Modules\RH\Application\Queries\PortalAccessibleReportsQuery;
use PHPUnit\Framework\TestCase;

class PortalAccessibleReportsQueryTest extends TestCase
{
    public function test_returns_only_reports_allowed_by_effective_permissions(): void
    {
        $perfil = new RHPortalPerfil([
            'permissoes' => ['dashboard.visualizar', 'holerites.visualizar', 'produtos.visualizar'],
        ]);

        $acesso = new RHPortalFuncionario([
            'permissoes_extras' => ['documentos.visualizar'],
            'ativo' => true,
        ]);
        $acesso->setRelation('perfil', $perfil);

        $reports = (new PortalAccessibleReportsQuery())->execute($acesso);

        $this->assertSame(['dashboard', 'holerites', 'produtos', 'documentos'], $reports);
    }
}
