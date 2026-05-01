<?php

namespace App\Services\RH;

use App\Models\RH\RHAclPapel;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RHAccessControlService
{
    public const PERMISSIONS = [
        'rh.dashboard.visualizar' => ['nome' => 'Visualizar Dashboard RH', 'descricao' => 'Acesso aos dashboards executivos e operacionais de RH.'],
        'rh.dashboard.executivo' => ['nome' => 'Visualizar Dashboard RH Executivo', 'descricao' => 'Acesso ao dashboard executivo com indicadores reais.'],
        'rh.dossie.visualizar' => ['nome' => 'Visualizar Dossiê', 'descricao' => 'Consulta do dossiê do funcionário.'],
        'rh.dossie.documentos.gerenciar' => ['nome' => 'Gerenciar documentos do dossiê', 'descricao' => 'Upload e organização de documentos do dossiê.'],
        'rh.dossie.documentos.excluir' => ['nome' => 'Excluir documentos do dossiê', 'descricao' => 'Exclusão lógica/física de documentos do dossiê.'],
        'rh.dossie.eventos.gerenciar' => ['nome' => 'Gerenciar eventos do dossiê', 'descricao' => 'Cadastro manual e manutenção de eventos do dossiê.'],
        'rh.dossie.automacao.executar' => ['nome' => 'Executar automação do dossiê', 'descricao' => 'Sincronização automática e hardening do dossiê.'],
        'rh.acl.gerenciar' => ['nome' => 'Gerenciar RBAC de RH', 'descricao' => 'Administração de papéis e permissões do RH.'],
        'enterprise.saas.executivo' => ['nome' => 'Visualizar Dashboard SaaS Executivo', 'descricao' => 'Acesso ao painel executivo consolidado do SaaS.'],
        'enterprise.saas.rbac' => ['nome' => 'Administrar governança enterprise', 'descricao' => 'Acesso à governança enterprise e administração de perfis.'],
    ];

    public function userCan(string $permission, ?Usuario $usuario = null, ?int $empresaId = null): bool
    {
        if (!$this->tablesReady()) {
            return true;
        }

        $usuario = $usuario ?: $this->resolveUsuario();
        if (!$usuario) {
            return false;
        }

        if ((int) ($usuario->adm ?? 0) === 1) {
            return true;
        }

        $empresaId = $empresaId ?: (int) ($usuario->empresa_id ?? data_get(session('user_logged'), 'empresa', 0));

        $query = DB::table('rh_acl_papel_usuarios as pu')
            ->join('rh_acl_papeis as p', 'p.id', '=', 'pu.papel_id')
            ->leftJoin('rh_acl_papel_permissoes as pp', 'pp.papel_id', '=', 'p.id')
            ->leftJoin('rh_acl_permissoes as per', 'per.id', '=', 'pp.permissao_id')
            ->where('pu.usuario_id', $usuario->id)
            ->where('pu.ativo', 1)
            ->where('p.ativo', 1)
            ->where(function ($q) use ($empresaId) {
                $q->whereNull('pu.empresa_id');
                if ($empresaId > 0) {
                    $q->orWhere('pu.empresa_id', $empresaId);
                }
            });

        $hasAnyRole = (clone $query)->exists();
        if (!$hasAnyRole) {
            return true;
        }

        return (clone $query)
            ->where(function ($q) use ($permission) {
                $q->where('p.is_admin', 1)
                    ->orWhere('per.codigo', $permission);
            })
            ->exists();
    }

    public function abortIfDenied(string $permission, ?Usuario $usuario = null, ?int $empresaId = null): void
    {
        abort_unless($this->userCan($permission, $usuario, $empresaId), 403, 'Você não possui permissão para acessar este recurso de RH.');
    }

    public function resolveUsuario(?Request $request = null): ?Usuario
    {
        $request = $request ?: request();

        $sessionId = (int) data_get(session('user_logged'), 'id', 0);
        if ($sessionId > 0) {
            return Usuario::find($sessionId);
        }

        $authUser = auth()->user();
        if ($authUser instanceof Usuario) {
            return $authUser;
        }

        return null;
    }

    public function tablesReady(): bool
    {
        return Schema::hasTable('rh_acl_papeis')
            && Schema::hasTable('rh_acl_permissoes')
            && Schema::hasTable('rh_acl_papel_permissoes')
            && Schema::hasTable('rh_acl_papel_usuarios');
    }

    public function syncDefaultSetup(): void
    {
        if (!$this->tablesReady()) {
            return;
        }

        foreach (self::PERMISSIONS as $codigo => $meta) {
            DB::table('rh_acl_permissoes')->updateOrInsert(
                ['codigo' => $codigo],
                [
                    'nome' => $meta['nome'],
                    'descricao' => $meta['descricao'],
                    'modulo' => 'rh',
                    'ativo' => 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $this->seedDefaultRoles();
        $this->seedDefaultAssignments();
    }

    protected function seedDefaultRoles(): void
    {
        $roles = [
            'rh-admin' => ['nome' => 'RH Admin', 'descricao' => 'Acesso administrativo completo do RH.', 'is_admin' => 1],
            'enterprise-admin' => ['nome' => 'Enterprise Admin', 'descricao' => 'Acesso executivo SaaS e governança corporativa.', 'is_admin' => 0],
            'rh-gestor' => ['nome' => 'RH Gestor', 'descricao' => 'Gestão operacional do RH e do dossiê.', 'is_admin' => 0],
            'rh-analista' => ['nome' => 'RH Analista', 'descricao' => 'Consulta executiva e manutenção do dossiê.', 'is_admin' => 0],
            'rh-consulta' => ['nome' => 'RH Consulta', 'descricao' => 'Somente leitura de indicadores e dossiê.', 'is_admin' => 0],
        ];

        foreach ($roles as $slug => $meta) {
            DB::table('rh_acl_papeis')->updateOrInsert(
                ['slug' => $slug],
                [
                    'empresa_id' => null,
                    'nome' => $meta['nome'],
                    'descricao' => $meta['descricao'],
                    'ativo' => 1,
                    'is_admin' => $meta['is_admin'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $permissionsByRole = [
            'rh-admin' => array_keys(self::PERMISSIONS),
            'enterprise-admin' => ['rh.dashboard.visualizar','rh.dashboard.executivo','rh.dossie.visualizar','rh.acl.gerenciar','enterprise.saas.executivo','enterprise.saas.rbac'],
            'rh-gestor' => [
                'rh.dashboard.visualizar', 'rh.dashboard.executivo', 'rh.dossie.visualizar',
                'rh.dossie.documentos.gerenciar', 'rh.dossie.eventos.gerenciar', 'rh.dossie.automacao.executar',
            ],
            'rh-analista' => [
                'rh.dashboard.visualizar', 'rh.dashboard.executivo', 'rh.dossie.visualizar',
                'rh.dossie.documentos.gerenciar', 'rh.dossie.eventos.gerenciar',
            ],
            'rh-consulta' => [
                'rh.dashboard.visualizar', 'rh.dashboard.executivo', 'rh.dossie.visualizar',
            ],
        ];

        $papelIds = DB::table('rh_acl_papeis')->pluck('id', 'slug');
        $permissionIds = DB::table('rh_acl_permissoes')->pluck('id', 'codigo');

        foreach ($permissionsByRole as $slug => $codes) {
            $papelId = (int) ($papelIds[$slug] ?? 0);
            if ($papelId <= 0) {
                continue;
            }

            foreach ($codes as $code) {
                $permissionId = (int) ($permissionIds[$code] ?? 0);
                if ($permissionId <= 0) {
                    continue;
                }

                DB::table('rh_acl_papel_permissoes')->updateOrInsert(
                    ['papel_id' => $papelId, 'permissao_id' => $permissionId],
                    ['updated_at' => now(), 'created_at' => now()]
                );
            }
        }
    }

    protected function seedDefaultAssignments(): void
    {
        if (!Schema::hasTable('usuarios')) {
            return;
        }

        $papelIds = DB::table('rh_acl_papeis')->pluck('id', 'slug');
        $adminRoleId = (int) ($papelIds['rh-admin'] ?? 0);
        $enterpriseRoleId = (int) ($papelIds['enterprise-admin'] ?? 0);
        $gestorRoleId = (int) ($papelIds['rh-gestor'] ?? 0);
        $consultaRoleId = (int) ($papelIds['rh-consulta'] ?? 0);

        $usuarios = DB::table('usuarios')->select('id', 'empresa_id', 'adm', 'permissao')->get();
        foreach ($usuarios as $usuario) {
            $papelId = $consultaRoleId;

            if ((int) ($usuario->adm ?? 0) === 1 && $adminRoleId > 0) {
                $papelId = $adminRoleId;
            } elseif ((int) ($usuario->adm ?? 0) === 1 && $enterpriseRoleId > 0) {
                $papelId = $enterpriseRoleId;
            } else {
                $json = json_decode((string) ($usuario->permissao ?? '[]'), true) ?: [];
                $hasRhAccess = collect($json)->contains(function ($uri) {
                    return is_string($uri) && (str_contains($uri, '/funcionarios') || str_contains($uri, '/apuracaoMensal') || str_contains($uri, '/eventoSalario') || str_contains($uri, '/rh'));
                });
                if ($hasRhAccess && $gestorRoleId > 0) {
                    $papelId = $gestorRoleId;
                }
            }

            if ($papelId > 0) {
                DB::table('rh_acl_papel_usuarios')->updateOrInsert(
                    ['papel_id' => $papelId, 'usuario_id' => $usuario->id],
                    [
                        'empresa_id' => $usuario->empresa_id,
                        'ativo' => 1,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}
