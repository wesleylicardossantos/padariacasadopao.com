<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use App\Models\RHMovimentacao;
use App\Modules\RH\Support\Concerns\InteractsWithRH;
use App\Services\RHFolhaLockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RHMovimentacaoController extends Controller
{
    use InteractsWithRH;

    public function index(Request $request)
    {
        if (!Schema::hasTable('rh_movimentacoes')) {
            session()->flash('flash_erro', 'Tabela RH não instalada. Execute o SQL do patch RH V2.');
            return view('rh.movimentacoes.index', [
                'data' => collect(),
                'nome' => $request->nome,
                'tipo' => $request->tipo,
                'semTabela' => true,
            ]);
        }

        $nome = $request->nome;
        $tipo = $request->tipo;

        $data = RHMovimentacao::with('funcionario')
            ->where('empresa_id', $this->empresaId($request))
            ->when(!empty($tipo), fn ($q) => $q->where('tipo', $tipo))
            ->when(!empty($nome), fn ($q) => $q->whereHas('funcionario', fn ($f) => $f->where('nome', 'like', '%' . $nome . '%')))
            ->orderByDesc('data_movimentacao')
            ->paginate($this->perPage());

        return view('rh.movimentacoes.index', compact('data', 'nome', 'tipo'));
    }

    public function create(Request $request)
    {
        $funcionarios = Funcionario::where('empresa_id', $this->empresaId($request))->orderBy('nome')->get();
        $tipos = RHMovimentacao::tipos();
        return view('rh.movimentacoes.create', compact('funcionarios', 'tipos'));
    }

    public function edit(Request $request, $id)
    {
        $item = RHMovimentacao::where('empresa_id', $this->empresaId($request))->findOrFail($id);
        $funcionarios = Funcionario::where('empresa_id', $this->empresaId($request))->orderBy('nome')->get();
        $tipos = RHMovimentacao::tipos();

        return view('rh.movimentacoes.edit', compact('item', 'funcionarios', 'tipos'));
    }

    public function store(Request $request)
    {
        try {
            RHFolhaLockService::bloquearSeFechada($this->empresaId($request), null, null, 'Folha fechada. Não é possível cadastrar movimentações na competência atual.');
        } catch (\RuntimeException $e) {
            session()->flash('flash_erro', $e->getMessage());
            return redirect()->back();
        }

        if (!Schema::hasTable('rh_movimentacoes')) {
            session()->flash('flash_erro', 'Tabela RH não instalada. Execute o SQL do patch RH V2.');
            return redirect()->route('rh.movimentacoes.index');
        }

        $this->validateForm($request);
        $funcionario = Funcionario::where('empresa_id', $this->empresaId($request))->findOrFail($request->funcionario_id);

        try {
            DB::transaction(function () use ($request, $funcionario) {
                $valorAnterior = $request->filled('valor_anterior') ? __convert_value_bd($request->valor_anterior) : null;
                $valorNovo = $request->filled('valor_novo') ? __convert_value_bd($request->valor_novo) : null;

                RHMovimentacao::create([
                    'empresa_id' => $this->empresaId($request),
                    'funcionario_id' => $funcionario->id,
                    'tipo' => $request->tipo,
                    'descricao' => $request->descricao,
                    'cargo_anterior' => $request->cargo_anterior ?: $funcionario->funcao,
                    'cargo_novo' => $request->cargo_novo ?: $funcionario->funcao,
                    'valor_anterior' => $valorAnterior,
                    'valor_novo' => $valorNovo,
                    'data_movimentacao' => $request->data_movimentacao,
                    'status_gerado' => $funcionario->ativo === 0 ? 'inativo' : 'ativo',
                    'usuario_id' => auth()->id() ?? null,
                ]);

                $this->applyBusinessEffects($funcionario, $request, $valorNovo);
            });

            session()->flash('flash_sucesso', 'Movimentação registrada com sucesso!');
        } catch (\Throwable $e) {
            session()->flash('flash_erro', 'Erro ao registrar movimentação: ' . $e->getMessage());
            __saveLogError($e, $this->empresaId($request));
        }

        return redirect()->route('rh.movimentacoes.index');
    }

    public function update(Request $request, $id)
    {
        try {
            RHFolhaLockService::bloquearSeFechada($this->empresaId($request), null, null, 'Folha fechada. Não é possível alterar movimentações na competência atual.');
        } catch (\RuntimeException $e) {
            session()->flash('flash_erro', $e->getMessage());
            return redirect()->back();
        }

        $item = RHMovimentacao::where('empresa_id', $this->empresaId($request))->findOrFail($id);
        $this->validateForm($request);

        try {
            $item->fill([
                'funcionario_id' => $request->funcionario_id,
                'tipo' => $request->tipo,
                'descricao' => $request->descricao,
                'cargo_anterior' => $request->cargo_anterior,
                'cargo_novo' => $request->cargo_novo,
                'valor_anterior' => $request->filled('valor_anterior') ? __convert_value_bd($request->valor_anterior) : null,
                'valor_novo' => $request->filled('valor_novo') ? __convert_value_bd($request->valor_novo) : null,
                'data_movimentacao' => $request->data_movimentacao,
            ])->save();

            session()->flash('flash_sucesso', 'Movimentação atualizada com sucesso!');
        } catch (\Throwable $e) {
            session()->flash('flash_erro', 'Erro ao atualizar movimentação: ' . $e->getMessage());
            __saveLogError($e, $this->empresaId($request));
        }

        return redirect()->route('rh.movimentacoes.index');
    }

    private function validateForm(Request $request): void
    {
        $request->validate([
            'funcionario_id' => 'required',
            'tipo' => 'required',
            'descricao' => 'required|max:255',
            'data_movimentacao' => 'required|date',
        ], [
            'funcionario_id.required' => 'Selecione o funcionário.',
            'tipo.required' => 'Selecione o tipo.',
            'descricao.required' => 'Informe a descrição.',
            'data_movimentacao.required' => 'Informe a data.',
        ]);
    }

    private function applyBusinessEffects(Funcionario $funcionario, Request $request, $valorNovo): void
    {
        if (in_array($request->tipo, ['salario', 'promocao'], true) && $valorNovo !== null) {
            $funcionario->salario = $valorNovo;
        }

        if (in_array($request->tipo, ['cargo', 'promocao'], true) && !empty($request->cargo_novo)) {
            $funcionario->funcao = $request->cargo_novo;
        }

        if ($request->tipo === 'demissao') {
            $funcionario->ativo = 0;
        }

        $funcionario->save();
    }
}
