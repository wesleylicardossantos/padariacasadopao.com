<?php

namespace App\Http\Controllers;

use App\Models\ClienteEcommerce;
use App\Models\EnderecoEcommerce;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EnderecosEcommerceController extends Controller
{
    public function index($id)
    {   
        $cliente = ClienteEcommerce::findOrFail($id);
        return view('enderecos_ecommerce.index', compact('cliente'));
    }

    public function edit($id)
    {
        $item = EnderecoEcommerce::findOrFail($id);
        return view('enderecos_ecommerce.edit', compact('item'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $item = EnderecoEcommerce::findOrFail($id);
        $item->fill($request->only([
            'rua', 'numero', 'bairro', 'cep', 'cidade', 'uf', 'complemento',
        ]));

        if ($request->filled('cidade_id') && ! $request->filled('cidade')) {
            $item->cidade = (string) $request->input('cidade_id');
        }

        $item->save();

        return redirect()->route('enderecosEcommerce.index', [$item->cliente_id])
            ->with('flash_sucesso', 'Endereço atualizado com sucesso!');
    }

}

