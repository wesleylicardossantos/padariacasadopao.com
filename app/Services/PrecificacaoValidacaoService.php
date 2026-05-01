<?php

namespace App\Services;

class PrecificacaoValidacaoService
{
    public function validar(array $dados): array
    {
        $custo = round((float) ($dados['custo_total'] ?? 0), 4);
        $preco = round((float) ($dados['preco_sugerido'] ?? 0), 4);
        $despesasPercentual = (float) ($dados['despesas_percentual'] ?? 0);
        $margemMinima = (float) ($dados['margem_minima'] ?? 30);
        $margemAlvo = (float) ($dados['margem_alvo'] ?? 40);
        $cmvMaximo = (float) ($dados['cmv_maximo'] ?? 40);
        $possuiReceita = (bool) ($dados['possui_receita'] ?? false);
        $possuiVinculo = (bool) ($dados['possui_vinculo'] ?? false);
        $insumosSemCusto = (int) ($dados['insumos_sem_custo'] ?? 0);

        $bloqueios = [];
        $alertas = [];

        if ($custo <= 0) {
            $bloqueios[] = 'Custo total inválido ou zerado.';
        }

        if ($preco <= 0) {
            $bloqueios[] = 'Preço sugerido inválido ou zerado.';
        }

        if (! $possuiReceita) {
            $bloqueios[] = 'Produto sem ficha técnica vinculada.';
        }

        if (! $possuiVinculo) {
            $bloqueios[] = 'Produto sem vínculo com o cadastro legado.';
        }

        if ($insumosSemCusto > 0) {
            $bloqueios[] = 'Existem insumos sem custo válido na ficha técnica.';
        }

        $precoMinimo = 0.0;
        if ($custo > 0) {
            $precoMinimo = $custo / max(0.0001, (1 - (($despesasPercentual + $margemMinima) / 100)));
        }

        $margemPercentual = 0.0;
        $cmvPercentual = 0.0;
        if ($preco > 0) {
            $margemPercentual = (($preco - $custo) / $preco) * 100;
            $cmvPercentual = ($custo / $preco) * 100;
        }

        if ($preco > 0 && $preco < $precoMinimo) {
            $bloqueios[] = 'Preço sugerido abaixo do preço mínimo operacional.';
        }

        if ($margemPercentual < $margemMinima && $preco > 0) {
            $bloqueios[] = 'Margem calculada abaixo da margem mínima configurada.';
        } elseif ($margemPercentual < $margemAlvo && $preco > 0) {
            $alertas[] = 'Margem abaixo da meta alvo. Recomendado revisar o preço.';
        }

        if ($cmvPercentual > $cmvMaximo && $preco > 0) {
            $alertas[] = 'CMV acima do máximo desejado.';
        }

        if (empty($bloqueios) && empty($alertas)) {
            $status = 'ok';
        } elseif (! empty($bloqueios)) {
            $status = 'bloqueado';
        } elseif ($margemPercentual < $margemAlvo) {
            $status = 'alerta';
        } else {
            $status = 'ok';
        }

        return [
            'status' => $status,
            'margem' => round($margemPercentual, 2),
            'cmv' => round($cmvPercentual, 2),
            'preco_minimo' => round($precoMinimo, 4),
            'bloqueios' => $bloqueios,
            'alertas' => $alertas,
        ];
    }
}
