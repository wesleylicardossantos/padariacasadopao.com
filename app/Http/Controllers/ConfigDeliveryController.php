<?php

namespace App\Http\Controllers;

use App\Models\CidadeDelivery;
use App\Models\DeliveryConfig;
use App\Models\DeliveryConfigGaleria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ConfigDeliveryController extends Controller
{
    private function empresaId(): ?int
    {
        return request()->empresa_id
            ?? data_get(session('user_logged'), 'empresa');
    }

    private function getConfig(): DeliveryConfig
    {
        $empresaId = $this->empresaId();

        return DeliveryConfig::firstOrCreate(
            ['empresa_id' => $empresaId],
            [
                'nome' => data_get(session('user_logged'), 'nome_empresa', 'Loja Delivery'),
                'descricao' => 'Configuração inicial do delivery',
                'api_token' => Str::random(25),
                'status' => 1,
            ]
        );
    }

    public function index(): View
    {
        $item = $this->getConfig()->loadMissing('cidade', 'galeria');
        $cidades = CidadeDelivery::orderBy('nome')->get();

        if ($item->tipos_pagamento && is_string($item->tipos_pagamento)) {
            $item->tipos_pagamento = json_decode($item->tipos_pagamento, true) ?: [];
        }

        return view('config_delivery.create', compact('item', 'cidades'));
    }

    public function store(Request $request): RedirectResponse
    {
        $item = $this->getConfig();

        $data = $request->except(['image', 'tipos_pagamento', '_token']);
        $data['empresa_id'] = $this->empresaId();
        $data['cidade_id'] = $request->input('cidade') ?: $request->input('cidade_id');
        $data['tipos_pagamento'] = json_encode(array_values($request->input('tipos_pagamento', [])));
        $data['api_token'] = $request->input('api_token') ?: ($item->api_token ?: Str::random(25));

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = preg_replace('/[^A-Za-z0-9_\.-]/', '_', uniqid('logo_', true) . '.' . $file->getClientOriginalExtension());
            $dir = public_path('delivery/logos');
            File::ensureDirectoryExists($dir);
            $file->move($dir, $filename);
            $data['logo'] = $filename;
        }

        $item->fill($data)->save();

        return redirect()->route('configDelivery.index')
            ->with('flash_sucesso', 'Configuração do delivery salva com sucesso!');
    }

    public function galeria(): View
    {
        $item = $this->getConfig()->loadMissing('empresa', 'galeria');
        return view('config_delivery.galery', compact('item'));
    }

    public function storeImagem(Request $request): RedirectResponse
    {
        $request->validate([
            'config_id' => 'required|integer',
            'image' => 'required|image',
        ]);

        $config = DeliveryConfig::findOrFail($request->input('config_id'));
        $file = $request->file('image');
        $filename = preg_replace('/[^A-Za-z0-9_\.-]/', '_', uniqid('gallery_', true) . '.' . $file->getClientOriginalExtension());
        $dir = public_path('uploads/lojaDelivery');
        File::ensureDirectoryExists($dir);
        $file->move($dir, $filename);

        DeliveryConfigGaleria::create([
            'config_id' => $config->id,
            'imagem' => $filename,
        ]);

        return redirect()->route('configDelivery.galeria')
            ->with('flash_sucesso', 'Imagem adicionada à galeria.');
    }

    public function deleteImagem(int $id): RedirectResponse
    {
        $item = DeliveryConfigGaleria::findOrFail($id);
        $path = public_path('uploads/lojaDelivery/' . $item->imagem);
        if (is_file($path)) {
            @unlink($path);
        }
        $item->delete();

        return redirect()->route('configDelivery.galeria')
            ->with('flash_sucesso', 'Imagem removida com sucesso.');
    }

    public function saveCoords(Request $request): RedirectResponse
    {
        $item = $this->getConfig();
        $item->latitude = $request->input('latitude', $request->input('lat', $item->latitude));
        $item->longitude = $request->input('longitude', $request->input('lng', $item->longitude));
        $item->save();

        return redirect()->route('configDelivery.index')
            ->with('flash_sucesso', 'Coordenadas salvas com sucesso.');
    }
}
