<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $documentTitle }}</title>
    <style>
        @page { size: A4 portrait; margin: 3mm; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: #fff; }
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            color: #000;
            font-size: 8px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .page {
            width: 204mm;
            min-height: 290.5mm;
            margin: 0 auto;
            border: 0.24mm solid #000;
        }

        .title {
            height: 7.2mm;
            line-height: 7.2mm;
            text-align: center;
            font-size: 10.4px;
            font-weight: 700;
            text-transform: uppercase;
            background: #d9d9d9;
            border-bottom: 0.24mm solid #000;
            letter-spacing: 0.08mm;
        }

        .section {
            width: 100%;
            border-bottom: 0.24mm solid #000;
            page-break-inside: avoid;
        }

        .section:after {
            content: "";
            display: block;
            clear: both;
        }

        .section:last-child {
            border-bottom: none;
        }

        .side {
            float: left;
            width: 6.5mm;
            min-height: 100%;
            border-right: 0.24mm solid #000;
            background: #ededed;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            text-align: center;
            font-size: 5.35px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12mm;
            line-height: 1;
            padding: 1.6mm 0;
        }

        .body {
            float: left;
            width: 197.5mm;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
        }

        .grid td,
        .grid th {
            border-right: 0.24mm solid #000;
            border-bottom: 0.24mm solid #000;
            vertical-align: top;
            padding: 0.5mm 1mm 0.55mm;
        }

        .grid tr:last-child td,
        .grid tr:last-child th {
            border-bottom: none;
        }

        .grid td:last-child,
        .grid th:last-child {
            border-right: none;
        }

        .row-h-emp td { height: 7.8mm; }
        .row-h-emp-short td { height: 6.85mm; }
        .row-h-ctr td { height: 7.55mm; }
        .row-h-rec td { height: 7.55mm; }
        .row-h-sign td { height: 15.8mm; }
        .row-h-hom td { height: 33.5mm; }

        .label {
            display: block;
            font-size: 5.05px;
            line-height: 1.02;
            text-transform: uppercase;
            min-height: 2.2mm;
            margin-bottom: 0.22mm;
        }

        .value {
            display: block;
            font-size: 7.25px;
            line-height: 1.08;
            font-weight: 700;
            min-height: 2.75mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .value.normal { font-weight: 500; }
        .center { text-align: center; }
        .right { text-align: right; }

        .verbas-head th {
            background: #ededed;
            font-size: 5.35px;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
            padding: 0.65mm 0.45mm;
            border-right: 0.24mm solid #000;
            border-bottom: 0.24mm solid #000;
            height: 5.6mm;
        }

        .verbas-head th:last-child { border-right: none; }

        .verbas td {
            border-right: 0.24mm solid #000;
            border-bottom: 0.24mm solid #000;
            padding: 0.35mm 0.85mm 0.45mm;
            height: 7.15mm;
            vertical-align: top;
        }

        .verbas td:last-child { border-right: none; }
        .verbas tr:last-child td { border-bottom: none; }

        .vlabel {
            display: block;
            font-size: 4.95px;
            line-height: 1.02;
            text-transform: uppercase;
            min-height: 2.1mm;
            margin-bottom: 0.1mm;
        }

        .vref {
            display: block;
            font-size: 4.8px;
            line-height: 1.0;
            color: #111;
            min-height: 1.55mm;
        }

        .vvalue {
            display: block;
            font-size: 7.25px;
            line-height: 1.08;
            font-weight: 700;
            min-height: 2.45mm;
        }

        .texto-livre {
            white-space: pre-line;
            font-size: 6.2px;
            line-height: 1.12;
            font-weight: 500;
            margin: 0;
            padding: 0;
        }

        .blank-line {
            display: block;
            height: 3.6mm;
        }

        .wrap { white-space: normal; overflow: visible; text-overflow: clip; }

    </style>
</head>
<body>
<div class="page">
    <div class="title">{{ $documentTitle }}</div>

    <div class="section">
        <div class="side">Identificação</div>
        <div class="body">
            <table class="grid row-h-emp">
                <colgroup><col style="width:28%"><col style="width:72%"></colgroup>
                <tr>
                    <td><span class="label">01 – CNPJ / CEI</span><span class="value">{{ $empresaDocumento }}</span></td>
                    <td><span class="label">02 – Razão Social / Nome</span><span class="value">{{ $empresaNome }}</span></td>
                </tr>
            </table>
            <table class="grid row-h-emp">
                <colgroup><col style="width:69%"><col style="width:31%"></colgroup>
                <tr>
                    <td><span class="label">03 – Endereço (logradouro, nº, andar, apartamento)</span><span class="value normal wrap">{{ $empresaEndereco }}</span></td>
                    <td><span class="label">04 – Bairro</span><span class="value normal">{{ $empresaBairro }}</span></td>
                </tr>
            </table>
            <table class="grid row-h-emp-short">
                <colgroup><col style="width:33%"><col style="width:9%"><col style="width:15%"><col style="width:18%"><col style="width:25%"></colgroup>
                <tr>
                    <td><span class="label">05 – Município</span><span class="value normal">{{ $empresaMunicipio }}</span></td>
                    <td><span class="label">06 – UF</span><span class="value normal center">{{ $empresaUf }}</span></td>
                    <td><span class="label">07 – CEP</span><span class="value normal center">{{ $empresaCep }}</span></td>
                    <td><span class="label">08 – CNAE</span><span class="value normal">{{ $empresaCnae }}</span></td>
                    <td><span class="label">09 – CNPJ / CEI Tomador/Obra</span><span class="value normal">{{ $empresaTomadorDocumento }}</span></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="side">Identificação</div>
        <div class="body">
            <table class="grid row-h-emp">
                <colgroup><col style="width:28%"><col style="width:72%"></colgroup>
                <tr>
                    <td><span class="label">10 – PIS / PASEP</span><span class="value normal">{{ $funcionarioPis }}</span></td>
                    <td><span class="label">11 – Nome</span><span class="value">{{ $funcionarioNome }}</span></td>
                </tr>
            </table>
            <table class="grid row-h-emp">
                <colgroup><col style="width:69%"><col style="width:31%"></colgroup>
                <tr>
                    <td><span class="label">12 – Endereço (logradouro, nº, andar, apartamento)</span><span class="value normal wrap">{{ $funcionarioEndereco }}</span></td>
                    <td><span class="label">13 – Bairro</span><span class="value normal">{{ $funcionarioBairro }}</span></td>
                </tr>
            </table>
            <table class="grid row-h-emp-short">
                <colgroup><col style="width:31%"><col style="width:10%"><col style="width:15%"><col style="width:44%"></colgroup>
                <tr>
                    <td><span class="label">14 – Município</span><span class="value normal">{{ $funcionarioMunicipio }}</span></td>
                    <td><span class="label">15 – UF</span><span class="value normal center">{{ $funcionarioUf }}</span></td>
                    <td><span class="label">16 – CEP</span><span class="value normal center">{{ $funcionarioCep }}</span></td>
                    <td><span class="label">17 – Carteira de Trabalho (Número, Série e UF)</span><span class="value normal wrap">{{ $funcionarioCtps }}</span></td>
                </tr>
            </table>
            <table class="grid row-h-emp-short">
                <colgroup><col style="width:27%"><col style="width:21%"><col style="width:52%"></colgroup>
                <tr>
                    <td><span class="label">18 – CPF</span><span class="value normal">{{ $funcionarioCpf }}</span></td>
                    <td><span class="label">19 – Data de Nascimento</span><span class="value normal center">{{ $funcionarioNascimento }}</span></td>
                    <td><span class="label">20 – Nome da Mãe</span><span class="value normal">{{ $funcionarioNomeMae }}</span></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="side">Dados do Contrato</div>
        <div class="body">
            <table class="grid row-h-ctr">
                <colgroup><col style="width:26%"><col style="width:24.6%"><col style="width:24.6%"><col style="width:24.8%"></colgroup>
                <tr>
                    <td><span class="label">21 – Remuneração p/ fins rescisórios</span><span class="value normal right">{{ $remuneracaoRescisoria }}</span></td>
                    <td><span class="label">22 – Data de Admissão</span><span class="value normal center">{{ $dataAdmissao }}</span></td>
                    <td><span class="label">23 – Data do Aviso Prévio</span><span class="value normal center">{{ $dataAviso }}</span></td>
                    <td><span class="label">24 – Data do Afastamento</span><span class="value normal center">{{ $dataRescisao }}</span></td>
                </tr>
            </table>
            <table class="grid row-h-ctr">
                <colgroup><col style="width:37%"><col style="width:18%"><col style="width:20%"><col style="width:25%"></colgroup>
                <tr>
                    <td><span class="label">25 – Causa do Afastamento</span><span class="value normal wrap">{{ $causaAfastamento }}</span></td>
                    <td><span class="label">26 – Cód. Afastamento</span><span class="value normal center">{{ $codigoAfastamento }}</span></td>
                    <td><span class="label">27 – Pensão Alimentícia (%)</span><span class="value normal center">{{ $pensaoPercentual }}</span></td>
                    <td><span class="label">28 – Categoria do Trabalhador</span><span class="value normal wrap">{{ $categoriaTrabalhador }}</span></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="side">Discriminação das Verbas Rescisórias</div>
        <div class="body">
            <table class="verbas-head">
                <colgroup><col style="width:43%"><col style="width:17%"><col style="width:43%"><col style="width:17%"></colgroup>
                <tr>
                    <th>Verbas Rescisórias</th>
                    <th>Valor</th>
                    <th>Deduções</th>
                    <th>Valor</th>
                </tr>
            </table>
            <table class="verbas">
                <colgroup><col style="width:43%"><col style="width:17%"><col style="width:43%"><col style="width:17%"></colgroup>
                @for ($i = 0; $i < max(count($verbas['left']), count($verbas['right'])); $i++)
                    @php($left = $verbas['left'][$i] ?? ['codigo' => '', 'label' => '', 'valor' => null, 'referencia' => null])
                    @php($right = $verbas['right'][$i] ?? ['codigo' => '', 'label' => '', 'valor' => null, 'referencia' => null])
                    <tr>
                        <td>
                            <span class="vlabel">{{ trim(($left['codigo'] ? $left['codigo'].' – ' : '').$left['label']) }}</span>
                            <span class="vref">{{ $left['referencia'] ?? '' }}</span>
                        </td>
                        <td><span class="vvalue right">{{ is_string($left['valor']) ? $left['valor'] : ($left['valor'] !== null ? number_format((float) $left['valor'], 2, ',', '.') : '') }}</span></td>
                        <td>
                            <span class="vlabel">{{ trim(($right['codigo'] ? $right['codigo'].' – ' : '').$right['label']) }}</span>
                            <span class="vref">{{ $right['referencia'] ?? '' }}</span>
                        </td>
                        <td><span class="vvalue right">{{ is_string($right['valor']) ? $right['valor'] : ($right['valor'] !== null ? number_format((float) $right['valor'], 2, ',', '.') : '') }}</span></td>
                    </tr>
                @endfor
            </table>
        </div>
    </div>

    <div class="section">
        <div class="body" style="margin-left:6.8mm; width:194.92mm;">
            <table class="grid row-h-rec">
                <colgroup><col style="width:52.5%"><col style="width:47.5%"></colgroup>
                <tr>
                    <td><span class="label">56 – Local e Data do Recebimento</span><span class="value normal">{{ trim($localRecebimento . ($dataRecebimento ? ' – ' . $dataRecebimento : '')) }}</span></td>
                    <td><span class="label">57 – Carimbo e Assinatura do Empregador ou Preposto</span><span class="value normal">&nbsp;</span></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section" style="border-bottom:none;">
        <div class="side">Formalização da Rescisão</div>
        <div class="body">
            <table class="grid row-h-sign">
                <colgroup><col style="width:28.5%"><col style="width:28.5%"><col style="width:21.5%"><col style="width:21.5%"></colgroup>
                <tr>
                    <td><span class="label">58 – Assinatura do Trabalhador</span></td>
                    <td><span class="label">59 – Assinatura do Responsável Legal do Trabalhador</span></td>
                    <td><span class="label">61 – Digital do Trabalhador</span></td>
                    <td><span class="label">62 – Digital do Responsável Legal</span></td>
                </tr>
            </table>
            <table class="grid row-h-hom">
                <colgroup><col style="width:28.5%"><col style="width:28.5%"><col style="width:43%"></colgroup>
                <tr>
                    <td>
                        <span class="label">60 – Homologação</span>
                        <div class="texto-livre">{{ $textoHomologacao }}</div>
                    </td>
                    <td>
                        <span class="label">63 – Identificação do Órgão Homologador</span>
                        <div class="texto-livre">{{ $orgaoHomologador }}</div>
                    </td>
                    <td>
                        <span class="label">64 – Recepção pelo Banco (data e carimbo)</span>
                        <div class="texto-livre"></div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
</body>
</html>
