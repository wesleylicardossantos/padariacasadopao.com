<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Recibo de Pagamento de Salário</title>
    <style>
        @page { size: A4 portrait; margin: 4mm 0; }
        * { box-sizing: border-box; }
        :root { --receipt-font-family: DejaVu Sans, Arial, Helvetica, sans-serif; }
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            background: #ffffff;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            color: #0f172a;
            font-family: var(--receipt-font-family);
            font-size: 8.4px;
        }
        .a4 {
            width: 196mm;
            min-height: 289mm;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            gap: 0;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .voucher-slot {
            height: 134mm;
            padding: 0;
            overflow: hidden;
            width: 100%;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .cut-line {
            width: 100%;
            margin: 1.8mm 0 2.2mm;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .cut-line td {
            height: 0;
            padding: 0;
            border: none;
            border-top: 2px dashed #1f2937;
        }
        .voucher {
            width: 100%;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        @media print {
            .a4 { width: 196mm; }
            .cut-line td { border-top: 2px dashed #000 !important; }
        }
        .title {
            text-align: center;
            font-size: 13.4px;
            font-weight: 800;
            color: #0b2c64;
            letter-spacing: .5px;
            margin: 0 0 .7mm 0;
        }
        .top-line {
            border-top: 2px solid #2e5fa7;
            margin-bottom: .5mm;
        }
        .section {
            margin-bottom: .5mm;
            border: 1px solid #c8d3e3;
            overflow: hidden;
        }
        .section-title {
            background: #e9eff8;
            color: #143f7f;
            font-weight: 700;
            font-size: 9px;
            letter-spacing: .55px;
            padding: .9mm 1.4mm;
            text-transform: uppercase;
            border-bottom: 1px solid #c8d3e3;
        }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        td, th {
            border-right: 1px solid #d5deeb;
            border-bottom: 1px solid #d5deeb;
            padding: .6mm .95mm;
            vertical-align: top;
        }
        tr:last-child td, tr:last-child th { border-bottom: none; }
        td:last-child, th:last-child { border-right: none; }
        .label {
            display: block;
            font-size: 7px;
            line-height: 1.1;
            color: #244b86;
            font-weight: 700;
            letter-spacing: .35px;
            text-transform: uppercase;
            margin-bottom: .2mm;
        }
        .value {
            display: block;
            color: #000;
            font-size: 7.8px;
            min-height: 2.2mm;
            word-wrap: break-word;
        }
        .value.big { font-size: 9.1px; font-weight: 700; }
        .center { text-align: center; }
        .right { text-align: right; }
        .strong { font-weight: 700; }
        .mono { font-family: inherit; font-variant-numeric: tabular-nums; }
        .events thead th {
            background: #f3f6fb;
            color: #20457d;
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
        }
        .events tbody td { height: 3.6mm; font-size: 7.8px; }
        .bottom-grid td { height: 6.8mm; }
        .declaration-wrap {
            display: table;
            width: 100%;
            border: 1px solid #c8d3e3;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .declaration-box,
        .signature-box,
        .date-box {
            display: table-cell;
            vertical-align: top;
            padding: 1.0mm 1.3mm;
        }
        .declaration-box,
        .signature-box { border-right: 1px solid #d5deeb; }
        .declaration-box {
            width: 20%;
            color: #294675;
            font-size: 7.2px;
            line-height: 1.15;
        }
        .signature-box { width: 60%; }
        .date-box { width: 20%; }
        .signature-area,
        .date-area {
            height: 5.8mm;
            border-bottom: 1px solid #7d8da8;
            margin-bottom: .5mm;
        }
        .sign-label {
            text-align: center;
            color: #4b6288;
            font-size: 7px;
        }
        .footer-note {
            text-align: right;
            color: #6a7f9d;
            font-size: 7px;
            margin-top: .6mm;
        }
    </style>
</head>
<body>
<?php
    $fmt = function ($v) { return number_format((float) ($v ?? 0), 2, ',', '.'); };
    $texto = function ($v, $fallback = '') { return isset($v) && $v !== '' && $v !== null ? $v : $fallback; };

    $empresaNome = $empresa->razao_social ?? $empresa->nome_fantasia ?? '';
    $empresaDoc = $empresa->cpf_cnpj ?? ($empresa->cnpj ?? '');
    $competencia = \App\Support\RHCompetenciaHelper::formatar($mes ?? date('m'), $ano ?? date('Y'));

    $candidatasAdmissao = [$funcionario->data_admissao ?? null, $funcionario->admissao ?? null, $funcionario->dt_admissao ?? null, $funcionario->admission_date ?? null, $funcionario->created_at ?? null];
    $admissao = '';
    foreach ($candidatasAdmissao as $valorAdmissao) {
        if (!empty($valorAdmissao)) {
            try { $admissao = \Carbon\Carbon::parse($valorAdmissao)->format('d/m/Y'); break; }
            catch (\Throwable $e) {
                if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', (string) $valorAdmissao)) { $admissao = $valorAdmissao; break; }
            }
        }
    }

    $cargo = $funcionario->funcao ?? $funcionario->cargo ?? '';
    $setor = $funcionario->setor ?? $funcionario->departamento ?? $funcionario->local ?? '';
    $salarioBase = $valores['salario_base'] ?? $salarioBase ?? 0;
    $totalProventos = $valores['total_proventos'] ?? $eventos ?? $proventos ?? $salarioBase ?? 0;
    $totalDescontos = $valores['total_descontos'] ?? $descontos ?? 0;
    $liquidoCalc = $valores['liquido'] ?? $liquido ?? (($totalProventos ?? 0) - ($totalDescontos ?? 0));
    $baseInss = $valores['base_inss'] ?? $salarioBase ?? 0;
    $inss = $valores['inss'] ?? $valores['desconto_inss'] ?? 0;
    $baseFgts = $valores['base_fgts'] ?? $salarioBase ?? 0;
    $fgts = $valores['fgts'] ?? 0;
    $baseIrrf = $valores['base_irrf'] ?? 0;
    $faixaIrrf = $valores['faixa_irrf'] ?? 0;

    $proventosItens = $valores['itens_proventos'] ?? [];
    $descontosItens = $valores['itens_descontos'] ?? [];
    $eventosLinhas = [];
    $totalLinhas = max(count($proventosItens), count($descontosItens), 6);
    for ($i = 0; $i < $totalLinhas; $i++) {
        $prov = $proventosItens[$i] ?? null;
        $desc = $descontosItens[$i] ?? null;
        $eventosLinhas[] = [
            'codigo' => $prov['evento_codigo'] ?? $prov['codigo'] ?? $desc['evento_codigo'] ?? $desc['codigo'] ?? '',
            'descricao' => $prov['descricao'] ?? $desc['descricao'] ?? '',
            'referencia' => $prov['referencia'] ?? $desc['referencia'] ?? '',
            'vencimento' => $prov['valor'] ?? null,
            'desconto' => $desc['valor'] ?? null,
        ];
    }
?>
<div class="a4">
    <div class="voucher-slot voucher-top">
        <?php echo $__env->make('rh.holerite._voucher_content', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
    <table class="cut-line" aria-hidden="true"><tr><td></td></tr></table>
    <div class="voucher-slot voucher-bottom">
        <?php echo $__env->make('rh.holerite._voucher_content', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
</div>
</body>
</html>
<?php /**PATH /home2/wesl4494/public_html/padariacasadopao.com/resources/views/rh/holerite/pdf.blade.php ENDPATH**/ ?>