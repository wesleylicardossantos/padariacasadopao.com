<?php

namespace App\Http\Controllers\Cardapio;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use App\Models\Pedido;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function openTable(Request $request)
    {
        $mesaId = $request->mesa_id ?: $request->id;
        $mesa = $mesaId ? Mesa::find($mesaId) : null;

        return response()->json([
            'success' => (bool) $mesa,
            'mesa' => $mesa,
            'message' => $mesa ? 'Mesa localizada.' : 'Mesa não encontrada.',
        ], $mesa ? 200 : 404);
    }

    public function getPedido(Request $request)
    {
        $pedidoId = $request->pedido_id ?: $request->id;
        $pedido = $pedidoId ? Pedido::with('itens')->find($pedidoId) : null;

        return response()->json([
            'success' => (bool) $pedido,
            'pedido' => $pedido,
        ], $pedido ? 200 : 404);
    }

    public function save(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Fluxo Cardápio unificado criado para evitar erro fatal. Ajuste a integração do app se este endpoint for utilizado.',
            'payload' => $request->all(),
        ], 501);
    }

    public function mesas()
    {
        return response()->json(Mesa::query()->orderBy('nome')->get(), 200);
    }
}
