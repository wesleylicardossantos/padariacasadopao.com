<?php

namespace App\Modules\PDV\Services;

use App\Models\Cliente;
use App\Models\ConfigNota;
use App\Models\Produto;
use App\Models\Usuario;
use Illuminate\Http\Request;

class OfflineBootstrapService
{
    public function build(Request $request): array
    {
        $empresaId = (int) $request->get('empresa_id');
        $usuarioId = (int) $request->get('pdv_usuario_id');
        $usuario = Usuario::find($usuarioId);

        $produtos = Produto::where('empresa_id', $empresaId)
            ->when($request->filled('produtos_updated_after'), function ($query) use ($request) {
                $query->where('updated_at', '>', $request->get('produtos_updated_after'));
            })
            ->limit((int) ($request->get('limite_produtos', 5000)))
            ->get()
            ->map(function ($produto) {
                return [
                    'id' => $produto->id,
                    'nome' => $produto->nome,
                    'valor_venda' => (float) $produto->valor_venda,
                    'valor_compra' => (float) $produto->valor_compra,
                    'codigo_barras' => $produto->codBarras,
                    'referencia' => $produto->referencia,
                    'categoria' => optional($produto->categoria)->nome,
                    'estoque_atual' => method_exists($produto, 'estoqueAtual2') ? (float) $produto->estoqueAtual2() : 0,
                    'unidade_venda' => $produto->unidade_venda,
                    'permite_desconto_maximo' => (float) ($produto->limite_maximo_desconto ?? 0),
                    'updated_at' => optional($produto->updated_at)->toDateTimeString(),
                ];
            })
            ->values();

        $clientes = Cliente::where('empresa_id', $empresaId)
            ->when($request->filled('clientes_updated_after'), function ($query) use ($request) {
                $query->where('updated_at', '>', $request->get('clientes_updated_after'));
            })
            ->limit((int) ($request->get('limite_clientes', 3000)))
            ->get()
            ->map(function ($cliente) {
                return [
                    'id' => $cliente->id,
                    'nome' => $cliente->razao_social ?? $cliente->nome,
                    'fantasia' => $cliente->nome_fantasia ?? null,
                    'cpf_cnpj' => $cliente->cpf_cnpj ?? null,
                    'telefone' => $cliente->telefone ?? null,
                    'email' => $cliente->email ?? null,
                    'endereco' => trim(collect([
                        $cliente->rua ?? null,
                        $cliente->numero ?? null,
                        $cliente->bairro ?? null,
                    ])->filter()->implode(', ')),
                    'updated_at' => optional($cliente->updated_at)->toDateTimeString(),
                ];
            })
            ->values();

        $config = ConfigNota::where('empresa_id', $empresaId)->first();

        return [
            'empresa_id' => $empresaId,
            'operador' => [
                'id' => $usuario?->id,
                'nome' => $usuario?->nome,
                'login' => $usuario?->login,
            ],
            'config' => [
                'ambiente' => $config->ambiente ?? null,
                'razao_social' => $config->razao_social ?? null,
                'nome_fantasia' => $config->nome_fantasia ?? null,
                'serie_nfce' => $config->numero_serie_nfce ?? null,
                'ultimo_numero_nfce' => method_exists('App\\Models\\VendaCaixa', 'lastNFCe') ? \App\Models\VendaCaixa::lastNFCe($empresaId) : null,
                'casas_decimais' => $config->casas_decimais ?? 2,
                'emitir_nfce' => (bool) ($config !== null),
            ],
            'produtos' => $produtos,
            'clientes' => $clientes,
            'meta' => [
                'gerado_em' => now()->toDateTimeString(),
                'total_produtos' => $produtos->count(),
                'total_clientes' => $clientes->count(),
            ],
        ];
    }
}
