<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Aviso de Férias</title>
    <style>
        @page{size:A4;margin:18mm}
        body{font-family:DejaVu Sans,Arial,sans-serif;font-size:12px;color:#111827;line-height:1.55}
        .title{text-align:center;font-size:18px;font-weight:700;margin-bottom:16px}
    </style>
</head>
<body>
    <div class="title">Aviso de Férias</div>
    <p>Informamos ao(à) colaborador(a) <strong>{{ $funcionario->nome }}</strong> o período de férias programado.</p>
    <p>Início: <strong>{{ $ferias ? \Carbon\Carbon::parse($ferias->data_inicio)->format('d/m/Y') : '-' }}</strong></p>
    <p>Fim: <strong>{{ $ferias ? \Carbon\Carbon::parse($ferias->data_fim)->format('d/m/Y') : '-' }}</strong></p>
    <p>Dias: <strong>{{ $ferias->dias ?? '-' }}</strong></p>
    <br><br>
    <p>______________________________________________<br>Assinatura do colaborador</p>
    <br>
    <p>______________________________________________<br>Assinatura da empresa</p>
</body>
</html>
