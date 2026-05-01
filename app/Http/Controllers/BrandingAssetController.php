<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class BrandingAssetController extends Controller
{
    public function show($empresaId, $type)
    {
        $empresa = Empresa::findOrFail((int) $empresaId);

        $path = $type === 'background'
            ? $empresa->branding_background_path
            : $empresa->branding_logo_path;

        if (!empty($path) && Storage::disk('public')->exists($path)) {
            $mime = Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';
            return Response::make(Storage::disk('public')->get($path), 200, [
                'Content-Type' => $mime,
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        $fallback = $type === 'background'
            ? public_path('assets/images/img123.jpg')
            : public_path('logos/default.png');

        if (is_file($fallback)) {
            return response()->file($fallback, [
                'Cache-Control' => 'public, max-age=3600',
            ]);
        }

        abort(404);
    }
}
