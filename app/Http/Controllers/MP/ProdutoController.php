<?php

namespace App\Http\Controllers\MP;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produto;

class ProdutoController extends Controller
{
    public function index(Request $request)
    {
        $produtos = Produto::query()
            ->where('empresa_id', $request->empresa_id)
            ->with(['categoria:id,nome'])
            ->select('id','empresa_id','categoria_id','nome','valor_venda','codBarras','updated_at','inativo')
            ->orderBy('id')
            ->limit(300)
            ->get();

        return response()->json($produtos, 200);
    }
}
