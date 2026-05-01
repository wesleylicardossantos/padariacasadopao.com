<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Support\BrandingResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConfigController extends Controller
{
    public function index()
    {
        $empresa = Empresa::findOrFail((int) session('user_logged.empresa'));
        $branding = app(BrandingResolver::class)->resolve(request());

        return view('config_empresa.create', compact('empresa', 'branding'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'background_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $empresa = Empresa::findOrFail((int) session('user_logged.empresa'));

        try {
            if ($request->hasFile('logo')) {
                $empresa->branding_logo_path = $this->storeBrandingFile(
                    $request->file('logo'),
                    $empresa,
                    'logo',
                    $empresa->branding_logo_path
                );
            }

            if ($request->hasFile('background_image')) {
                $empresa->branding_background_path = $this->storeBrandingFile(
                    $request->file('background_image'),
                    $empresa,
                    'background',
                    $empresa->branding_background_path
                );
            }

            $empresa->save();

            session(['branding_empresa_id' => $empresa->id]);
            cookie()->queue(cookie('branding_empresa_id', (string) $empresa->id, 60 * 24 * 30));
            session()->flash('flash_sucesso', 'Identidade visual atualizada com sucesso!');
        } catch (\Throwable $e) {
            session()->flash('flash_erro', 'Algo deu errado ao atualizar a identidade visual: ' . $e->getMessage());
        }

        return redirect()->route('config.index');
    }

    protected function storeBrandingFile($file, Empresa $empresa, string $type, ?string $oldPath = null): string
    {
        $directory = 'empresas/' . $empresa->id . '/branding';
        $extension = strtolower($file->getClientOriginalExtension() ?: ($type === 'logo' ? 'png' : 'jpg'));
        $filename = $type . '_' . time() . '.' . $extension;

        if (!empty($oldPath) && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        return $file->storeAs($directory, $filename, 'public');
    }
}
