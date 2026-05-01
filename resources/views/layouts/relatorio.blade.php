<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>@yield('titulo','Relatório')</title>
<style>
    @page { margin: 18px 22px 20px 22px; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1f2937; margin: 0; }
    .report-shell { width: 100%; }
    .report-header { border-bottom: 2px solid #1f4e79; padding-bottom: 10px; margin-bottom: 12px; }
    .header-table { width: 100%; border-collapse: collapse; }
    .header-table td { vertical-align: top; }
    .logo-wrap { width: 95px; }
    .logo-box { width: 82px; height: 58px; }
    .logo-box img { max-width: 82px; max-height: 58px; }
    .empresa-nome { font-size: 16px; font-weight: bold; color: #0f172a; margin-bottom: 4px; }
    .empresa-meta { font-size: 9px; color: #64748b; line-height: 1.45; }
    .report-title-box { text-align: right; width: 240px; }
    .report-title { font-size: 16px; font-weight: bold; color: #1f4e79; margin-bottom: 4px; }
    .report-subtitle { font-size: 9px; color: #64748b; }

    .meta { width:100%; margin:8px 0 10px 0; border-collapse: collapse; }
    .meta td { width: 33.333%; padding-right: 6px; vertical-align: top; }
    .meta td:last-child { padding-right: 0; }
    .meta-box { border:1px solid #cbd5e1; background:#f8fafc; padding:6px 8px; min-height: 34px; }
    .meta-label { font-size:8px; text-transform:uppercase; color:#64748b; }
    .meta-value { font-size:10px; font-weight:bold; color:#0f172a; margin-top:2px; }

    table.report { width:100%; border-collapse:collapse; table-layout: fixed; }
    table.report thead th { background:#1f4e79; color:#fff; font-size:9px; text-transform:uppercase; padding:5px; border:1px solid #dbeafe; }
    table.report tbody td { border:1px solid #d1d5db; padding:4px 5px; }
    .text-center { text-align:center; }
    .text-right { text-align:right; }

    .footer { margin-top: 12px; border-top:1px solid #cbd5e1; padding-top:4px; font-size:8px; color:#64748b; text-align:right; }
</style>
</head>
<body>
@php
$brandingResolver = app(\App\Support\BrandingResolver::class);
$empresa = $brandingResolver->resolveEmpresa();

$normalizeScalar = function ($value) {
    if (is_null($value)) {
        return null;
    }
    if (is_array($value)) {
        return null;
    }
    if (is_object($value)) {
        if (method_exists($value, '__toString')) {
            $value = (string) $value;
        } else {
            return null;
        }
    }

    $value = trim((string) $value);
    return $value === '' ? null : $value;
};

$decodeJsonArray = function ($value) {
    if (is_array($value)) {
        return $value;
    }
    if (is_object($value)) {
        return (array) $value;
    }
    if (!is_string($value)) {
        return null;
    }

    $decoded = json_decode($value, true);
    return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : null;
};

$pick = function ($sources, array $keys) use ($normalizeScalar, $decodeJsonArray) {
    foreach ((array) $sources as $source) {
        if (is_array($source)) {
            foreach ($keys as $key) {
                if (array_key_exists($key, $source)) {
                    $value = $normalizeScalar($source[$key]);
                    if ($value !== null) {
                        return $value;
                    }
                }
            }
        } elseif (is_object($source)) {
            foreach ($keys as $key) {
                if (isset($source->{$key})) {
                    $value = $normalizeScalar($source->{$key});
                    if ($value !== null) {
                        return $value;
                    }
                }
            }
        }

        $decoded = $decodeJsonArray($source);
        if (is_array($decoded)) {
            foreach ($keys as $key) {
                if (array_key_exists($key, $decoded)) {
                    $value = $normalizeScalar($decoded[$key]);
                    if ($value !== null) {
                        return $value;
                    }
                }
            }
        }
    }

    return null;
};

$helperNome = method_exists($brandingResolver, 'empresaNome') ? $brandingResolver->empresaNome($empresa) : null;
$helperDocumento = method_exists($brandingResolver, 'empresaDocumento') ? $brandingResolver->empresaDocumento($empresa) : null;
$helperEndereco = method_exists($brandingResolver, 'empresaEnderecoLinha') ? $brandingResolver->empresaEnderecoLinha($empresa) : null;
$helperContato = method_exists($brandingResolver, 'empresaContatoLinha') ? $brandingResolver->empresaContatoLinha($empresa) : null;
$helperLogo = method_exists($brandingResolver, 'logoDataUri') ? $brandingResolver->logoDataUri($empresa) : null;

$sources = array_filter([
    $empresa,
    $decodeJsonArray($helperNome),
    $decodeJsonArray($helperDocumento),
    $decodeJsonArray($helperEndereco),
    $decodeJsonArray($helperContato),
]);

$empresaNome = $pick(array_merge([$helperNome], $sources), ['nome_fantasia', 'razao_social', 'nome']) ?: config('app.name', 'Empresa');
$empresaDocumento = $pick(array_merge([$helperDocumento], $sources), ['cpf_cnpj', 'cnpj', 'documento', 'cpfCnpj']);

$rua = $pick(array_merge([$helperEndereco], $sources), ['rua', 'logradouro', 'endereco']);
$numero = $pick($sources, ['numero']);
$bairro = $pick($sources, ['bairro']);
$cidade = $pick($sources, ['cidade', 'cidade_nome']);
$uf = $pick($sources, ['uf', 'estado']);
$telefone = $pick(array_merge([$helperContato], $sources), ['telefone', 'fone', 'celular']);
$email = $pick(array_merge([$helperContato], $sources), ['email']);

if (!$cidade && is_object($empresa) && method_exists($empresa, 'cidade')) {
    try {
        $cidadeModel = $empresa->relationLoaded('cidade') ? $empresa->cidade : $empresa->cidade()->first();
        if ($cidadeModel) {
            $cidade = $normalizeScalar($cidadeModel->nome ?? null) ?: $normalizeScalar($cidadeModel->cidade ?? null);
            if (!$uf) {
                $uf = $normalizeScalar($cidadeModel->uf ?? null);
            }
        }
    } catch (\Throwable $e) {
        // ignora erros de relação no relatório
    }
}

$empresaEndereco = trim(implode(' - ', array_filter([
    trim(implode(' ', array_filter([$rua, $numero]))),
    $bairro,
    trim(implode('/', array_filter([$cidade, $uf]))),
])));
$empresaEndereco = $empresaEndereco !== '' ? $empresaEndereco : null;

$empresaContato = trim(implode(' | ', array_filter([$telefone, $email])));
$empresaContato = $empresaContato !== '' ? $empresaContato : null;

$empresaLogo = $helperLogo;
if (!$empresaLogo && $empresa) {
    try {
        $logoPath = null;
        if (!empty($empresa->branding_logo_path)) {
            $logoPath = storage_path('app/public/' . ltrim($empresa->branding_logo_path, '/'));
        } elseif (!empty($empresa->logo)) {
            $logoPath = public_path('logos/' . ltrim($empresa->logo, '/'));
        }

        if ($logoPath && is_file($logoPath)) {
            $mime = function_exists('mime_content_type') ? mime_content_type($logoPath) : 'image/png';
            $empresaLogo = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
        }
    } catch (\Throwable $e) {
        $empresaLogo = null;
    }
}
@endphp

<div class="report-shell">
    <div class="report-header">
        <table class="header-table">
            <tr>
                <td class="logo-wrap">
                    @if($empresaLogo)
                        <div class="logo-box">
                            <img src="{{ $empresaLogo }}" alt="Logo da empresa">
                        </div>
                    @endif
                </td>
                <td>
                    <div class="empresa-nome">{{ $empresaNome }}</div>
                    <div class="empresa-meta">
                        @if($empresaDocumento) @yield('empresa_documento_label', 'Documento'): {{ $empresaDocumento }}<br>
                        @endif
                    </div>
                </td>
                <td class="report-title-box">
                    <div class="report-title">@yield('titulo','Relatório')</div>
                    <div class="report-subtitle">Gerado em {{ now()->format('d/m/Y H:i') }}</div>
                </td>
            </tr>
        </table>
    </div>

    @yield('conteudo')

    <div class="footer">Documento gerado automaticamente pelo sistema</div>
</div>
</body>
</html>
