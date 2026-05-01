<?php

namespace App\Http\Controllers;

use App\Models\RH\RHAclPapel;
use App\Models\Usuario;
use App\Services\RH\RHAccessControlService;
use App\Services\RH\RHAdminAuditService;
use App\Support\Tenancy\InteractsWithTenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RHAclController extends Controller
{
    use InteractsWithTenantContext;

    public function __construct(private RHAccessControlService $acl, private RHAdminAuditService $audit)
    {
        $this->middleware('tenant.context');
        $this->middleware('rh.permission:rh.acl.gerenciar');
    }

    public function index(Request $request)
    {
        $empresaId = $this->tenantEmpresaId($request);
        $this->acl->syncDefaultSetup();

        $papeis = RHAclPapel::query()
            ->with('permissoes')
            ->where(function ($query) use ($empresaId) {
                $query->whereNull('empresa_id');
                if ($empresaId > 0) {
                    $query->orWhere('empresa_id', $empresaId);
                }
            })
            ->orderByDesc('is_admin')
            ->orderBy('nome')
            ->get();

        $usuarios = Usuario::query()
            ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
            ->orderBy('nome')
            ->limit(200)
            ->get();

        $assignments = DB::table('rh_acl_papel_usuarios')->get()->groupBy('usuario_id');

        return view('rh.acl.index', [
            'empresaId' => $empresaId,
            'papeis' => $papeis,
            'usuarios' => $usuarios,
            'assignments' => $assignments,
            'permissionsCatalog' => RHAccessControlService::PERMISSIONS,
        ]);
    }

    public function syncDefaults()
    {
        $this->acl->syncDefaultSetup();
        $this->audit->log('acl.sync_defaults', 'rh-acl');

        return back()->with('flash_sucesso', 'RBAC padrão sincronizado com sucesso.');
    }

    public function assign(Request $request)
    {
        $empresaId = $this->tenantEmpresaId($request);
        $data = $request->validate([
            'usuario_id' => ['required', 'integer'],
            'papel_id' => ['required', 'integer'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        DB::table('rh_acl_papel_usuarios')->updateOrInsert(
            ['papel_id' => (int) $data['papel_id'], 'usuario_id' => (int) $data['usuario_id']],
            [
                'empresa_id' => $empresaId > 0 ? $empresaId : null,
                'ativo' => $request->boolean('ativo', true),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $this->audit->log('acl.assign_role', 'rh-acl', [
            'usuario_id' => (int) $data['usuario_id'],
            'papel_id' => (int) $data['papel_id'],
            'ativo' => $request->boolean('ativo', true),
        ], 'usuario', (int) $data['usuario_id'], $empresaId > 0 ? $empresaId : null);

        return back()->with('flash_sucesso', 'Papel atribuído com sucesso.');
    }
}
