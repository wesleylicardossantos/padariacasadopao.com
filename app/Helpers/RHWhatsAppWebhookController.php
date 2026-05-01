<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RHWhatsAppWebhookController extends Controller
{
    public function receber(Request $request)
    {
        $mensagem = strtolower((string) $request->input('mensagem', ''));

        if (str_contains($mensagem, 'empresa')) {
            return response()->json(['resposta' => 'Empresa sob monitoramento.']);
        }

        if (str_contains($mensagem, 'contratar')) {
            return response()->json(['resposta' => 'Analise a margem antes de contratar.']);
        }

        return response()->json(['resposta' => 'Pergunta não reconhecida.']);
    }
}
