<?php

namespace App\Modules\RH\Support;

use App\Models\Empresa;
use App\Models\RHRescisao;
use Carbon\Carbon;

class RescisaoPdfDataBuilder
{
    public function build(Empresa $empresa, RHRescisao $rescisao, string $documentType): array
    {
        $funcionario = $rescisao->funcionario;
        $ficha = $funcionario?->fichaAdmissao;
        $desligamento = $rescisao->desligamento;

        $empresaNome = $this->firstFilled([
            $empresa->razao_social ?? null,
            $empresa->nome_fantasia ?? null,
            $empresa->nome ?? null,
        ]);

        $empresaDocumento = $this->firstFilled([
            $empresa->cpf_cnpj ?? null,
            $empresa->cnpj ?? null,
        ]);

        $empresaEndereco = trim(implode(' ', array_filter([
            $empresa->rua ?? null,
            $empresa->numero ?? null,
        ])));

        $empresaMunicipio = $this->firstFilled([
            data_get($empresa, 'cidade.nome'),
            $empresa->municipio ?? null,
            $empresa->cidade ?? null,
        ]);

        $funcionarioEndereco = $this->firstFilled([
            $ficha->endereco ?? null,
            trim(implode(' ', array_filter([
                $funcionario->rua ?? null,
                $funcionario->numero ?? null,
            ]))),
        ]);

        $funcionarioMunicipio = $this->firstFilled([
            data_get($funcionario, 'cidade.nome'),
            $funcionario->municipio ?? null,
        ]);

        $dataAdmissao = $this->firstDate([
            $rescisao->data_admissao,
            $ficha->data_admissao ?? null,
            $funcionario->data_admissao ?? null,
            $funcionario->data_registro ?? null,
        ]);

        $dataRescisao = $this->firstDate([
            $rescisao->data_rescisao,
            $desligamento->data_desligamento ?? null,
        ]);

        $dataAviso = $this->firstDate([
            $desligamento->data_aviso_previo ?? null,
            $rescisao->data_aviso_previo ?? null,
        ]);

        $localRecebimento = trim(implode(' / ', array_filter([
            $empresaMunicipio,
            $empresa->uf ?? null,
        ])));

        $textoHomologacao = $this->firstFilled([
            $rescisao->texto_homologacao ?? null,
            'Foi prestada gratuitamente assistência ao trabalhador nos termos do art. 477, § 1º da Consolidação das Leis do Trabalho - CLT, sendo comprovado neste ato o efetivo pagamento das verbas rescisórias acima especificadas.',
        ]);

        $verbas = $this->mapVerbas($rescisao, $dataAdmissao, $dataRescisao);
        $titulo = $this->resolveTitle($documentType);
        $nomeArquivo = $this->resolveFilename($documentType, $rescisao->id, $funcionario->nome ?? 'funcionario');

        return [
            'documentType' => $documentType,
            'documentTitle' => $titulo,
            'fileName' => $nomeArquivo,
            'empresa' => $empresa,
            'funcionario' => $funcionario,
            'ficha' => $ficha,
            'desligamento' => $desligamento,
            'rescisao' => $rescisao,
            'empresaDocumento' => $empresaDocumento,
            'empresaNome' => $empresaNome,
            'empresaEndereco' => $empresaEndereco,
            'empresaBairro' => $empresa->bairro ?? '',
            'empresaMunicipio' => $empresaMunicipio,
            'empresaUf' => $empresa->uf ?? '',
            'empresaCep' => $empresa->cep ?? '',
            'empresaCnae' => $empresa->cnae ?? '',
            'empresaTomadorDocumento' => $empresa->cnpj_tomador_obra ?? '',
            'funcionarioPis' => $this->firstFilled([$ficha->pis_pasep ?? null, $funcionario->pis ?? null]),
            'funcionarioNome' => $funcionario->nome ?? '',
            'funcionarioEndereco' => $funcionarioEndereco,
            'funcionarioBairro' => $this->firstFilled([$ficha->bairro ?? null, $funcionario->bairro ?? null]),
            'funcionarioMunicipio' => $funcionarioMunicipio,
            'funcionarioUf' => $this->firstFilled([$ficha->uf ?? null, $funcionario->uf ?? null, $empresa->uf ?? null]),
            'funcionarioCep' => $this->firstFilled([$ficha->cep ?? null, $funcionario->cep ?? null]),
            'funcionarioCtps' => $this->formatCtps($ficha, $funcionario),
            'funcionarioCpf' => $funcionario->cpf ?? '',
            'funcionarioNascimento' => $this->firstDate([$ficha->data_nascimento ?? null, $funcionario->data_nascimento ?? null]),
            'funcionarioNomeMae' => $this->firstFilled([$ficha->nome_mae ?? null, $funcionario->nome_mae ?? null]),
            'remuneracaoRescisoria' => $this->money($this->resolveRemuneracao($rescisao, $funcionario)),
            'dataAdmissao' => $dataAdmissao,
            'dataAviso' => $dataAviso,
            'dataRescisao' => $dataRescisao,
            'causaAfastamento' => strtoupper((string) ($rescisao->motivo ?? '')),
            'codigoAfastamento' => $rescisao->codigo_afastamento ?? '',
            'pensaoPercentual' => $rescisao->pensao_percentual ?? '',
            'categoriaTrabalhador' => $this->firstFilled([$funcionario->funcao ?? null, $funcionario->cargo ?? null]),
            'verbas' => $verbas,
            'totalBruto' => $this->money($rescisao->total_bruto),
            'totalDeducoes' => $this->money($rescisao->total_descontos),
            'liquidoReceber' => $this->money($rescisao->total_liquido),
            'localRecebimento' => $localRecebimento,
            'dataRecebimento' => $dataRescisao,
            'textoHomologacao' => $textoHomologacao,
            'orgaoHomologador' => $rescisao->orgao_homologador ?? '',
            'observacoes' => $rescisao->observacoes ?? '',
        ];
    }

