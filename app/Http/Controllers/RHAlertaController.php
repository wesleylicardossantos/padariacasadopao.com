<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class RHAlertaController extends Controller
{
    public function index()
    {
        $alertas = collect();

        if (Schema::hasTable('funcionarios_ficha_admissao')) {
            $fichas = \App\Models\FuncionarioFichaAdmissao::where('empresa_id', request()->empresa_id)->get();

            foreach ($fichas as $ficha) {
                if (!empty($ficha->cnh_validade)) {
                    $dias = now()->diffInDays(\Carbon\Carbon::parse($ficha->cnh_validade), false);
                    if ($dias <= 30) {
                        $alertas->push([
                            'tipo' => 'CNH',
                            'funcionario' => optional($ficha->funcionario)->nome ?? 'Funcionário',
                            'descricao' => 'CNH próxima do vencimento',
                            'data_ref' => \Carbon\Carbon::parse($ficha->cnh_validade)->format('d/m/Y'),
                            'dias' => $dias,
                            'gravidade' => $dias < 0 ? 'danger' : 'warning',
                        ]);
                    }
                }

                if (!empty($ficha->data_exame_admissional)) {
                    $diasAso = now()->diffInDays(\Carbon\Carbon::parse($ficha->data_exame_admissional)->addYear(), false);
                    if ($diasAso <= 30) {
                        $alertas->push([
                            'tipo' => 'ASO',
                            'funcionario' => optional($ficha->funcionario)->nome ?? 'Funcionário',
                            'descricao' => 'ASO próximo do vencimento',
                            'data_ref' => \Carbon\Carbon::parse($ficha->data_exame_admissional)->addYear()->format('d/m/Y'),
                            'dias' => $diasAso,
                            'gravidade' => $diasAso < 0 ? 'danger' : 'warning',
                        ]);
                    }
                }

                if (!empty($ficha->data_admissao)) {
                    $fimExperiencia = \Carbon\Carbon::parse($ficha->data_admissao)->addDays(90);
                    $diasExp = now()->diffInDays($fimExperiencia, false);
                    if ($diasExp <= 15) {
                        $alertas->push([
                            'tipo' => 'Experiência',
                            'funcionario' => optional($ficha->funcionario)->nome ?? 'Funcionário',
                            'descricao' => 'Contrato de experiência próximo do fim',
                            'data_ref' => $fimExperiencia->format('d/m/Y'),
                            'dias' => $diasExp,
                            'gravidade' => $diasExp < 0 ? 'danger' : 'info',
                        ]);
                    }
                }
            }
        }

        if (Schema::hasTable('rh_ferias')) {
            $ferias = \App\Models\RHFerias::with('funcionario')
                ->where('empresa_id', request()->empresa_id)
                ->whereIn('status', ['programada', 'pendente'])
                ->get();

            foreach ($ferias as $item) {
                if (!empty($item->data_inicio)) {
                    $diasFerias = now()->diffInDays(\Carbon\Carbon::parse($item->data_inicio), false);
                    if ($diasFerias <= 30) {
                        $alertas->push([
                            'tipo' => 'Férias',
                            'funcionario' => optional($item->funcionario)->nome ?? 'Funcionário',
                            'descricao' => 'Férias próximas do início',
                            'data_ref' => \Carbon\Carbon::parse($item->data_inicio)->format('d/m/Y'),
                            'dias' => $diasFerias,
                            'gravidade' => $diasFerias < 0 ? 'danger' : 'primary',
                        ]);
                    }
                }
            }
        }

        $alertas = $alertas->sortBy('dias')->values();

        return view('rh.alertas.index', compact('alertas'));
    }
}
