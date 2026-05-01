<?php

namespace App\Http\Controllers;

use App\Models\EventoSalario;
use App\Models\Funcionario;
use App\Models\FuncionarioEvento;
use App\Modules\RH\Application\FuncionarioEvento\FuncionarioEventoService;
use App\Modules\RH\Support\Concerns\InteractsWithRH;
use App\Services\RHFolhaLockService;
use Illuminate\Http\Request;

class FuncionarioEventoController extends Controller
{
    use InteractsWithRH;

    public function __construct(private FuncionarioEventoService $service)
    {
    }

    public function index(Request $request)
    {
        $empresaId = $this->empresaId($request);
        $funcionario = $request->funcionario;

        $data = Funcionario::with(['eventos.evento'])
            ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
            ->when($request->filled('funcionario'), fn ($q) => $q->where('nome', 'like', '%' . $funcionario . '%'))
            ->orderBy('nome')
            ->paginate($this->perPage());

        return view('funcionario_evento.index', compact('data', 'funcionario'));
    }

    public function create(Request $request)
    {
        $empresaId = $this->empresaId($request);
        $funcionarios = Funcionario::query()->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))->orderBy('nome')->get();
        $eventos = EventoSalario::query()->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))->orderBy('nome')->get();

        return view('funcionario_evento.create', compact('eventos', 'funcionarios'));
    }

    public function store(Request $request)
    {
        $empresaId = $this->empresaId($request);
        if (RHFolhaLockService::bloquearSeFechada($empresaId, $request->mes, $request->ano)) {
            session()->flash('flash_erro', 'Folha fechada para esta competência. Não é permitido lançar eventos.');
            return redirect()->route('funcionarioEventos.index');
        }

        try {
            $this->service->replaceForFuncionario((int) $request->funcionario_id, $request->all(), $empresaId);
            session()->flash('flash_sucesso', 'Evento adicionado com sucesso!');
        } catch (\Throwable $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, $empresaId);
        }

        return redirect()->route('funcionarioEventos.index');
    }


    public function show(Request $request, $id)
    {
        $empresaId = $this->empresaId($request);
        $item = Funcionario::with(['eventos.evento'])
            ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($id);

        $funcionarios = Funcionario::query()->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))->orderBy('nome')->get();
        $eventos = EventoSalario::query()->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))->orderBy('nome')->get();

        return view('funcionario_evento.edit', compact('eventos', 'funcionarios', 'item'));
    }

    public function edit(Request $request, $id)
    {
        $empresaId = $this->empresaId($request);
        $item = Funcionario::query()->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))->findOrFail($id);
        $funcionarios = Funcionario::query()->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))->orderBy('nome')->get();
        $eventos = EventoSalario::query()->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))->orderBy('nome')->get();

        return view('funcionario_evento.edit', compact('eventos', 'funcionarios', 'item'));
    }

    public function update(Request $request, $id)
    {
        $empresaId = $this->empresaId($request);
        if (RHFolhaLockService::bloquearSeFechada($empresaId, $request->mes, $request->ano)) {
            session()->flash('flash_erro', 'Folha fechada para esta competência. Não é permitido alterar eventos.');
            return redirect()->route('funcionarioEventos.index');
        }

        try {
            $this->service->replaceForFuncionario((int) $id, $request->all(), $empresaId);
            session()->flash('flash_sucesso', 'Eventos atualizados!');
        } catch (\Throwable $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, $empresaId);
        }

        return redirect()->route('funcionarioEventos.index');
    }

    public function destroy(Request $request, $id)
    {
        $empresaId = $this->empresaId($request);
        if (RHFolhaLockService::bloquearSeFechada($empresaId, $request->mes, $request->ano)) {
            session()->flash('flash_erro', 'Folha fechada para esta competência. Não é permitido excluir eventos.');
            return redirect()->route('funcionarioEventos.index');
        }

        try {
            $query = FuncionarioEvento::query()
                ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId));

            $apagados = (clone $query)->where('funcionario_id', $id)->delete();

            if (!$apagados) {
                $apagados = (clone $query)->whereKey($id)->delete();
            }

            session()->flash('flash_sucesso', $apagados > 0
                ? 'Eventos removidos com sucesso!'
                : 'Nenhum evento pendente para exclusão.');
        } catch (\Throwable $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, $empresaId);
        }

        return redirect()->route('funcionarioEventos.index');
    }
}