    private function mapVerbas(RHRescisao $rescisao, string $dataAdmissao, string $dataRescisao): array
    {
        $diasSaldo = $this->calcularDiasSaldo($dataRescisao);
        $avosDecimo = $this->calcularAvosDecimo($dataRescisao);
        $avosFerias = $this->calcularAvosFerias($dataAdmissao, $dataRescisao);
        $referenciaFgts = $this->montarReferenciaFgts($dataAdmissao, $dataRescisao);

        $left = [
            ['codigo' => '29', 'label' => 'Aviso Prévio Indenizado', 'valor' => $this->extractMoney($rescisao, ['aviso_previo'])],
            ['codigo' => '30', 'label' => 'Saldo de Salário', 'valor' => $this->extractMoney($rescisao, ['saldo_salario']), 'referencia' => $this->findReference($rescisao, ['saldo', 'salário']) ?: $diasSaldo],
            ['codigo' => '31', 'label' => '13º Salário', 'valor' => $this->findItemValue($rescisao, ['13', 'decimo']) ?: $this->extractMoney($rescisao, ['decimo_terceiro']), 'referencia' => $this->findReference($rescisao, ['13', 'decimo']) ?: $avosDecimo],
            ['codigo' => '32', 'label' => '13º Salário Indenizado', 'valor' => $this->findItemValue($rescisao, ['indenizado', '13']), 'referencia' => $this->findReference($rescisao, ['indenizado', '13']) ?: ($avosDecimo ? '1/12 avos' : null)],
            ['codigo' => '33', 'label' => 'Férias Vencidas', 'valor' => $this->extractMoney($rescisao, ['ferias_vencidas'])],
            ['codigo' => '34', 'label' => 'Férias Proporcionais', 'valor' => $this->extractMoney($rescisao, ['ferias_proporcionais']), 'referencia' => $this->findReference($rescisao, ['ferias', 'propor']) ?: $avosFerias],
            ['codigo' => '35', 'label' => '1/3 Salário s/ Férias', 'valor' => $this->findItemValue($rescisao, ['1/3', 'venc'])],
            ['codigo' => '41', 'label' => 'Adicional de Insalub./Periculosidade', 'valor' => $this->findItemValue($rescisao, ['insalub', 'pericul'])],
            ['codigo' => '43', 'label' => '1/3 Férias', 'valor' => $this->extractMoney($rescisao, ['terco_ferias'])],
            ['codigo' => '44', 'label' => 'FGTS', 'valor' => $this->extractMoney($rescisao, ['fgts_deposito']), 'referencia' => $referenciaFgts],
            ['codigo' => '46', 'label' => '40% FGTS', 'valor' => $this->extractMoney($rescisao, ['fgts_multa'])],
        ];

        $right = [
            ['codigo' => '47', 'label' => 'Previdência', 'valor' => $this->extractMoney($rescisao, ['inss'])],
            ['codigo' => '48', 'label' => 'Previdência 13º Salário', 'valor' => $this->findItemValue($rescisao, ['inss', '13'])],
            ['codigo' => '49', 'label' => 'Adiantamento', 'valor' => $this->findItemValue($rescisao, ['adiant'])],
            ['codigo' => '50', 'label' => 'IRRF', 'valor' => $this->extractMoney($rescisao, ['irrf'])],
            ['codigo' => '51', 'label' => '', 'valor' => null],
            ['codigo' => '52', 'label' => '', 'valor' => null],
            ['codigo' => '53', 'label' => '', 'valor' => null],
            ['codigo' => '54', 'label' => 'Total das Deduções', 'valor' => $this->extractMoney($rescisao, ['total_descontos'])],
            ['codigo' => '55', 'label' => 'Líquido a Receber', 'valor' => $this->extractMoney($rescisao, ['total_liquido'])],
            ['codigo' => '', 'label' => '', 'valor' => null],
            ['codigo' => '40', 'label' => 'Total Bruto', 'valor' => $this->extractMoney($rescisao, ['total_bruto'])],
        ];

        return ['left' => $left, 'right' => $right];
    }

