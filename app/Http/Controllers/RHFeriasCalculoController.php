<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use App\Models\RHFerias;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class RHFeriasCalculoController extends Controller
{
    public function index()
    {
        $data = Funcionario::where('empresa_id', request()->empresa_id)
            ->orderBy('nome')
            ->get()
            ->map(function($item){
                $ficha = null;
                if (Schema::hasTable('funcionarios_ficha_admissao')) {
                    $ficha = \App\Models\FuncionarioFichaAdmissao::where('funcionario_id', $item->id)->first();
                }

                $dataAdmissao = $this->resolveAdmissionDate($item, $ficha);

                $avos = 0;
                $periodoInicio = null;
                $periodoFim = null;

                if ($dataAdmissao) {
                    $periodoInicio = $dataAdmissao->copy();
                    $periodoFim = $dataAdmissao->copy()->addYear()->subDay();
                    $meses = $dataAdmissao->diffInMonths(now());
                    $avos = min(12, max(0, $meses));
                }

                $ultimaFerias = null;
                if (Schema::hasTable('rh_ferias')) {
                    $ultimaFerias = RHFerias::where('empresa_id', request()->empresa_id)
                        ->where('funcionario_id', $item->id)
                        ->orderBy('data_inicio', 'desc')
                        ->first();
                }

                return [
                    'funcionario' => $item,
                    'data_admissao' => $dataAdmissao,
                    'periodo_inicio' => $periodoInicio,
                    'periodo_fim' => $periodoFim,
                    'avos' => $avos,
                    'ultima_ferias' => $ultimaFerias,
                ];
            });

        return view('rh.ferias.calculo', compact('data'));
    }

    private function resolveAdmissionDate(Funcionario $funcionario, $ficha = null): ?Carbon
    {
        $candidatos = [
            $ficha?->data_admissao ?? null,
            $funcionario->data_admissao ?? null,
            $funcionario->admissao ?? null,
            $funcionario->dt_admissao ?? null,
            $funcionario->admission_date ?? null,
            $funcionario->data_registro ?? null,
            $funcionario->created_at ?? null,
        ];

        foreach ($candidatos as $valor) {
            if (empty($valor)) {
                continue;
            }

            try {
                return $valor instanceof Carbon ? $valor->copy() : Carbon::parse($valor);
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }

}
