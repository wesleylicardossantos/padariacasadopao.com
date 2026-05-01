<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Ficha Cadastral - Admissão</title>
    <style>
        @page { size: A4; margin: 9mm 10mm 9mm 10mm; }

        body{
            margin:0;
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size:9px;
            color:#0f172a;
        }

        .sheet{
            width:100%;
        }

        .header{
            border-bottom:2px solid #1d4f91;
            padding-bottom:5px;
            margin-bottom:6px;
        }

        .header-table{
            width:100%;
            border-collapse:collapse;
        }

        .header-table td{
            vertical-align:top;
        }

        .title{
            text-align:center;
            font-size:15px;
            font-weight:700;
            letter-spacing:.4px;
            color:#0f172a;
        }

        .subtitle{
            text-align:center;
            font-size:8px;
            color:#475569;
            margin-top:1px;
        }

        .section{
            border:1px solid #cbd5e1;
            border-radius:4px;
            margin-bottom:5px;
            overflow:hidden;
        }

        .section-head{
            background:#eef4fb;
            color:#1d4f91;
            font-size:8px;
            font-weight:700;
            text-transform:uppercase;
            letter-spacing:.4px;
            padding:4px 6px;
            border-bottom:1px solid #d7e2f0;
        }

        table.form{
            width:100%;
            border-collapse:collapse;
        }

        table.form td{
            border-right:1px solid #e5e7eb;
            border-bottom:1px solid #e5e7eb;
            padding:3px 5px;
            vertical-align:top;
        }

        table.form tr:last-child td{ border-bottom:none; }
        table.form td:last-child{ border-right:none; }

        .label{
            display:block;
            font-size:7px;
            font-weight:700;
            text-transform:uppercase;
            color:#475569;
            margin-bottom:1px;
            letter-spacing:.25px;
        }

        .value{
            font-size:9px;
            min-height:10px;
            line-height:1.15;
            color:#111827;
        }

        .checkline{
            font-size:9px;
            line-height:1.2;
        }

        .mark{
            font-weight:700;
            color:#111827;
        }

        .muted{
            color:#64748b;
        }

        .small{
            font-size:7.3px;
            line-height:1.2;
        }

        .footer{
            margin-top:4px;
            font-size:7.5px;
            color:#64748b;
            text-align:right;
        }
    </style>
</head>
<body>
@php
    $f = $item;
    $fa = $ficha;

    $fmtDate = function($d) {
        if (!$d) return '';
        try {
            return \Carbon\Carbon::parse($d)->format('d/m/Y');
        } catch (\Exception $e) { return ''; }
    };

    $mark = function($cond){ return $cond ? '✓' : ''; };

    $city = optional($f->cidade)->nome ?? '';
    $ufCity = optional($f->cidade)->uf ?? '';

    $rc = strtoupper($fa->raca_cor ?? '');
    $sx = strtoupper($fa->sexo ?? '');
    $ec = strtoupper($fa->estado_civil ?? '');
@endphp

