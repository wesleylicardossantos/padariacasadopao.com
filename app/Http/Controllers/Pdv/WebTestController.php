<?php

namespace App\Http\Controllers\Pdv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebTestController extends Controller
{
    public function index(Request $request)
    {
        $payloadExemplo = [
            'uuid_local' => 'TESTE-' . now()->format('YmdHis'),
            'cliente_id' => null,
            'cliente_nome' => 'Consumidor Final',
            'cpf_cnpj' => '',
            'pagamento_principal' => '01',
            'valor_recebido' => 10,
            'troco' => 0,
            'desconto' => 0,
            'acrescimo' => 0,
            'total' => 10,
            'itens' => [
                [
                    'produto_id' => 1,
                    'quantidade' => 1,
                    'valor' => 10,
                    'observacao' => 'Item de teste',
                ],
            ],
            'pagamentos' => [
                [
                    'forma_pagamento' => '01',
                    'valor' => 10,
                ],
            ],
        ];

        return view('pdv.teste_web', [
            'apiBase' => url('/api/pdv'),
            'payloadExemploJson' => json_encode($payloadExemplo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }
}
