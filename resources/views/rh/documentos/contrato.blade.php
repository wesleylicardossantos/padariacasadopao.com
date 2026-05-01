@php
    $empresaNome = trim((string) ($empresa->razao_social ?? $empresa->nome_fantasia ?? config('app.name', 'Empresa')));
    $empresaDocumento = trim((string) ($empresa->cpf_cnpj ?? ''));
    $empresaEndereco = collect([
        $empresa->rua ?? null,
        $empresa->numero ?? null,
        $empresa->bairro ?? null,
        optional($empresa->cidade)->nome ?? null,
        $empresa->uf ?? null,
        $empresa->cep ?? null,
    ])->filter(fn ($item) => filled($item))->implode(', ');

    $funcionarioEndereco = collect([
        $funcionario->rua ?? null,
        $funcionario->numero ?? null,
        $funcionario->bairro ?? null,
        optional($funcionario->cidade)->nome ?? null,
    ])->filter(fn ($item) => filled($item))->implode(', ');

    $admissao = !empty($ficha->data_admissao ?? null)
        ? \Carbon\Carbon::parse($ficha->data_admissao)->format('d/m/Y')
        : (!empty($funcionario->data_registro ?? null) ? \Carbon\Carbon::parse($funcionario->data_registro)->format('d/m/Y') : '-');

    $salario = number_format((float) ($funcionario->salario ?? 0), 2, ',', '.');
    $cargo = (string) ($funcionario->funcao ?? 'Colaborador(a)');
    $cidadeAssinatura = optional($empresa->cidade)->nome ?: '________________';
    $dataEmissao = now()->format('d/m/Y');
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Contrato Individual de Trabalho</title>
    <style>
        @page { margin: 20mm 16mm 22mm 16mm; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #162033;
            line-height: 1.55;
            margin: 0;
        }
        .watermark {
            position: fixed;
            top: 34%;
            left: 8%;
            right: 8%;
            text-align: center;
            font-size: 52px;
            color: rgba(15, 23, 42, 0.05);
            transform: rotate(-25deg);
            letter-spacing: 4px;
            z-index: -1;
            font-weight: 700;
            text-transform: uppercase;
        }
        .page-header {
            border: 1px solid #1f2a44;
            padding: 12px 14px 10px;
            margin-bottom: 18px;
        }
        .page-header-table,
        .meta-table,
        .info-table,
        .signature-table,
        .witness-table {
            width: 100%;
            border-collapse: collapse;
        }
        .brand {
            font-size: 17px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .subtitle {
            font-size: 9.5px;
            color: #4b5563;
        }
        .meta-cell {
            width: 34%;
            text-align: right;
            vertical-align: top;
        }
        .tag {
            display: inline-block;
            border: 1px solid #1f2a44;
            padding: 4px 8px;
            font-size: 9px;
            margin-bottom: 6px;
            text-transform: uppercase;
            font-weight: 700;
        }
        .doc-title {
            margin: 18px 0 12px;
            text-align: center;
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.7px;
        }
        .intro,
        .clause-body,
        .legal-note,
        .sign-place {
            text-align: justify;
        }
        .section-box {
            border: 1px solid #d5dbe7;
            margin-bottom: 10px;
        }
        .section-title {
            background: #eef2f7;
            border-bottom: 1px solid #d5dbe7;
            padding: 7px 10px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .section-content {
            padding: 10px 12px;
        }
        .clause-item {
            margin: 0 0 8px;
        }
        .info-table td {
            border: 1px solid #d5dbe7;
            padding: 7px 8px;
            vertical-align: top;
        }
        .info-label {
            font-size: 8.8px;
            color: #4b5563;
            text-transform: uppercase;
            display: block;
            margin-bottom: 2px;
        }
        .signature-section {
            margin-top: 26px;
        }
        .signature-table td,
        .witness-table td {
            width: 50%;
            padding: 0 12px;
            vertical-align: top;
        }
        .sign-line {
            margin-top: 42px;
            border-top: 1px solid #111827;
            padding-top: 6px;
            text-align: center;
            font-size: 10px;
        }
        .sign-role {
            font-size: 9px;
            color: #4b5563;
            text-transform: uppercase;
        }
        .footer {
            position: fixed;
            bottom: -8px;
            left: 0;
            right: 0;
            border-top: 1px solid #cbd5e1;
            padding-top: 6px;
            font-size: 9px;
            color: #475569;
        }
        .footer .left { float: left; width: 65%; }
        .footer .right { float: right; width: 35%; text-align: right; }
        .clearfix::after { content: ""; display: block; clear: both; }
        strong { font-weight: 700; }
    </style>
</head>
<body>
    <div class="watermark">{{ $empresaNome }}</div>

    <div class="page-header">
        <table class="page-header-table">
            <tr>
                <td>
                    <div class="brand">{{ $empresaNome }}</div>
                    <div class="subtitle">
                        @if($empresaDocumento) CNPJ/CPF: {{ $empresaDocumento }} · @endif
                        {{ $empresaEndereco ?: 'Endereço empresarial não informado no cadastro.' }}
                    </div>
                </td>
                <td class="meta-cell">
                    <div class="tag">Documento jurídico corporativo</div>
                    <table class="meta-table">
                        <tr>
                            <td><strong>Nº contrato:</strong> {{ $documentoNumero }}</td>
                        </tr>
                        <tr>
                            <td><strong>Emissão:</strong> {{ $dataEmissao }}</td>
                        </tr>
                        <tr>
                            <td><strong>Hash validação:</strong> {{ $documentoHash }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="doc-title">Contrato Individual de Trabalho por Prazo Indeterminado</div>

    <p class="intro">
        Pelo presente instrumento particular, de um lado <strong>{{ $empresaNome }}</strong>,
        @if($empresaDocumento) inscrita no CPF/CNPJ sob o nº <strong>{{ $empresaDocumento }}</strong>, @endif
        com sede em <strong>{{ $empresaEndereco ?: 'endereço não informado' }}</strong>, doravante denominada
        <strong>EMPREGADORA</strong>; e, de outro lado, <strong>{{ $funcionario->nome }}</strong>,
        @if(!empty($funcionario->cpf)) portador(a) do CPF nº <strong>{{ $funcionario->cpf }}</strong>, @endif
        @if(!empty($funcionario->rg)) RG nº <strong>{{ $funcionario->rg }}</strong>, @endif
        residente e domiciliado(a) em <strong>{{ $funcionarioEndereco ?: 'endereço não informado' }}</strong>,
        doravante denominado(a) <strong>EMPREGADO(A)</strong>, têm entre si justo e contratado o seguinte,
        regido pela legislação trabalhista aplicável e pelas cláusulas abaixo.
    </p>

    <table class="info-table" style="margin-bottom: 14px;">
        <tr>
            <td>
                <span class="info-label">Empregado(a)</span>
                {{ $funcionario->nome }}
            </td>
            <td>
                <span class="info-label">Função</span>
                {{ $cargo }}
            </td>
            <td>
                <span class="info-label">Admissão</span>
                {{ $admissao }}
            </td>
        </tr>
        <tr>
            <td>
                <span class="info-label">Remuneração base</span>
                R$ {{ $salario }}
            </td>
            <td>
                <span class="info-label">Contato</span>
                {{ $funcionario->telefone ?: ($funcionario->celular ?: '-') }}
            </td>
            <td>
                <span class="info-label">E-mail</span>
                {{ $funcionario->email ?: '-' }}
            </td>
        </tr>
    </table>

    <div class="section-box">
        <div class="section-title">Cláusula 1ª – Do objeto</div>
        <div class="section-content clause-body">
            <p class="clause-item">1.1. O presente contrato tem por objeto a prestação de serviços pessoais, contínuos, subordinados e onerosos pelo(a) EMPREGADO(A) à EMPREGADORA, integrando o quadro funcional da empresa.</p>
            <p class="clause-item">1.2. O(A) EMPREGADO(A) declara ciência de que suas atividades deverão observar as diretrizes internas, normas operacionais, políticas de segurança e procedimentos administrativos vigentes.</p>
        </div>
    </div>

    <div class="section-box">
        <div class="section-title">Cláusula 2ª – Da função e atribuições</div>
        <div class="section-content clause-body">
            <p class="clause-item">2.1. O(A) EMPREGADO(A) exercerá a função de <strong>{{ strtoupper($cargo) }}</strong>, comprometendo-se a executar as atribuições inerentes ao cargo, bem como outras atividades correlatas compatíveis com sua condição pessoal e profissional, nos termos da legislação aplicável.</p>
            <p class="clause-item">2.2. Poderá haver adequação de atividades, remanejamento interno ou recondução funcional, desde que preservados os limites legais e contratuais.</p>
        </div>
    </div>

    <div class="section-box">
        <div class="section-title">Cláusula 3ª – Da remuneração e forma de pagamento</div>
        <div class="section-content clause-body">
            <p class="clause-item">3.1. Pela prestação dos serviços, a EMPREGADORA pagará ao(à) EMPREGADO(A) a remuneração mensal de <strong>R$ {{ $salario }}</strong>, sujeita aos descontos legais, adiantamentos autorizados e demais rubricas aplicáveis.</p>
            <p class="clause-item">3.2. O pagamento será realizado conforme rotina financeira da empresa, preferencialmente por crédito em conta bancária de titularidade do(a) EMPREGADO(A), até o quinto dia útil do mês subsequente ao vencido, salvo norma coletiva ou política interna mais benéfica.</p>
        </div>
    </div>

    <div class="section-box">
        <div class="section-title">Cláusula 4ª – Da jornada de trabalho</div>
        <div class="section-content clause-body">
            <p class="clause-item">4.1. A jornada contratual observará a carga horária definida pela EMPREGADORA para a função exercida, respeitados os limites legais, os intervalos obrigatórios e os descansos remunerados.</p>
            <p class="clause-item">4.2. A eventual prestação de horas extraordinárias dependerá de necessidade do serviço e seguirá a legislação trabalhista e os instrumentos normativos aplicáveis.</p>
        </div>
    </div>

    <div class="section-box">
        <div class="section-title">Cláusula 5ª – Dos descontos e obrigações legais</div>
        <div class="section-content clause-body">
            <p class="clause-item">5.1. Ficam autorizados os descontos legais incidentes sobre a remuneração, inclusive previdenciários, fiscais, trabalhistas, convencionais, judiciais e aqueles expressamente permitidos por lei.</p>
            <p class="clause-item">5.2. O(A) EMPREGADO(A) compromete-se a manter atualizados seus dados cadastrais, bancários e documentais no sistema da EMPREGADORA.</p>
        </div>
    </div>

    <div class="section-box">
        <div class="section-title">Cláusula 6ª – Das disposições gerais</div>
        <div class="section-content clause-body">
            <p class="clause-item">6.1. Este contrato passa a produzir efeitos a partir da data de admissão informada no cadastro funcional, podendo ser rescindido nos termos da legislação vigente.</p>
            <p class="clause-item">6.2. A via gerada por este sistema integra o dossiê funcional do(a) EMPREGADO(A), servindo como documento corporativo para conferência, impressão e controle interno.</p>
        </div>
    </div>

    <p class="legal-note">
        E, por estarem justas e contratadas, as partes firmam o presente instrumento em duas vias de igual teor.
        Local e data: <strong>{{ $cidadeAssinatura }}, {{ $dataEmissao }}</strong>.
    </p>

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>
                    <div class="sign-line">
                        <strong>{{ $empresaNome }}</strong><br>
                        <span class="sign-role">EMPREGADORA</span>
                    </div>
                </td>
                <td>
                    <div class="sign-line">
                        <strong>{{ $funcionario->nome }}</strong><br>
                        <span class="sign-role">EMPREGADO(A)</span>
                    </div>
                </td>
            </tr>
        </table>
        <table class="witness-table" style="margin-top: 18px;">
            <tr>
                <td>
                    <div class="sign-line">
                        Testemunha 1<br>
                        <span class="sign-role">Nome / CPF</span>
                    </div>
                </td>
                <td>
                    <div class="sign-line">
                        Testemunha 2<br>
                        <span class="sign-role">Nome / CPF</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer clearfix">
        <div class="left">
            Documento gerado automaticamente pelo sistema · Nº {{ $documentoNumero }} · Hash {{ $documentoHash }}
        </div>
        <div class="right">
            Contrato individual de trabalho
        </div>
    </div>
</body>
</html>
