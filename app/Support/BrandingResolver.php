<?php

namespace App\Support;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandingResolver
{
    public function resolve(?Request $request = null): array
    {
        $empresa = $this->resolveEmpresa($request);

        return [
            'empresa' => $empresa,
            'empresa_id' => $empresa?->id,
            'nome' => $empresa?->nome_fantasia ?: config('app.name'),
            'logo_url' => $empresa ? $empresa->branding_logo_url : asset('logos/default.png'),
            'background_url' => $empresa ? $empresa->branding_background_url : asset('assets/images/img123.jpg'),
            'has_custom_logo' => !empty($empresa?->branding_logo_path),
            'has_custom_background' => !empty($empresa?->branding_background_path),
        ];
    }


    public function logoDataUri(?Empresa $empresa = null, ?string $legacyLogo = null, string $legacyDir = 'uploads/configEmitente', ?string $fallbackPublic = null): string
    {
        $empresa = $empresa ?: $this->resolveEmpresa();
        $fallbackPublic = $fallbackPublic ?: 'logos/default.png';

        $candidates = [];

        if (!empty($empresa?->branding_logo_path)) {
            $candidates[] = storage_path('app/public/' . ltrim((string) $empresa->branding_logo_path, '/'));
            $candidates[] = public_path('storage/' . ltrim((string) $empresa->branding_logo_path, '/'));
        }

        if (!empty($legacyLogo)) {
            $legacyLogo = ltrim((string) $legacyLogo, '/');
            $candidates[] = public_path(trim($legacyDir, '/') . '/' . $legacyLogo);
            $candidates[] = public_path('logos/' . $legacyLogo);
        }

        if (!empty($empresa?->logo)) {
            $candidates[] = public_path('logos/' . ltrim((string) $empresa->logo, '/'));
        }

        $candidates[] = public_path('logo.png');
        $candidates[] = public_path(ltrim($fallbackPublic, '/'));
        $candidates[] = public_path('imgs/slym.png');

        foreach ($candidates as $path) {
            if (!empty($path) && is_file($path) && is_readable($path)) {
                $contents = @file_get_contents($path);
                if ($contents === false) {
                    continue;
                }

                $mime = @mime_content_type($path) ?: 'image/png';
                if (!Str::startsWith($mime, 'image/')) {
                    $mime = 'image/png';
                }

                return 'data:' . $mime . ';base64,' . base64_encode($contents);
            }
        }

        return '';
    }

    public function empresaNome(?Empresa $empresa = null): string
    {
        $empresa = $empresa ?: $this->resolveEmpresa();

        return (string) ($empresa?->nome_fantasia ?: $empresa?->razao_social ?: env('EMPRESA_NOME', config('app.name')));
    }


    public function empresaDocumento(?Empresa $empresa = null): string
    {
        $empresa = $empresa ?: $this->resolveEmpresa();

        return (string) ($empresa?->cpf_cnpj ?: env('EMPRESA_CNPJ', ''));
    }

    public function empresaEnderecoLinha(?Empresa $empresa = null): string
    {
        $empresa = $empresa ?: $this->resolveEmpresa();

        return (string) collect([
            $empresa?->rua,
            $empresa?->numero,
            $empresa?->bairro,
            $empresa?->cidade,
            $empresa?->uf,
        ])->filter()->implode(' - ');
    }

    public function empresaContatoLinha(?Empresa $empresa = null): string
    {
        $empresa = $empresa ?: $this->resolveEmpresa();

        return (string) collect([
            $empresa?->telefone,
            $empresa?->email,
        ])->filter()->implode(' | ');
    }

    public function resolveEmpresa(?Request $request = null): ?Empresa
    {
        $request = $request ?: request();

        $sessionUser = session('user_logged');
        if (!empty($sessionUser['empresa'])) {
            return Empresa::find($sessionUser['empresa']);
        }

        $brandingEmpresaId = session('branding_empresa_id') ?: $request->cookie('branding_empresa_id');
        if (!empty($brandingEmpresaId)) {
            $empresa = Empresa::find($brandingEmpresaId);
            if ($empresa) {
                return $empresa;
            }
        }

        $empresaParam = $request->query('empresa') ?: $request->input('empresa');
        if (!empty($empresaParam)) {
            $empresa = ctype_digit((string) $empresaParam)
                ? Empresa::find((int) $empresaParam)
                : Empresa::where('hash', $empresaParam)->first();
            if ($empresa) {
                return $empresa;
            }
        }

        return null;
    }
}
