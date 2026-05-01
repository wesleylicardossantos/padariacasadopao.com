<?php

namespace App\Http\Controllers;

use App\Models\ContaReceber;
use App\Modules\Financeiro\Services\LegacyBridge\LegacyReceivableBridgeService;
use Illuminate\Http\Request;

class ContaReceberController extends Controller
{
    public function __construct(protected LegacyReceivableBridgeService $bridge)
    {
    }

    public function index(Request $request)
    {
        return view('conta_receber.index', $this->bridge->indexPayload($request));
    }

    public function create(Request $request)
    {
        return view('conta_receber.create', $this->bridge->createPayload($request));
    }

    public function edit(Request $request, $id)
    {
        $item = ContaReceber::findOrFail($id);
        if (! __valida_objeto($item)) {
            abort(403);
        }

        return view('conta_receber.edit', $this->bridge->editPayload($request, $item));
    }

    public function store(Request $request)
    {
        $this->_validate($request);

        try {
            $this->bridge->create($request);
            session()->flash('flash_sucesso', 'Conta a receber cadastrada!');
        } catch (\Throwable $e) {
            session()->flash('flash_erro', 'Algo deu errado: '.$e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('conta-receber.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'cliente_id' => 'required',
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

    public function update(Request $request, $id)
    {
        $item = ContaReceber::findOrFail($id);
        $this->_validate($request);

        try {
            $this->bridge->update($request, $item);
            session()->flash('flash_sucesso', 'Conta a receber atualizada!');
        } catch (\Throwable $e) {
            session()->flash('flash_erro', 'Algo deu errado: '.$e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('conta-receber.index');
    }

    public function destroy($id)
    {
        $item = ContaReceber::findOrFail($id);
        if (! __valida_objeto($item)) {
            abort(403);
        }

        try {
            $this->bridge->destroy(request(), $item);
            session()->flash('flash_sucesso', 'Conta removida!');
        } catch (\Throwable $e) {
            session()->flash('flash_erro', 'Algo deu errado: '.$e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('conta-receber.index');
    }

    public function pay($id)
    {
        $item = ContaReceber::findOrFail($id);
        if (! __valida_objeto($item)) {
            abort(403);
        }

        return view('conta_receber.pay', compact('item'));
    }

    public function payPut(Request $request, $id)
    {
        $item = ContaReceber::findOrFail($id);
        if (! __valida_objeto($item)) {
            abort(403);
        }

        try {
            $this->bridge->markAsPaid($request, $item);
            session()->flash('flash_sucesso', 'Conta a recebida!');
        } catch (\Throwable $e) {
            session()->flash('flash_erro', 'Algo deu errado: '.$e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('conta-receber.index');
    }
}
