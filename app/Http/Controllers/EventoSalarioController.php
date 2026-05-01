<?php

namespace App\Http\Controllers;

use App\Models\ApuracaoSalarioEvento;
use App\Models\EventoSalario;
use App\Models\FuncionarioEvento;
use App\Modules\RH\Support\Concerns\InteractsWithRH;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EventoSalarioController extends Controller
{
    use InteractsWithRH;

    public function index(Request $request)
    {
        $data = $this->scopeEmpresa(EventoSalario::query(), $request, 'evento_salarios')
            ->when($request->filled('nome'), fn ($q) => $q->where('nome', 'like', '%' . $request->nome . '%'))
            ->orderBy('nome')
            ->paginate($this->perPage());

        return view('evento_salario.index', compact('data'));
    }

    public function create()
    {
        return view('evento_salario.create');
    }

    public function store(Request $request)
    {
        $this->validateForm($request);

        try {
            EventoSalario::create($this->payload($request));
            session()->flash('flash_sucesso', 'Evento cadastrado com sucesso!');
        } catch (\Throwable $e) {
            session()->flash('flash_erro', 'Erro ao cadastrar evento! ' . $e->getMessage());
            __saveLogError($e, $this->empresaId($request));
        }

        return redirect()->route('eventoSalario.index');
    }

    public function edit(Request $request, $id)
    {
        $item = $this->scopeEmpresa(EventoSalario::query(), $request, 'evento_salarios')->findOrFail($id);
        return view('evento_salario.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = $this->scopeEmpresa(EventoSalario::query(), $request, 'evento_salarios')->findOrFail($id);
        $this->validateForm($request);

        DB::beginTransaction();
        try {
            $ativoAnterior = (int) ($item->ativo ?? 1);
            $payload = $this->payload($request);

            $item->fill($payload)->save();

            if (Schema::hasTable('funcionario_eventos') && Schema::hasColumn('funcionario_eventos', 'ativo')) {
                $ativoAtual = (int) ($item->ativo ?? 1);

                if ($ativoAtual !== $ativoAnterior) {
                    $query = FuncionarioEvento::query()->where('evento_id', $item->id);

                    if (Schema::hasColumn('funcionario_eventos', 'empresa_id')) {
                        $query->where('empresa_id', $this->empresaId($request));
                    }

                    $query->update(['ativo' => $ativoAtual]);
                }
            }

            DB::commit();
            session()->flash('flash_sucesso', 'Evento alterado com sucesso!');
        } catch (\Throwable $e) {
            DB::rollBack();
            session()->flash('flash_erro', 'Erro ao alterar evento! ' . $e->getMessage());
            __saveLogError($e, $this->empresaId($request));
        }

        return redirect()->route('eventoSalario.index');
    }

    public function destroy(Request $request, $id)
    {
        $item = $this->scopeEmpresa(EventoSalario::query(), $request, 'evento_salarios')->findOrFail($id);
        $nome = mb_strtoupper(trim((string) $item->nome));

        if (in_array($nome, ['SALARIO', 'SALÁRIO'], true)) {
            session()->flash('flash_erro', 'O evento base de salário não pode ser excluído.');
            return redirect()->route('eventoSalario.index');
        }

        DB::beginTransaction();
        try {
            $vinculosFuncionario = Schema::hasTable('funcionario_eventos') ? FuncionarioEvento::where('evento_id', $item->id)->delete() : 0;
            $vinculosApuracao = Schema::hasTable('apuracao_salario_eventos') ? ApuracaoSalarioEvento::where('evento_id', $item->id)->delete() : 0;
            $item->delete();
            DB::commit();

            $extra = ($vinculosFuncionario > 0 || $vinculosApuracao > 0)
                ? ' Vínculos removidos: funcionário=' . (int) $vinculosFuncionario . ', apuração=' . (int) $vinculosApuracao . '.'
                : '';

            session()->flash('flash_sucesso', 'Evento deletado com sucesso!' . $extra);
        } catch (\Throwable $e) {
            DB::rollBack();
            session()->flash('flash_erro', 'Erro ao deletar evento! ' . $e->getMessage());
            __saveLogError($e, $this->empresaId($request));
        }

        return redirect()->route('eventoSalario.index');
    }

    private function validateForm(Request $request): void
    {
        $this->validate($request, [
            'nome' => 'required|max:50',
            'tipo' => 'required',
            'metodo' => 'required',
            'condicao' => 'required',
            'ativo' => 'required',
        ], [
            'nome.required' => 'O campo Nome é obrigatório.',
            'nome.max' => '50 caracteres maximos permitidos.',
            'tipo.required' => 'O campo Tipo é obrigatório.',
            'metodo.required' => 'O campo Médoto é obrigatório.',
            'condicao.required' => 'O campo Condição é obrigatório.',
            'ativo.required' => 'O campo Ativo é obrigatório.',
        ]);
    }

    private function payload(Request $request): array
    {
        $payload = $request->all();
        $payload['empresa_id'] = $this->empresaId($request);
        return $payload;
    }
}
