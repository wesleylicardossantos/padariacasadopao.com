<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Termo de Desligamento</title>
    <style>
        @page{size:A4;margin:18mm}
        body{font-family:DejaVu Sans,Arial,sans-serif;font-size:12px;color:#111827;line-height:1.55}
        .title{text-align:center;font-size:18px;font-weight:700;margin-bottom:16px}
    </style>
</head>
<body>
    <div class="title">Termo de Desligamento</div>
    <p>Fica registrado o desligamento do(a) colaborador(a) <strong>{{ $funcionario->nome }}</strong>, CPF <strong>{{ $funcionario->cpf }}</strong>.</p>
    <p>Data de desligamento: <strong>{{ $desligamento ? \Carbon\Carbon::parse($desligamento->data_desligamento)->format('d/m/Y') : '-' }}</strong></p>
    <p>Tipo: <strong>{{ $desligamento->tipo ?? '-' }}</strong></p>
    <p>Motivo: <strong>{{ $desligamento->motivo ?? '-' }}</strong></p>
    <br><br>
    <p>______________________________________________<br>Assinatura do colaborador</p>
    <br>
    <p>______________________________________________<br>Assinatura da empresa</p>
</body>
</html>
