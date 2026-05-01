<?php

namespace App\Http\Controllers\Pdv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produto;
use App\Support\Tenancy\InteractsWithTenantContext;

class ProdutoController extends Controller
{
    use InteractsWithTenantContext;

    public function __construct()
    {
        $this->middleware('tenant.context');
    }

    private function queryBase(Request $request)
    {
        return Produto::query()
            ->with([
                'categoria:id,nome',
                'estoque:produto_id,quantidade',
            ])
            ->where('empresa_id', $this->tenantEmpresaId($request));
    }

    private function normalizaRetorno($produtos)
    {
        foreach ($produtos as $p) {
            $p->estoque_atual = optional($p->estoque)->quantidade ?? 0;
            $p->categoria_nome = optional($p->categoria)->nome;
            unset($p->categoria, $p->estoque);
        }

        return $produtos;
    }

    public function index(Request $request)
    {
        $produtos = $this->queryBase($request)
            ->when($request->filled('update_date'), function ($query) use ($request) {
                return $query->where('updated_at', '>', $request->update_date);
            })
            ->get();

        return response()->json($this->normalizaRetorno($produtos), 200);
    }

    public function count(Request $request)
    {
        $count = Produto::where('empresa_id', $this->tenantEmpresaId($request))->count();

        return response()->json($count, 200);
    }

    public function limit(Request $request)
    {
        $produtos = $this->queryBase($request)
            ->when($request->filled('id'), function ($query) use ($request) {
                return $query->where('id', '>', $request->id);
            })
            ->when($request->filled('update_date'), function ($query) use ($request) {
                return $query->where('updated_at', '>', $request->update_date);
            })
            ->orderBy('id')
            ->limit(300)
            ->get();

        return response()->json($this->normalizaRetorno($produtos), 200);
    }
}
