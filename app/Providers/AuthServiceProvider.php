<?php

namespace App\Providers;

use App\Models\RHPortalFuncionario;
use App\Models\RHPortalPerfil;
use App\Policies\RHPortalFuncionarioPolicy;
use App\Policies\RHPortalPerfilPolicy;
use App\Services\RH\RHAccessControlService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        RHPortalPerfil::class => RHPortalPerfilPolicy::class,
        RHPortalFuncionario::class => RHPortalFuncionarioPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function ($user, string $ability) {
            if ((int) ($user->adm ?? 0) === 1 && str_starts_with($ability, 'rh.')) {
                return true;
            }

            return null;
        });

        foreach (array_keys(RHAccessControlService::PERMISSIONS) as $permission) {
            Gate::define($permission, function ($user = null) use ($permission) {
                return app(RHAccessControlService::class)->userCan($permission, $user);
            });
        }

        foreach (array_keys(RHPortalPerfil::permissoesDisponiveis()) as $permission) {
            Gate::define('portal.' . $permission, function ($user = null) use ($permission) {
                $portalSession = session('funcionario_portal');
                if (!is_array($portalSession)) {
                    return false;
                }

                $acesso = RHPortalFuncionario::query()
                    ->with('perfil')
                    ->where('empresa_id', (int) ($portalSession['empresa_id'] ?? 0))
                    ->where('funcionario_id', (int) ($portalSession['funcionario_id'] ?? 0))
                    ->where('ativo', 1)
                    ->first();

                if (!$acesso) {
                    return in_array($permission, ['dashboard.visualizar', 'holerites.visualizar'], true);
                }

                return $acesso->hasPermission($permission);
            });
        }
    }
}
