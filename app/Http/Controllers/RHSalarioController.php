<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use App\Models\RHMovimentacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Support\Tenancy\TenantContext;

class RHSalarioController extends Controller
{
    public function index(Request $request)
    {
        $nome = trim((string) $request->nome);
        $empresaId = TenantContext::empresaId($request);

        $query = Funcionario::query();

        if ($empresaId > 0 && Schema::hasColumn('funcionarios', 'empresa_id')) {
            $query->where('empresa_id', $empresaId);
        }

        $data = $query
            ->when($nome !== '', function ($q) use ($nome) {
                return $q->where('nome', 'like', "%{$nome}%");
            })
            ->orderBy('nome')
            ->paginate(env("PAGINACAO"));

        return view('rh.salarios.index', compact('data', 'nome'));
    }

    public function create(Request $request)
    {
        $empresaId = TenantContext::empresaId($request);

        $query = Funcionario::query();

        if ($empresaId > 0 && Schema::hasColumn('funcionarios', 'empresa_id')) {
            $query->where('empresa_id', $empresaId);
        }

        $funcionarios = $query
            ->orderBy('nome')
            ->get();

        return view('rh.salarios.create', compact('funcionarios'));
    }

    public function store(Request $request)
    {
        $empresaId = TenantContext::empresaId($request);

        if (!Schema::hasTable('rh_movimentacoes')) {
            session()->flash('flash_erro', 'Tabela RH não instalada. Execute o SQL do patch RH V2.');
            return redirect()->route('rh.salarios.index');
        }

        $request->validate([
            'funcionario_id' => 'required',
            'salario_novo' => 'required',
            'descricao' => 'required|max:255',
            'data_movimentacao' => 'required|date',
        ], [
            'funcionario_id.required' => 'Selecione o funcionário.',
            'salario_novo.required' => 'Informe o novo salário.',
            'descricao.required' => 'Informe a descrição.',
            'data_movimentacao.required' => 'Informe a data.',
        ]);

        $funcionarioQuery = Funcionario::query();
        if ($empresaId > 0 && Schema::hasColumn('funcionarios', 'empresa_id')) {
            $funcionarioQuery->where('empresa_id', $empresaId);
        }

        $funcionario = $funcionarioQuery->findOrFail($request->funcionario_id);

        try {
            DB::transaction(function () use ($request, $funcionario, $empresaId) {
                $salarioAnterior = (float) $funcionario->salario;
                $salarioNovo = __convert_value_bd($request->salario_novo);

                RHMovimentacao::create([
                    'empresa_id' => $empresaId > 0 ? $empresaId : ($funcionario->empresa_id ?? null),
                    'funcionario_id' => $funcionario->id,
                    'tipo' => 'salario',
                    'descricao' => $request->descricao,
                    'cargo_anterior' => $funcionario->funcao,
                    'cargo_novo' => $funcionario->funcao,
                    'valor_anterior' => $salarioAnterior,
                    'valor_novo' => $salarioNovo,
                    'data_movimentacao' => $request->data_movimentacao,
                    'status_gerado' => $funcionario->ativo === 0 ? 'inativo' : 'ativo',
                    'usuario_id' => auth()->id() ?? null,
                ]);

                $funcionario->salario = $salarioNovo;
                $funcionario->save();
            });

            session()->flash('flash_sucesso', 'Reajuste salarial registrado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Erro ao registrar reajuste: ' . $e->getMessage());
            __saveLogError($e, $empresaId);
        }

        return redirect()->route('rh.salarios.index');
    }
}
