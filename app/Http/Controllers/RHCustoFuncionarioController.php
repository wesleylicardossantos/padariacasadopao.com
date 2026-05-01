<?php
namespace App\Http\Controllers;

use App\Models\Funcionario;
use App\Services\RHFolhaCalculoService;
use Illuminate\Http\Request;
use App\Modules\RH\Support\RHContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class RHCustoFuncionarioController extends Controller
{
    public function __construct(private RHFolhaCalculoService $folhaCalculo)
    {
    }

    public function index(Request $request)
    {
        $empresaId = $this->resolveEmpresaId($request);
        $mes = max(1, min(12, (int) ($request->mes ?: date('m'))));
        $ano = (int) ($request->ano ?: date('Y'));

        $encargosPerc = $this->toFloat($request->encargos, 28);
        $beneficiosPerc = $this->toFloat($request->beneficios, 8);

        $funcionarios = $this->buscarFuncionarios($empresaId);
        $linhas = [];
        $totalFolha = 0.0;

        foreach ($funcionarios as $f) {
            $valores = $this->safeCalcularFuncionario($f, $mes, $ano);
            $bruto = round((float) ($valores['proventos'] ?? (($f->salario ?? 0) + ($valores['eventos'] ?? 0))), 2);
            $descontos = round((float) ($valores['descontos'] ?? 0), 2);
            $encargos = round($bruto * ($encargosPerc/100), 2);
            $beneficios = round($bruto * ($beneficiosPerc/100), 2);
            $custo = round($bruto + $encargos + $beneficios - $descontos, 2);

            $totalFolha += $custo;

            $linhas[] = [
                'funcionario'=>$f,
                'base'=>(float) ($f->salario ?? 0),
                'eventos'=>(float) ($valores['eventos'] ?? 0),
                'descontos'=>$descontos,
                'encargos'=>$encargos,
                'beneficios'=>$beneficios,
                'custo'=>$custo
            ];
        }

        usort($linhas, fn($a,$b) => $b['custo'] <=> $a['custo']);

        return view('rh.custo_funcionario.index', compact('mes','ano','encargosPerc','beneficiosPerc','linhas','totalFolha'));
    }

    private function resolveEmpresaId(Request $request): int
    {
        $candidatos = [
            $request->get('empresa_id'),
            $request->get('empresa_id'),
            data_get(session('empresa_selecionada'), 'empresa_id'),
            optional(Auth::user())->empresa_id,
            session('empresa_id'),
            data_get(session('user_logged'), 'empresa'),
            data_get(session('user_logged'), 'empresa_id'),
            data_get(session('usuario'), 'empresa'),
            data_get(session('usuario'), 'empresa_id'),
        ];

        foreach ($candidatos as $candidato) {
            if (is_numeric($candidato) && (int) $candidato > 0) {
                return (int) $candidato;
            }
        }

        return 0;
    }

    private function buscarFuncionarios(int $empresaId): Collection
    {
        if (!Schema::hasTable('funcionarios')) {
            return collect();
        }

        $query = Funcionario::query();
        if ($empresaId > 0 && Schema::hasColumn('funcionarios', 'empresa_id')) {
            $query->where('empresa_id', $empresaId);
        }

        if (Schema::hasColumn('funcionarios', 'ativo')) {
            $query->where(function($q){
                $q->whereNull('ativo')
                    ->orWhere('ativo',1)
                    ->orWhere('ativo',true)
                    ->orWhere('ativo','1')
                    ->orWhereRaw('LOWER(CAST(ativo AS CHAR)) in (?, ?)', ['s', 'sim']);
            });
        }

        $funcionarios = $query->orderBy('nome')->get();

        if ($funcionarios->isEmpty() && $empresaId > 0) {
            $fallback = Funcionario::query();
            if (Schema::hasColumn('funcionarios', 'empresa_id')) {
                $fallback->where('empresa_id', $empresaId);
            }
            $funcionarios = $fallback->orderBy('nome')->get();
        }

        return $funcionarios;
    }

    private function safeCalcularFuncionario($funcionario, int $mes, int $ano): array
    {
        try {
            $valores = $this->folhaCalculo->calcularFuncionario($funcionario, $mes, $ano);
            return is_array($valores) ? $valores : [];
        } catch (\Throwable $e) {
            return [
                'proventos' => round((float) ($funcionario->salario ?? 0), 2),
                'descontos' => 0.0,
                'eventos' => 0.0,
            ];
        }
    }

    private function toFloat($value, float $default): float
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return (float) str_replace(',', '.', (string) $value);
    }
}
