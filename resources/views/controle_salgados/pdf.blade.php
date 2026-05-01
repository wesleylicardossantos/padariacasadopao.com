<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Controle de Salgados</title>
    <style>
        *{box-sizing:border-box;font-family:DejaVu Sans, sans-serif}
        body{margin:0;padding:10px;color:#111}
        .sheet{border:1px solid #111;width:100%}
        .top{display:grid;grid-template-columns:1fr 1fr;border-bottom:1px solid #111}
        .top div{padding:8px 12px;font-weight:700;border-right:1px solid #111}
        .top div:last-child{border-right:none}
        .section{background:#ececec;text-align:center;font-weight:800;padding:6px 0;border-bottom:1px solid #111}
        table{width:100%;border-collapse:collapse}
        th,td{border-right:1px solid #111;border-bottom:1px solid #111;padding:6px 8px;font-size:12px;height:26px}
        th:last-child,td:last-child{border-right:none}
        th{text-align:center;background:#f5f5f5}
        .gap{height:18px;border-bottom:1px solid #111}
        .col-qtd{width:14%}.col-desc{width:48%}.col-termino{width:18%}.col-saldo{width:20%}
        .footer{margin-top:8px;font-size:11px;color:#444}
    </style>
</head>
<body>
@php
    $manha = $item->itens->where('periodo', 'manha')->sortBy('ordem')->values();
    $tarde = $item->itens->where('periodo', 'tarde')->sortBy('ordem')->values();
@endphp
<div class="sheet">
    <div class="top">
        <div>DATA: {{ optional($item->data)->format('d/m/Y') }}</div>
        <div>DIA: {{ $item->dia }}</div>
    </div>

    <div class="section">MANHÃ</div>
    <table>
        <thead>
            <tr>
                <th class="col-qtd">QTD</th>
                <th class="col-desc">DESCRIÇÃO</th>
                <th class="col-termino">TERMINO</th>
                <th class="col-saldo">SALDO</th>
            </tr>
        </thead>
        <tbody>
            @foreach($manha as $row)
                <tr>
                    <td>{{ $row->qtd }}</td>
                    <td>{{ $row->descricao }}</td>
                    <td>{{ $row->termino }}</td>
                    <td>{{ $row->saldo }}</td>
                </tr>
            @endforeach
            @for($i = $manha->count(); $i < 5; $i++)
                <tr><td></td><td></td><td></td><td></td></tr>
            @endfor
        </tbody>
    </table>

    <div class="gap"></div>

    <div class="section">TARDE</div>
    <table>
        <thead>
            <tr>
                <th class="col-qtd">QTD</th>
                <th class="col-desc">DESCRIÇÃO</th>
                <th class="col-termino">TERMINO</th>
                <th class="col-saldo">SALDO</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tarde as $row)
                <tr>
                    <td>{{ $row->qtd }}</td>
                    <td>{{ $row->descricao }}</td>
                    <td>{{ $row->termino }}</td>
                    <td>{{ $row->saldo }}</td>
                </tr>
            @endforeach
            @for($i = $tarde->count(); $i < 14; $i++)
                <tr><td></td><td></td><td></td><td></td></tr>
            @endfor
        </tbody>
    </table>
</div>
<div class="footer">
    @if($item->observacoes)
        Observações: {{ $item->observacoes }}<br>
    @endif
    Controle #{{ $item->id }} | Emitido em {{ now()->format('d/m/Y H:i') }}
</div>
</body>
</html>
