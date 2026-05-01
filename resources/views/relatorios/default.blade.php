<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Relatório' }}</title>
    <style type="text/css">
        @page { margin: 18px 22px 20px 22px; }
        body {
            margin: 0;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #1f2937;
        }

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

        .table-sm { width: 100%; border-collapse: collapse; }
        .table-sm th, .table-sm td { padding: 4px 5px; border: 1px solid #d1d5db; }
        .table-sm thead th { background: #1f4e79; color: #fff; text-transform: uppercase; font-size: 9px; border-color: #dbeafe; }

        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .mb-0 { margin-bottom: 0 !important; }
        .mb-2 { margin-bottom: 8px !important; }
        .mb-3 { margin-bottom: 12px !important; }

        .footer {
            margin-top: 12px;
            border-top: 1px solid #cbd5e1;
            padding-top: 4px;
            font-size: 8px;
            color: #64748b;
        }

        hr { border: 0; border-top: 1px solid #cbd5e1; }
    </style>
</head>
@php
    $brandingResolver = app(\App\Support\BrandingResolver::class);
    $empresa = $brandingResolver->resolveEmpresa();
    $config = class_exists(\App\Models\ConfigNota::class) ? \App\Models\ConfigNota::configStatic() : null;
    $logoSrc = $brandingResolver->logoDataUri($empresa, $config->logo ?? null, 'uploads/configEmitente', 'imgs/slym.png');
    $empresaNome = $brandingResolver->empresaNome($empresa);
    $empresaDocumento = $brandingResolver->empresaDocumento($empresa);
    $empresaEndereco = $brandingResolver->empresaEnderecoLinha($empresa);
    $empresaContato = $brandingResolver->empresaContatoLinha($empresa);
@endphp
<body>
    <div class="report-shell">
        <div class="report-header">
            <table class="header-table">
                <tr>
                    <td class="logo-wrap">
                        @if(!empty($logoSrc))
                            <div class="logo-box">
                                <img src="{{ $logoSrc }}" alt="Logo">
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="empresa-nome">{{ $empresaNome }}</div>
                        <div class="empresa-meta">
                            @if($empresaDocumento) Documento: {{ $empresaDocumento }}<br>@endif
                            @if($empresaEndereco) {{ $empresaEndereco }}<br>@endif
                            @if($empresaContato) {{ $empresaContato }}@endif
                        </div>
                    </td>
                    <td class="report-title-box">
                        <div class="report-title">{{ $title ?? 'Relatório' }}</div>
                        <div class="report-subtitle">Gerado em {{ date('d/m/Y - H:i') }}</div>
                    </td>
                </tr>
            </table>
        </div>

        @yield('content')

        <div class="footer">
            <div class="text-left">{{ env('SITE_SUPORTE') }}</div>
        </div>
    </div>
</body>
</html>
