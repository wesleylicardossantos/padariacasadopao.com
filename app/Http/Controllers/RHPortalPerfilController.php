<?php

namespace App\Http\Controllers;

use App\Models\RHPortalPerfil;
use App\Support\SchemaSafe;
use App\Support\Tenancy\InteractsWithTenantContext;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class RHPortalPerfilController extends Controller
{
    use InteractsWithTenantContext;

    public function __construct()
    {
        $this->middleware('tenant.context');
    }
    public function index(Request $request)
    {
        $this->authorize('viewAny', RHPortalPerfil::class);

        if (!SchemaSafe::hasTable('rh_portal_perfis')) {
            return view('rh.portal_perfis.index', [
                'perfis' => new LengthAwarePaginator([], 0, 20),
                'permissoesDisponiveis' => RHPortalPerfil::permissoesDisponiveis(),
                'semTabela' => true,
            ]);
        }

        $empresaId = $this->tenantEmpresaId($request);

        $perfis = $this->scopedPerfis($empresaId)
            ->orderBy('nome')
            ->paginate(20);

        return view('rh.portal_perfis.index', [
            'perfis' => $perfis,
            'permissoesDisponiveis' => RHPortalPerfil::permissoesDisponiveis(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', RHPortalPerfil::class);

        abort_unless(SchemaSafe::hasTable('rh_portal_perfis'), 404, 'Tabela rh_portal_perfis não encontrada.');

        return view('rh.portal_perfis.form', [
            'item' => new RHPortalPerfil(['ativo' => true, 'permissoes' => ['dashboard.visualizar', 'holerites.visualizar']]),
            'permissoesDisponiveis' => RHPortalPerfil::permissoesDisponiveis(),
            'action' => route('rh.portal_perfis.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', RHPortalPerfil::class);
        abort_unless(SchemaSafe::hasTable('rh_portal_perfis'), 404, 'Tabela rh_portal_perfis não encontrada.');

        $empresaId = $this->tenantEmpresaId($request);
        $data = $this->validated($request);
        $data['empresa_id'] = $empresaId;
        $data['slug'] = $this->makeSlug($data['nome'], $empresaId);

        $item = new RHPortalPerfil();
        SchemaSafe::fillAndSave($item, SchemaSafe::filter('rh_portal_perfis', $data));

        return redirect()->route('rh.portal_perfis.index')->with('flash_sucesso', 'Perfil do portal criado com sucesso.');
    }

    public function edit(Request $request, $id)
    {
        $item = $this->findPerfil($request, $id);
        $this->authorize('update', $item);

        return view('rh.portal_perfis.form', [
            'item' => $item,
            'permissoesDisponiveis' => RHPortalPerfil::permissoesDisponiveis(),
            'action' => route('rh.portal_perfis.update', $item->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, $id)
    {
        $item = $this->findPerfil($request, $id);
        $this->authorize('update', $item);
        $data = $this->validated($request);
        $data['slug'] = $this->makeSlug($data['nome'], (int) ($item->empresa_id ?? 0), $item->id);
        SchemaSafe::fillAndSave($item, SchemaSafe::filter('rh_portal_perfis', $data));

        return redirect()->route('rh.portal_perfis.index')->with('flash_sucesso', 'Perfil do portal atualizado com sucesso.');
    }

    public function destroy(Request $request, $id)
    {
        $item = $this->findPerfil($request, $id);
        $this->authorize('delete', $item);

        if ($item->portalFuncionarios()->exists()) {
            return back()->with('flash_erro', 'Este perfil está vinculado a funcionários e não pode ser excluído.');
        }

        $item->delete();

        return redirect()->route('rh.portal_perfis.index')->with('flash_sucesso', 'Perfil do portal removido com sucesso.');
    }

    private function validated(Request $request): array
    {
        $request->validate([
            'nome' => 'required|string|max:80',
            'descricao' => 'nullable|string|max:255',
            'ativo' => 'nullable',
            'permissoes' => 'array|min:1',
            'permissoes.*' => 'string',
        ], [
            'permissoes.min' => 'Selecione pelo menos uma permissão para o perfil.',
        ]);

        $permissoesValidas = array_keys(RHPortalPerfil::permissoesDisponiveis());
        $permissoes = array_values(array_intersect($request->input('permissoes', []), $permissoesValidas));
        if (empty($permissoes)) {
            $permissoes = ['dashboard.visualizar', 'holerites.visualizar'];
        }

        return [
            'nome' => (string) $request->nome,
            'descricao' => (string) ($request->descricao ?? ''),
            'ativo' => $request->boolean('ativo', true),
            'permissoes' => $permissoes,
        ];
    }

    private function findPerfil(Request $request, int $id): RHPortalPerfil
    {
        abort_unless(SchemaSafe::hasTable('rh_portal_perfis'), 404, 'Tabela rh_portal_perfis não encontrada.');

        $empresaId = $this->tenantEmpresaId($request);

        return $this->scopedPerfis($empresaId)
            ->where('id', $id)
            ->firstOrFail();
    }

    private function makeSlug(string $nome, int $empresaId, ?int $ignoreId = null): string
    {
        $base = Str::slug($nome) ?: 'perfil-portal';
        $slug = $base;
        $i = 2;

        while (RHPortalPerfil::query()
            ->where('slug', $slug)
            ->when(SchemaSafe::hasColumn('rh_portal_perfis', 'empresa_id') && $empresaId > 0, fn ($q) => $q->where(function ($s) use ($empresaId) {
                $s->whereNull('empresa_id')->orWhere('empresa_id', $empresaId);
            }))
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private function scopedPerfis(int $empresaId)
    {
        return SchemaSafe::applyEmpresaScope(RHPortalPerfil::query(), $empresaId, 'rh_portal_perfis');
    }
}
