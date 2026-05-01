<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Camada de compatibilidade para rotas legadas de NFC-e.
 *
 * O projeto vinha com um arquivo inválido/autorreferente, o que quebrava o
 * boot do container HTTP/CLI. Esta classe mantém as rotas históricas vivas
 * delegando o que já existe em controllers consolidados.
 */
class NfceController extends FrontBoxController
{

    public function show($id)
    {
        $item = \App\Models\VendaCaixa::findOrFail($id);

        if (!__valida_objeto($item)) {
            abort(403);
        }

        $adm = null;
        if (valida_objeto($item)) {
            $adm = session('user_logged');
        }

        return view('frontBox.show', compact('adm', 'item'));
    }

    public function xmlTemp($id)
    {
        return app(NotaFiscalController::class)->xmlTemp($id);
    }

    public function imprimir($id)
    {
        return app(NotaFiscalController::class)->imprimir($id);
    }

    public function baixarXml($id)
    {
        return app(NfeController::class)->baixarXml($id);
    }

    public function estadoFiscal($id)
    {
        return app(NfeController::class)->estadoFiscal($id);
    }

    public function updateState(Request $request, $id)
    {
        return app(NfeController::class)->updateState($request, $id);
    }

    public function inutilizar(Request $request)
    {
        return app(NotaFiscalController::class)->inutilizar($request);
    }

    public function imprimirComprovanteAssessor($id)
    {
        return $this->imprimirNaoFiscal($id);
    }
}
