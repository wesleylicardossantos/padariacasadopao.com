<?php

namespace App\Services\RH;

use App\Models\Empresa;
use App\Models\Funcionario;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RHDocumentoTemplateEngineService
{
    public function buildVariables(Funcionario $funcionario, array $extra = []): array
    {
        $empresa = null;
        if (!empty($funcionario->empresa_id)) {
            $empresa = Empresa::find($funcionario->empresa_id);
        }

        $ficha = null;
        if (Schema::hasTable('funcionarios_ficha_admissao') && method_exists($funcionario, 'fichaAdmissao')) {
            $ficha = $funcionario->fichaAdmissao;
        }

        $hoje = Carbon::now();
        $empresaMunicipio = (string) ($empresa?->cidade?->nome ?? '');
        $funcionarioMunicipio = (string) ($funcionario->cidade?->nome ?? '');

        $empresaNome = (string) ($empresa->razao_social ?? $empresa->nome_fantasia ?? 'Empresa');
        $empresaCnpj = $this->maskDocument($empresa->cpf_cnpj ?? '');
        $empresaEndereco = trim(collect([
            $empresa->rua ?? null,
            $empresa->numero ?? null,
            $empresa->bairro ?? null,
        ])->filter()->implode(', '));

        $funcionarioEndereco = trim(collect([
            $funcionario->rua ?? null,
            $funcionario->numero ?? null,
            $funcionario->bairro ?? null,
        ])->filter()->implode(', '));

        $dataAdmissao = $this->formatDate($ficha->data_admissao ?? $funcionario->data_registro ?? null);
        $dataRescisao = $this->formatDate($extra['data_rescisao'] ?? null);
        $salario = $this->money($funcionario->salario ?? 0);
        $tipoRescisao = (string) ($extra['tipo_rescisao'] ?? 'Sem informação');
        $motivo = (string) ($extra['motivo'] ?? 'Conforme política interna da empresa.');
        $tipoContratoInformado = (string) ($extra['tipo_contrato'] ?? $extra['tipo_contrato_label'] ?? 'indeterminado');
        $regimeInformado = (string) ($extra['regime'] ?? $extra['regime_trabalho'] ?? 'presencial');
        $tipoContratoKey = $this->inferTipoContratoKey($tipoContratoInformado);
        $regimeKey = $this->inferRegimeKey($regimeInformado);
        $tipoContrato = (string) ($extra['tipo_contrato_label'] ?? $this->humanizeTipoContrato($tipoContratoKey));
        $regimeTrabalho = (string) ($extra['regime_trabalho'] ?? $this->humanizeRegime($regimeKey));
        $periodicidade = (string) ($extra['periodicidade_pagamento'] ?? 'Mensal');
        $formaPagamento = (string) ($extra['forma_pagamento_documento'] ?? $extra['forma_pagamento'] ?? 'depósito bancário em conta de titularidade do(a) empregado(a)');
        $jornada = (string) ($extra['jornada_descricao'] ?? $extra['jornada'] ?? '44 (quarenta e quatro) horas semanais, com intervalos e descansos legais.');
        $prazoContrato = (string) ($extra['prazo_contrato_descricao'] ?? $extra['prazo'] ?? $this->defaultPrazoDescricao($tipoContratoKey, $dataAdmissao));
        $beneficios = (string) ($extra['beneficios_descricao'] ?? $extra['beneficios'] ?? 'Conforme política interna da empresa e instrumento coletivo aplicável.');
        $foroCidade = (string) ($extra['foro_cidade'] ?? $extra['foro'] ?? $empresaMunicipio ?: 'Não informado');
        $atividades = (string) ($extra['funcionario_atividades'] ?? $extra['atividades'] ?? 'Atividades inerentes à função e outras compatíveis com a condição pessoal do(a) empregado(a).');
        $empresaTipoPessoa = (string) ($extra['empresa_tipo_pessoa'] ?? (!empty($empresaCnpj) ? 'pessoa jurídica de direito privado' : 'empregador(a)'));
        $autorizaSindical = $this->normalizeSindicalAuthorization($extra['autoriza_contribuicao_sindical'] ?? null);
        $representanteLegal = (string) ($extra['empresa_representante_legal'] ?? $empresa->representante_legal ?? 'Não informado');
        $representanteCpf = $this->maskDocument((string) ($extra['empresa_representante_cpf'] ?? $empresa->cpf_representante_legal ?? ''));
        $nacionalidade = (string) ($extra['funcionario_nacionalidade'] ?? $ficha->nacionalidade ?? 'brasileiro(a)');
        $estadoCivil = (string) ($extra['funcionario_estado_civil'] ?? $ficha->estado_civil ?? 'estado civil não informado');
        $profissao = (string) ($extra['funcionario_profissao'] ?? ($funcionario->funcao ?? ($ficha->cargo ?? 'profissional')));
        $ctps = (string) ($extra['funcionario_ctps'] ?? $ficha->ctps_numero ?? '');
        $ctpsSerie = (string) ($extra['funcionario_ctps_serie'] ?? $ficha->ctps_serie ?? '');
        $localTrabalho = (string) ($extra['local_trabalho'] ?? $empresaNome);
        $banco = (string) ($extra['banco'] ?? $ficha->banco ?? '');
        $agencia = (string) ($extra['agencia'] ?? $ficha->agencia ?? '');
        $contaCorrente = (string) ($extra['conta_corrente'] ?? $extra['conta'] ?? $ficha->conta_salario ?? '');
        $confidencialidadeMulta = (string) ($extra['confidencialidade_multa'] ?? '');
        $pis = (string) ($extra['funcionario_pis'] ?? $ficha->pis_numero ?? '');
        $funcionarioNascimento = $this->formatDate($extra['funcionario_data_nascimento'] ?? $ficha->data_nascimento ?? null);
        $funcionarioMae = (string) ($extra['funcionario_mae'] ?? $ficha->nome_mae ?? '');
        $funcionarioCep = $this->maskCep($extra['funcionario_cep'] ?? '');
        if ($funcionarioCep === '') {
            $funcionarioCep = $this->maskCep($funcionario->cep ?? '');
        }
        $empresaCep = $this->maskCep($extra['empresa_cep'] ?? $empresa->cep ?? '');

        $receiptDate = $this->formatDate($extra['data_recebimento'] ?? $extra['data_pagamento_rescisao'] ?? $extra['data_rescisao'] ?? null) ?: $hoje->format('d/m/Y');
        $receiptLocal = (string) ($extra['local_recebimento'] ?? $empresaMunicipio ?: $foroCidade);

        $vars = [
            '{{empresa_nome}}' => $empresaNome,
            '{{empresa_razao_social}}' => $empresaNome,
            '{{empresa_cnpj}}' => $empresaCnpj,
            '{{empresa_endereco}}' => $empresaEndereco,
            '{{empresa_logradouro}}' => (string) ($empresa->rua ?? ''),
            '{{empresa_numero}}' => (string) ($empresa->numero ?? ''),
            '{{empresa_bairro}}' => (string) ($empresa->bairro ?? ''),
            '{{empresa_municipio}}' => $empresaMunicipio,
            '{{empresa_uf}}' => (string) ($empresa->uf ?? ''),
            '{{empresa_cep}}' => $empresaCep,
            '{{empresa_tipo_pessoa}}' => $empresaTipoPessoa,
            '{{empresa_representante_legal}}' => $representanteLegal,
            '{{empresa_representante_cpf}}' => $representanteCpf,
            '{{funcionario_nome}}' => (string) ($funcionario->nome ?? ''),
            '{{funcionario_cpf}}' => $this->maskDocument((string) ($funcionario->cpf ?? '')),
            '{{funcionario_rg}}' => (string) ($funcionario->rg ?? ''),
            '{{funcionario_cargo}}' => (string) ($funcionario->funcao ?? ($ficha->cargo ?? 'Colaborador')),
            '{{funcionario_salario}}' => $salario,
            '{{funcionario_data_admissao}}' => $dataAdmissao,
            '{{funcionario_telefone}}' => (string) ($funcionario->telefone ?? $funcionario->celular ?? ''),
            '{{funcionario_email}}' => (string) ($funcionario->email ?? ''),
            '{{funcionario_endereco}}' => $funcionarioEndereco,
            '{{funcionario_logradouro}}' => (string) ($funcionario->rua ?? ''),
            '{{funcionario_numero}}' => (string) ($funcionario->numero ?? ''),
            '{{funcionario_bairro}}' => (string) ($funcionario->bairro ?? ''),
            '{{funcionario_municipio}}' => $funcionarioMunicipio,
            '{{funcionario_uf}}' => (string) ($funcionario->cidade?->uf ?? $empresa->uf ?? ''),
            '{{funcionario_cep}}' => $funcionarioCep,
            '{{funcionario_pis}}' => $pis,
            '{{funcionario_nacionalidade}}' => $nacionalidade,
            '{{funcionario_estado_civil}}' => $estadoCivil,
            '{{funcionario_profissao}}' => $profissao,
            '{{funcionario_ctps}}' => $ctps,
            '{{funcionario_ctps_serie}}' => $ctpsSerie,
            '{{funcionario_data_nascimento}}' => $funcionarioNascimento,
            '{{funcionario_mae}}' => $funcionarioMae,
            '{{funcionario_atividades}}' => $atividades,
            '{{data_hoje}}' => $hoje->format('d/m/Y'),
            '{{data_hoje_extenso}}' => $this->formatDateLongPtBr($hoje),
            '{{mes_atual}}' => $this->monthNamePtBr((int) $hoje->format('n')),
            '{{ano_atual}}' => $hoje->format('Y'),
            '{{tipo_rescisao}}' => $tipoRescisao,
            '{{motivo_documento}}' => $motivo,
            '{{data_rescisao}}' => $dataRescisao,
            '{{data_recebimento}}' => $receiptDate,
            '{{local_recebimento}}' => $receiptLocal,
            '{{usuario_responsavel}}' => (string) (auth()->user()->nome ?? auth()->user()->name ?? 'Responsável RH'),
            '{{observacoes_adicionais}}' => (string) ($extra['observacoes'] ?? ''),
            '{{tipo_contrato_label}}' => $tipoContrato,
            '{{tipo_contrato_chave}}' => $tipoContratoKey,
            '{{regime_trabalho}}' => $regimeTrabalho,
            '{{regime_trabalho_chave}}' => $regimeKey,
            '{{periodicidade_pagamento}}' => $periodicidade,
            '{{forma_pagamento_documento}}' => $formaPagamento,
            '{{banco}}' => $banco,
            '{{agencia}}' => $agencia,
            '{{conta_corrente}}' => $contaCorrente,
            '{{beneficios_descricao}}' => $beneficios,
            '{{autoriza_contribuicao_sindical}}' => $autorizaSindical,
            '{{jornada_descricao}}' => $jornada,
            '{{prazo_contrato_descricao}}' => $prazoContrato,
            '{{confidencialidade_multa}}' => $confidencialidadeMulta,
            '{{foro_cidade}}' => $foroCidade,
            '{{local_trabalho}}' => $localTrabalho,
        ];

        foreach ($extra as $key => $value) {
            $placeholder = '{{' . trim((string) $key) . '}}';
            if (!array_key_exists($placeholder, $vars)) {
                $vars[$placeholder] = is_scalar($value) ? (string) $value : '';
            }
        }

        return $vars;
    }

    public function render(string $content, array $variables): string
    {
        $content = $this->processConditionalBlocks($content, $variables);
        $content = str_replace(array_keys($variables), array_values($variables), $content);

        preg_match_all('/{{\s*[^}]+\s*}}/', $content, $matches);
        $missing = array_values(array_unique($matches[0] ?? []));
        if (!empty($missing)) {
            Log::warning('RH documento com placeholders não preenchidos.', [
                'missing_placeholders' => $missing,
            ]);
        }

        $content = preg_replace('/{{\s*[^}]+\s*}}/', '', $content);
        $content = preg_replace("/
{3,}/", "

", $content);

        return $this->cleanupRenderedHtml($content);
    }

    private function processConditionalBlocks(string $content, array $variables): string
    {
        $tipoContrato = Str::lower(trim((string) ($variables['{{tipo_contrato_chave}}'] ?? 'indeterminado')));
        $regimeTrabalho = Str::lower(trim((string) ($variables['{{regime_trabalho_chave}}'] ?? 'presencial')));

        $flags = [
            'indeterminado' => $tipoContrato === 'indeterminado',
            'determinado' => $tipoContrato === 'determinado',
            'intermitente' => $tipoContrato === 'intermitente',
            'presencial' => $regimeTrabalho === 'presencial',
            'teletrabalho' => $regimeTrabalho === 'teletrabalho',
        ];

        foreach ($flags as $block => $enabled) {
            $pattern = '/{{#' . preg_quote($block, '/') . '}}(.*?){{\/' . preg_quote($block, '/') . '}}/si';
            $content = preg_replace($pattern, $enabled ? '$1' : '', $content);
        }

        return $content;
    }

    private function cleanupRenderedHtml(string $content): string
    {
        $content = str_replace(['Documento RH', 'Documento gerado pelo módulo RH'], '', $content);
        $content = preg_replace('/<p>\s*<\/p>/i', '', $content);
        $content = preg_replace('/<div[^>]*>\s*<\/div>/i', '', $content);
        $content = preg_replace('/\s{2,}/', ' ', $content);

        return trim($content);
    }

    private function normalizeSindicalAuthorization($value): string
    {
        if ($value === null || $value === '') {
            return 'não';
        }

        $normalized = mb_strtolower(trim((string) $value));
        return in_array($normalized, ['1', 'sim', 's', 'true', 'autoriza'], true) ? '' : 'não';
    }

    private function inferTipoContratoKey(string $tipoContrato): string
    {
        $value = Str::lower(trim($tipoContrato));
        if (str_contains($value, 'intermit')) {
            return 'intermitente';
        }
        if (str_contains($value, 'determin')) {
            return 'determinado';
        }
        return 'indeterminado';
    }

    private function inferRegimeKey(string $regimeTrabalho): string
    {
        $value = Str::lower(trim($regimeTrabalho));
        if (str_contains($value, 'tele') || str_contains($value, 'remot') || str_contains($value, 'home') || str_contains($value, 'híbr') || str_contains($value, 'hibr')) {
            return 'teletrabalho';
        }

        return 'presencial';
    }

    private function humanizeTipoContrato(string $tipoContratoKey): string
    {
        return match ($tipoContratoKey) {
            'determinado' => 'por prazo determinado',
            'intermitente' => 'intermitente',
            default => 'por prazo indeterminado',
        };
    }

    private function humanizeRegime(string $regimeKey): string
    {
        return $regimeKey === 'teletrabalho' ? 'Teletrabalho' : 'Presencial';
    }

    private function defaultPrazoDescricao(string $tipoContratoKey, string $dataAdmissao): string
    {
        return match ($tipoContratoKey) {
            'determinado' => 'O presente contrato terá duração conforme estipulado entre as partes, iniciando-se em ' . ($dataAdmissao ?: 'data a definir') . '.',
            'intermitente' => 'A prestação ocorrerá de forma não contínua, com alternância entre períodos de trabalho e inatividade, mediante convocação prévia.',
            default => 'O presente contrato é válido por prazo indeterminado, iniciando-se em ' . ($dataAdmissao ?: 'data a definir') . '.',
        };
    }

    private function formatDate($value): string
    {
        if (empty($value)) {
            return '';
        }

        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }


    private function formatDateLongPtBr($date): string
    {
        if (empty($date)) {
            return '';
        }

        try {
            $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
            return $carbon->format('d') . ' de ' . $this->monthNamePtBr((int) $carbon->format('n')) . ' de ' . $carbon->format('Y');
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function monthNamePtBr(int $month): string
    {
        return [
            1 => 'janeiro',
            2 => 'fevereiro',
            3 => 'março',
            4 => 'abril',
            5 => 'maio',
            6 => 'junho',
            7 => 'julho',
            8 => 'agosto',
            9 => 'setembro',
            10 => 'outubro',
            11 => 'novembro',
            12 => 'dezembro',
        ][$month] ?? '';
    }
    private function money($value): string
    {
        return number_format((float) $value, 2, ',', '.');
    }

    private function maskDocument(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value);

        if (strlen($digits) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits) ?: $value;
        }

        if (strlen($digits) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digits) ?: $value;
        }

        return $value;
    }

    private function maskCep(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value);
        if (strlen($digits) !== 8) {
            return trim($value);
        }

        return preg_replace('/(\d{2})(\d{3})(\d{3})/', '$1.$2-$3', $digits) ?: $value;
    }
}