    private function resolveTitle(string $documentType): string
    {
        return match ($documentType) {
            'tqrct' => 'TERMO DE QUITAÇÃO DE RESCISÃO DO CONTRATO DE TRABALHO',
            'homologacao' => 'TERMO DE HOMOLOGAÇÃO DA RESCISÃO DO CONTRATO DE TRABALHO',
            default => 'TERMO DE RESCISÃO DO CONTRATO DE TRABALHO',
        };
    }

    private function resolveFilename(string $documentType, int $id, string $nome): string
    {
        $slug = preg_replace('/[^a-z0-9]+/i', '-', trim($nome)) ?: 'funcionario';
        return strtolower($documentType . '-' . $id . '-' . trim($slug, '-')) . '.pdf';
    }

    private function resolveRemuneracao(RHRescisao $rescisao, $funcionario): ?float
    {
        return $this->firstFilled([
            $rescisao->remuneracao_fins_rescisorios ?? null,
            $funcionario->salario ?? null,
            ($rescisao->total_bruto ?? 0) > 0 ? $rescisao->total_bruto : null,
        ]);
    }



    private function calcularDiasSaldo(string $dataRescisao): ?string
    {
        if ($dataRescisao === '') {
            return null;
        }

        try {
            return ((int) Carbon::createFromFormat('d/m/Y', $dataRescisao)->day) . ' dias';
        } catch (\Throwable) {
            return null;
        }
    }

    private function calcularAvosDecimo(string $dataRescisao): ?string
    {
        if ($dataRescisao === '') {
            return null;
        }

        try {
            $mes = (int) Carbon::createFromFormat('d/m/Y', $dataRescisao)->month;
            return max($mes - 1, 0) . '/12 avos';
        } catch (\Throwable) {
            return null;
        }
    }

