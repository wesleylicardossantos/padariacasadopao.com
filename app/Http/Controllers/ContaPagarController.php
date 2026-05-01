<?php

namespace App\Http\Controllers;

use App\Models\ContaPagar;
use App\Modules\Financeiro\Services\LegacyBridge\LegacyPayableBridgeService;
use Illuminate\Http\Request;

class ContaPagarController extends Controller
{
    public function __construct(protected LegacyPayableBridgeService $bridge)
    {
    }

    public function index(Request $request)
    {
        return view('conta_pagar.index', $this->bridge->indexPayload($request));
    }

    public function create(Request $request)
    {
        return view('conta_pagar.create', $this->bridge->createPayload($request));
    }

    public function edit(Request $request, $id)
    {
        $item = ContaPagar::findOrFail($id);
        if (! __valida_objeto($item)) {
            abort(403);
        }

        return view('conta_pagar.edit', $this->bridge->editPayload($request, $item));
    }

    public function store(Request $request)
    {
        $this->_validate($request);

        try {
            $this->bridge->create($request);
            session()->flash('flash_sucesso', 'Conta a pagar cadastrada!');
        } catch (\Throwable $e) {
            session()->flash('flash_erro', 'Algo deu errado: '.$e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('conta-pagar.index');
    }

    public function update(Request $request, $id)
    {
        $item = ContaPagar::findOrFail($id);
        $this->_validate($request);

        try {
            $this->bridge->update($request, $item);
            session()->flash('flash_sucesso', 'Conta a pagar atualizada!');
        } catch (\Throwable $e) {
            session()->flash('flash_erro', 'Algo deu errado: '.$e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('conta-pagar.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'fornecedor_id' => 'required',
            'referencia' => 'required',
            'valor_integral' => 'required',
            'data_vencimento' => 'required',
        ];
        $messages = [
            'referencia.required' => 'O campo referencia é obrigatório.',
            'fornecedor_id.required' => 'O campo fornecedor é obrigatório.',
            'valor_integral.required' => 'O campo valor é obrigatório.',
            'data_vencimento.required' => 'O campo vencimento é obrigatório.',
        ];
        $this->validate($request, $rules, $messages);
    }

    public function destroy($id)
    {
        $item = ContaPagar::findOrFail($id);
        if (! __valida_objeto($item)) {
            abort(403);
        }
        if ($item->status && ! is_adm()) {
            session()->flash('flash_erro', 'Somente administrador pode excluir conta paga!');
            return redirect()->route('conta-pagar.index');
        }

        try {
            $this->bridge->destroy(request(), $item);
            session()->flash('flash_sucesso', 'Conta removida!');
        } catch (\Throwable $e) {
            session()->flash('flash_erro', 'Algo deu errado: '.$e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('conta-pagar.index');
    }

    public function pay($id)
    {
        $item = ContaPagar::findOrFail($id);
        if (! __valida_objeto($item)) {
            abort(403);
        }

        return view('conta_pagar.pay', compact('item'));
    }

    public function payPut(Request $request, $id)
    {
        $item = ContaPagar::findOrFail($id);
        if (! __valida_objeto($item)) {
            abort(403);
        }

        try {
            $this->bridge->markAsPaid($request, $item);
            session()->flash('flash_sucesso', 'Conta a paga!');
        } catch (\Throwable $e) {
            session()->flash('flash_erro', 'Algo deu errado: '.$e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('conta-pagar.index');
    }
}