<div class="sheet">
    <div class="header">
        <div class="title">FICHA CADASTRAL - ADMISSÃO</div>
        <div class="subtitle">Cadastro funcional para admissão e conferência interna</div>
    </div>

    <div class="section">
        <div class="section-head">Dados pessoais</div>
        <table class="form">
            <tr>
                <td colspan="2">
                    <span class="label">Nome</span>
                    <div class="value">{{ $f->nome }}</div>
                </td>
                <td>
                    <span class="label">CPF</span>
                    <div class="value">{{ $f->cpf }}</div>
                </td>
                <td>
                    <span class="label">Matrícula / Matrícula social</span>
                    <div class="value">{{ $fa->matricula ?? '' }}{{ !empty($fa->matricula_social) ? ' / '.$fa->matricula_social : '' }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Endereço</span>
                    <div class="value">{{ trim(($f->rua ?? '') . ' ' . ($f->numero ?? '')) }}</div>
                </td>
                <td>
                    <span class="label">Bairro</span>
                    <div class="value">{{ $f->bairro }}</div>
                </td>
                <td>
                    <span class="label">Complemento</span>
                    <div class="value">{{ $f->complemento ?? '' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Cidade / UF</span>
                    <div class="value">{{ trim($city . ($ufCity ? ' - '.$ufCity : '')) }}</div>
                </td>
                <td>
                    <span class="label">Telefone</span>
                    <div class="value">{{ $f->telefone }}</div>
                </td>
                <td>
                    <span class="label">Celular</span>
                    <div class="value">{{ $f->celular }}</div>
                </td>
                <td>
                    <span class="label">Data de nascimento</span>
                    <div class="value">{{ $fmtDate($fa->data_nascimento ?? null) }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Nome do pai</span>
                    <div class="value">{{ $fa->nome_pai ?? '' }}</div>
                </td>
                <td colspan="2">
                    <span class="label">Nome da mãe</span>
                    <div class="value">{{ $fa->nome_mae ?? '' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Naturalidade / Nacionalidade</span>
                    <div class="value">{{ $fa->naturalidade ?? '' }}{{ !empty($fa->nacionalidade) ? ' / '.$fa->nacionalidade : '' }}</div>
                </td>
                <td>
                    <span class="label">UF naturalidade</span>
                    <div class="value">{{ $fa->uf_naturalidade ?? '' }}</div>
                </td>
                <td>
                    <span class="label">RG</span>
                    <div class="value">{{ $f->rg }}</div>
                </td>
                <td>
                    <span class="label">Órgão emissor / Emissão</span>
                    <div class="value">{{ $fa->rg_orgao_emissor ?? '' }} {{ $fmtDate($fa->rg_data_emissao ?? null) ? ' - '.$fmtDate($fa->rg_data_emissao ?? null) : '' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-head">Classificação e documentos</div>
        <table class="form">
            <tr>
                <td>
                    <span class="label">Raça / Cor</span>
                    <div class="checkline">
                        Branca <span class="mark">{{ $mark($rc=='BRANCA') }}</span> &nbsp;
                        Preta <span class="mark">{{ $mark($rc=='PRETA') }}</span> &nbsp;
                        Parda <span class="mark">{{ $mark($rc=='PARDA') }}</span> &nbsp;
                        Amarela <span class="mark">{{ $mark($rc=='AMARELA') }}</span>
                    </div>
                </td>
                <td>
                    <span class="label">Sexo</span>
                    <div class="checkline">
                        Masculino <span class="mark">{{ $mark($sx=='MASCULINO') }}</span> &nbsp;
                        Feminino <span class="mark">{{ $mark($sx=='FEMININO') }}</span> &nbsp;
                        Outro <span class="mark">{{ $mark($sx=='OUTRO') }}</span>
                    </div>
                </td>
                <td colspan="2">
                    <span class="label">Estado civil</span>
                    <div class="checkline">
                        Solteiro <span class="mark">{{ $mark($ec=='SOLTEIRO') }}</span> &nbsp;
                        Casado <span class="mark">{{ $mark($ec=='CASADO') }}</span> &nbsp;
                        Divorciado <span class="mark">{{ $mark($ec=='DIVORCIADO') }}</span> &nbsp;
                        Separado <span class="mark">{{ $mark($ec=='SEPARADO') }}</span> &nbsp;
                        Outros <span class="mark">{{ $mark($ec=='OUTROS') }}</span>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Deficiência física</span>
                    <div class="checkline">Sim <span class="mark">{{ $mark(($fa->deficiencia_fisica ?? false) == true) }}</span> &nbsp; Não <span class="mark">{{ $mark(($fa->deficiencia_fisica ?? false) == false) }}</span></div>
                </td>
                <td>
                    <span class="label">Grau de instrução</span>
                    <div class="value">{{ $fa->grau_instrucao ?? '' }}</div>
                </td>
                <td>
                    <span class="label">PIS</span>
                    <div class="value">{{ $fa->pis_numero ?? '' }}</div>
                </td>
                <td>
                    <span class="label">Cadastro PIS</span>
                    <div class="value">{{ $fmtDate($fa->pis_data_cadastro ?? null) }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">CTPS número</span>
                    <div class="value">{{ $fa->ctps_numero ?? '' }}</div>
                </td>
                <td>
                    <span class="label">CTPS série / UF</span>
                    <div class="value">{{ $fa->ctps_serie ?? '' }} {{ !empty($fa->ctps_uf) ? ' - '.$fa->ctps_uf : '' }}</div>
                </td>
                <td>
                    <span class="label">Expedição CTPS</span>
                    <div class="value">{{ $fmtDate($fa->ctps_data_expedicao ?? null) }}</div>
                </td>
                <td>
                    <span class="label">Título eleitor</span>
                    <div class="value">{{ $fa->titulo_eleitor ?? '' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Zona / Seção</span>
                    <div class="value">{{ $fa->titulo_zona ?? '' }}{{ !empty($fa->titulo_secao) ? ' / '.$fa->titulo_secao : '' }}</div>
                </td>
                <td>
                    <span class="label">Certificado reservista</span>
                    <div class="value">{{ $fa->certificado_reservista ?? '' }}</div>
                </td>
                <td>
                    <span class="label">CNH / Categoria</span>
                    <div class="value">{{ $fa->cnh_numero ?? '' }}{{ !empty($fa->cnh_categoria) ? ' - '.$fa->cnh_categoria : '' }}</div>
                </td>
                <td>
                    <span class="label">Validade / 1ª habilitação</span>
                    <div class="value">
                        {{ $fmtDate($fa->cnh_validade ?? null) }}
                        {{ $fmtDate($fa->cnh_primeira_habilitacao ?? null) ? ' / '.$fmtDate($fa->cnh_primeira_habilitacao ?? null) : '' }}
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Tipo habilitação</span>
                    <div class="value">{{ $fa->tipo_habilitacao ?? '' }}</div>
                </td>
                <td>
                    <span class="label">Registro profissional</span>
                    <div class="value">{{ $fa->registro_profissional ?? '' }}</div>
                </td>
                <td colspan="2">
                    <span class="label">Órgão do registro</span>
                    <div class="value">{{ $fa->orgao_registro_profissional ?? '' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-head">Dados internos da empresa</div>
        <table class="form">
            <tr>
                <td>
                    <span class="label">Salário</span>
                    <div class="value">{{ isset($f->salario) ? number_format((float)$f->salario, 2, ',', '.') : '' }}</div>
                </td>
                <td>
                    <span class="label">Função / Cargo</span>
                    <div class="value">{{ $f->funcao ?? '' }}</div>
                </td>
                <td>
                    <span class="label">Data admissão / opção FGTS</span>
                    <div class="value">{{ $fmtDate($fa->data_admissao ?? null) }}{{ $fmtDate($fa->data_opcao_fgts ?? null) ? ' / '.$fmtDate($fa->data_opcao_fgts ?? null) : '' }}</div>
                </td>
                <td>
                    <span class="label">Exame admissional</span>
                    <div class="value">{{ $fmtDate($fa->data_exame_admissional ?? null) }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Dependentes</span>
                    <div class="checkline">Sim <span class="mark">{{ $mark(($fa->possui_dependentes ?? false) == true) }}</span> &nbsp; Não <span class="mark">{{ $mark(($fa->possui_dependentes ?? false) == false) }}</span></div>
                </td>
                <td colspan="3">
                    <span class="label">Detalhes dependentes</span>
                    <div class="value">{{ $fa->dependentes_texto ?? '' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Vale transporte</span>
                    <div class="checkline">Sim <span class="mark">{{ $mark(($fa->vale_transporte ?? false) == true) }}</span> &nbsp; Não <span class="mark">{{ $mark(($fa->vale_transporte ?? false) == false) }}</span></div>
                </td>
                <td>
                    <span class="label">Linhas</span>
                    <div class="value">{{ $fa->vt_linhas ?? '' }}</div>
                </td>
                <td>
                    <span class="label">Preço passagem</span>
                    <div class="value">{{ isset($fa->vt_preco_passagem) ? number_format((float)$fa->vt_preco_passagem, 2, ',', '.') : '' }}</div>
                </td>
                <td>
                    <span class="label">Qtd. por dia</span>
                    <div class="value">{{ $fa->vt_quantidade_dia ?? '' }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Horário seg. a sexta</span>
                    <div class="value">
                        Entrada: {{ $fa->horario_seg_sex_entrada ?? '' }}
                        {{ !empty($fa->horario_seg_sex_saida) ? ' | Saída: '.$fa->horario_seg_sex_saida : '' }}
                        {{ !empty($fa->horario_seg_sex_intervalo_inicio) ? ' | Intervalo: '.$fa->horario_seg_sex_intervalo_inicio.' - '.$fa->horario_seg_sex_intervalo_fim : '' }}
                    </div>
                </td>
                <td colspan="2">
                    <span class="label">Horário sábado</span>
                    <div class="value">
                        @if(($fa->nao_trabalha_sabado ?? false) == true)
                            Não trabalha aos sábados
                        @else
                            Entrada: {{ $fa->horario_sabado_entrada ?? '' }}
                            {{ !empty($fa->horario_sabado_saida) ? ' | Saída: '.$fa->horario_sabado_saida : '' }}
                        @endif
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Forma pagamento</span>
                    <div class="value">{{ $fa->forma_pagamento ?? '' }}</div>
                </td>
                <td>
                    <span class="label">Banco / Agência</span>
                    <div class="value">{{ $fa->banco ?? '' }}{{ !empty($fa->agencia) ? ' / '.$fa->agencia : '' }}</div>
                </td>
                <td>
                    <span class="label">Conta salário</span>
                    <div class="value">{{ $fa->conta_salario ?? '' }}</div>
                </td>
                <td>
                    <span class="label">Contrato experiência</span>
                    <div class="checkline">Sim <span class="mark">{{ $mark(($fa->contrato_experiencia ?? false) == true) }}</span> &nbsp; Não <span class="mark">{{ $mark(($fa->contrato_experiencia ?? false) == false) }}</span></div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Indicativo admissão</span>
                    <div class="value">{{ $fa->indicativo_admissao ?? '' }}</div>
                </td>
                <td>
                    <span class="label">Nº processo trabalhista</span>
                    <div class="value">{{ $fa->numero_processo_trabalhista ?? '' }}</div>
                </td>
                <td>
                    <span class="label">Categoria trabalhador</span>
                    <div class="value small">{{ $fa->categoria_trabalhador ?? '' }}</div>
                </td>
                <td>
                    <span class="label">Tipo contrato</span>
                    <div class="value small">{{ $fa->tipo_contrato_trabalho ?? '' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Natureza atividade</span>
                    <div class="value small">{{ $fa->natureza_atividade ?? '' }}</div>
                </td>
                <td>
                    <span class="label">Departamento</span>
                    <div class="value">{{ $fa->departamento ?? '' }}</div>
                </td>
                <td>
                    <span class="label">CBO</span>
                    <div class="value">{{ $fa->cbo ?? '' }}</div>
                </td>
                <td>
                    <span class="label">Descanso semanal</span>
                    <div class="value">{{ $fa->descanso_semanal ?? '' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Horas mês</span>
                    <div class="value">{{ $fa->horas_mes ?? '' }}</div>
                </td>
                <td>
                    <span class="label">Horas semana</span>
                    <div class="value">{{ $fa->horas_semana ?? '' }}</div>
                </td>
                <td>
                    <span class="label">Dep. salário família</span>
                    <div class="value">{{ $fa->dependentes_salario_familia ?? '' }}</div>
                </td>
                <td>
                    <span class="label">Dep. IRRF</span>
                    <div class="value">{{ $fa->dependentes_irrf ?? '' }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <span class="label">Descrição salário variável</span>
                    <div class="value">{{ $fa->salario_variavel_descricao ?? '' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-head">Observações e conferência</div>
        <table class="form">
            <tr>
                <td colspan="3">
                    <span class="label">Observações</span>
                    <div class="value">{{ $fa->observacoes ?? '' }}</div>
                </td>
                <td>
                    <span class="label">Ficha preenchida por</span>
                    <div class="value">{{ $fa->ficha_preenchida_por ?? '' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Documento interno gerado automaticamente pelo sistema
    </div>
</div>
</body>
</html>