    private function calcularAvosFerias(string $dataAdmissao, string $dataRescisao): ?string
    {
        if ($dataAdmissao === '' || $dataRescisao === '') {
            return null;
        }

        try {
            $admissao = Carbon::createFromFormat('d/m/Y', $dataAdmissao);
            $rescisao = Carbon::createFromFormat('d/m/Y', $dataRescisao);

            $inicioPeriodo = $admissao->copy()->year($rescisao->year);
            if ($inicioPeriodo->greaterThan($rescisao)) {
                $inicioPeriodo->subYear();
            }

            $meses = (($rescisao->year - $inicioPeriodo->year) * 12) + ($rescisao->month - $inicioPeriodo->month);
            if ((int) $rescisao->day >= 15) {
                $meses++;
            }

            $meses = max(min($meses, 12), 0);
            return $meses . '/12 avos';
        } catch (\Throwable) {
            return null;
        }
    }

    private function montarReferenciaFgts(string $dataAdmissao, string $dataRescisao): ?string
    {
        if ($dataAdmissao === '' || $dataRescisao === '') {
            return null;
        }

        try {
            $admissao = Carbon::createFromFormat('d/m/Y', $dataAdmissao);
            $rescisao = Carbon::createFromFormat('d/m/Y', $dataRescisao);

            $meses = (($rescisao->year - $admissao->year) * 12) + ($rescisao->month - $admissao->month);
            $dias = max((int) $rescisao->day, 0);

            $partes = [];
            if ($meses > 0) {
                $partes[] = $meses . ' ' . ($meses === 1 ? 'mês' : 'meses');
            }
            if ($dias > 0) {
                $partes[] = $dias . ' dias';
            }

            return $partes !== [] ? implode(' / ', $partes) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function findItemValue(RHRescisao $rescisao, array $needles): ?float
    {
        foreach ($rescisao->itens ?? [] as $item) {
            $haystack = strtolower($item->descricao . ' ' . $item->codigo . ' ' . $item->tipo);
            $ok = true;
            foreach ($needles as $needle) {
                if (!str_contains($haystack, strtolower((string) $needle))) {
                    $ok = false;
                    break;
                }
            }
            if ($ok) {
                return (float) $item->valor;
            }
        }
        return null;
    }

    private function findReference(RHRescisao $rescisao, array $needles): ?string
    {
        foreach ($rescisao->itens ?? [] as $item) {
            $haystack = strtolower($item->descricao . ' ' . $item->codigo . ' ' . $item->tipo);
            $ok = true;
            foreach ($needles as $needle) {
                if (!str_contains($haystack, strtolower((string) $needle))) {
                    $ok = false;
                    break;
                }
            }
            if ($ok && $item->referencia !== null) {
                $ref = (float) $item->referencia;
                return rtrim(rtrim(number_format($ref, 2, ',', '.'), '0'), ',');
            }
        }
        return null;
    }

    private function extractMoney(RHRescisao $rescisao, array $fields): ?float
    {
        foreach ($fields as $field) {
            $value = data_get($rescisao, $field);
            if ($value !== null && $value !== '') {
                return (float) $value;
            }
        }
        return null;
    }

    private function money($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        return number_format((float) $value, 2, ',', '.');
    }

    private function firstFilled(array $values)
    {
        foreach ($values as $value) {
            if ($value !== null && $value !== '') {
                return $value;
            }
        }
        return '';
    }

    private function firstDate(array $values): string
    {
        foreach ($values as $value) {
            if (empty($value)) {
                continue;
            }
            try {
                return Carbon::parse($value)->format('d/m/Y');
            } catch (\Throwable $e) {
                if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', (string) $value)) {
                    return (string) $value;
                }
            }
        }
        return '';
    }

    private function formatCtps($ficha, $funcionario): string
    {
        $parts = array_filter([
            $this->firstFilled([$ficha->ctps_numero ?? null, $funcionario->ctps_numero ?? null]),
            $this->firstFilled([$ficha->ctps_serie ?? null, $funcionario->ctps_serie ?? null]),
            $this->firstFilled([$ficha->ctps_uf ?? null, $funcionario->ctps_uf ?? null]),
        ]);

        return implode(' / ', $parts);
    }
}
