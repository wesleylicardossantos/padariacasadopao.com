<?php

namespace App\Modules\RH\Application\Actions;

use App\Models\RHPortalFuncionario;
use App\Models\RHPortalPerfil;
use App\Modules\RH\Application\DTOs\ConfigurePortalAccessData;
use App\Services\RHPortalAcessoService;
use Illuminate\Support\Facades\Schema;

final class ConfigurePortalAccessAction
{
    public function __construct(private RHPortalAcessoService $portalAcessoService)
    {
    }

    public function execute(ConfigurePortalAccessData $data): RHPortalFuncionario
    {
        $acesso = $this->portalAcessoService->criarOuObter($data->funcionario);
        $acesso->empresa_id = (int) $data->funcionario->empresa_id;
        $acesso->ativo = $data->ativo;

        if (Schema::hasColumn($acesso->getTable(), 'perfil_id')) {
            $acesso->perfil_id = $this->resolvePerfilId($data);
        }

        if (Schema::hasColumn($acesso->getTable(), 'permissoes_extras')) {
            $acesso->permissoes_extras = $data->permissoesExtras();
        }

        if (Schema::hasColumn($acesso->getTable(), 'pode_ver_relatorio_produtos')) {
            $acesso->pode_ver_relatorio_produtos = $data->podeVerRelatorioProdutos || $data->podeVerRelatorioProdutosExtra;
        }

        $acesso->save();

        return $acesso->fresh(['perfil']);
    }

    private function resolvePerfilId(ConfigurePortalAccessData $data): ?int
    {
        if (!$data->perfilId || !Schema::hasTable('rh_portal_perfis')) {
            return null;
        }

        $perfil = RHPortalPerfil::query()
            ->where('id', $data->perfilId)
            ->where(function ($query) use ($data) {
                $query->whereNull('empresa_id')->orWhere('empresa_id', $data->funcionario->empresa_id);
            })
            ->where('ativo', 1)
            ->first();

        return $perfil?->id;
    }
}
