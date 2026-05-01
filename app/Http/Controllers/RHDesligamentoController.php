<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use App\Models\RHDesligamento;
use App\Models\RHRescisao;
use App\Modules\RH\Support\RHContext;
use App\Http\Requests\RH\StoreDesligamentoRequest;
use App\Modules\RH\Application\Actions\RegisterTerminationAction;
use App\Modules\RH\Application\DTOs\RegisterTerminationData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Services\RHFolhaLockService;
use App\Services\RHDesligamentoStatusSyncService;
use App\Services\RHRescisaoService;
use Carbon\Carbon;

class RHDesligamentoController extends Controller
{
    public function __construct(private RHDesligamentoStatusSyncService $statusSync, private RHRescisaoService $rescisaoService, private RegisterTerminationAction $registerTermination)
    {
    }

    public function index(Request $request)
    {
        if (!Schema::hasTable('rh_desligamentos')) {
            session()->flash('flash_erro', 'Tabela de desligamentos ainda não instalada. Execute o SQL do patch RH V4.');
            return view('rh.desligamentos.index', ['data' => collect(), 'semTabela' => true, 'rescisaoInstalada' => false]);
        }

        $funcionario = $request->funcionario;

        try {
            $this->statusSync->syncEmpresa((int) RHContext::empresaId(request()));
        } catch (\Throwable $e) {
        }

        $data = RHDesligamento::with(['funcionario'])
            ->where('empresa_id', RHContext::empresaId(request()))
            ->when(!empty($funcionario), function ($q) use ($funcionario) {
                return $q->whereHas('funcionario', function ($f) use ($funcionario) {
                    $f->where('nome', 'like', "%$funcionario%");
                });
            })
            ->orderBy('data_desligamento', 'desc')
            ->paginate(env('PAGINACAO'));

        return view('rh.desligamentos.index', [
            'data' => $data,
            'funcionario' => $funcionario,
            'rescisaoInstalada' => Schema::hasTable('rh_rescisoes'),
        ]);
    }

    public function create()
    {
        try {
            $this->statusSync->syncEmpresa((int) RHContext::empresaId(request()));
        } catch (\Throwable $e) {
        }

        $funcionarios = Funcionario::where('empresa_id', RHContext::empresaId(request()))
            ->where(function ($q) {
                $q->whereNull('ativo')->orWhere('ativo', 1);
            })
            ->orderBy('nome')
            ->get();

        $podeGerarRescisao = Schema::hasTable('rh_rescisoes') && Schema::hasTable('rh_rescisao_itens');

        return view('rh.desligamentos.create', compact('funcionarios', 'podeGerarRescisao'));
    }

    public function store(StoreDesligamentoRequest $request)
    {
        try {
            RHFolhaLockService::bloquearSeFechada(RHContext::empresaId(request()), null, null, 'Folha fechada. Não é possível registrar desligamentos na competência atual.');
        } catch (\RuntimeException $e) {
            session()->flash('flash_erro', $e->getMessage());
            return redirect()->back();
        }

        if (!Schema::hasTable('rh_desligamentos')) {
            session()->flash('flash_erro', 'Tabela de desligamentos ainda não instalada. Execute o SQL do patch RH V4.');
            return redirect()->route('rh.desligamentos.index');
        }

        $empresaId = (int) RHContext::empresaId(request());
        $funcionario = Funcionario::query()
            ->withoutGlobalScope('rh_status_visibility')
            ->where('empresa_id', $empresaId)
            ->findOrFail($request->funcionario_id);

        try {
            $result = $this->registerTermination->execute(new RegisterTerminationData(
                empresaId: $empresaId,
                funcionario: $funcionario,
                dataDesligamento: (string) $request->data_desligamento,
                motivo: (string) $request->motivo,
                tipo: (string) $request->tipo,
                tipoAviso: $request->filled('tipo_aviso') ? (string) $request->tipo_aviso : null,
                dependentesIrrf: (int) ($request->dependentes_irrf ?? 0),
                descontosExtras: (float) ($request->descontos_extras ?? 0),
                observacao: $request->filled('observacao') ? (string) $request->observacao : null,
                gerarTrct: $request->boolean('gerar_trct', true),
                gerarTqrct: $request->boolean('gerar_tqrct', true),
                gerarHomologacao: $request->boolean('gerar_homologacao', true),
                bloquearPortal: $request->boolean('bloquear_portal', true),
                arquivoMorto: $request->boolean('arquivo_morto', true),
                usuarioId: auth()->id() ?? null,
            ));
        } catch (\Throwable $e) {
            Log::error('Falha ao salvar desligamento/processar rescisão.', [
                'empresa_id' => $empresaId,
                'funcionario_id' => (int) $funcionario->id,
                'data_desligamento' => $request->data_desligamento,
                'arquivo_morto' => (bool) $request->boolean('arquivo_morto', true),
                'bloquear_portal' => (bool) $request->boolean('bloquear_portal', true),
                'erro' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('flash_erro', 'Não foi possível salvar o desligamento. O fluxo de rescisão foi compatibilizado com o banco atual, mas ocorreu uma falha durante o processamento.');
        }

        $rescisao = $result['rescisao'] ?? null;

        session()->flash('flash_sucesso', 'Desligamento registrado com sucesso!');

        return $rescisao
            ? redirect()->route('rh.desligamentos.show', $rescisao->id)
            : redirect()->route('rh.desligamentos.index');
    }

    public function show($id)
    {
        abort_if(!Schema::hasTable('rh_rescisoes'), 404);

        $rescisao = RHRescisao::query()
            ->with(['funcionario', 'itens', 'desligamento'])
            ->where('empresa_id', (int) RHContext::empresaId(request()))
            ->findOrFail($id);

        return view('rh.desligamentos.show', compact('rescisao'));
    }

    public function dashboardExecutivo()
    {
        $empresaId = (int) RHContext::empresaId(request());
        $resumo = $this->rescisaoService->resumoExecutivo($empresaId);

        return view('rh.desligamentos.dashboard_executivo', $resumo);
    }

    public function exportarFgts()
    {
        $empresaId = (int) RHContext::empresaId(request());
        $conteudo = $this->rescisaoService->exportarFgts($empresaId);
        $nome = 'fgts_rescisoes_' . $empresaId . '_' . now()->format('Ymd_His') . '.csv';

        return response($conteudo)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $nome . '"');
    }

    public function reativar($id)
    {
        $empresaId = (int) RHContext::empresaId(request());
        $rescisao = RHRescisao::query()->where('empresa_id', $empresaId)->with('funcionario')->findOrFail($id);
        $funcionario = $rescisao->funcionario;

        if ($funcionario) {
            $payload = [];
            if (Schema::hasColumn('funcionarios', 'ativo')) {
                $payload['ativo'] = 1;
            }
            if (Schema::hasColumn('funcionarios', 'status')) {
                $payload['status'] = 'Ativo';
            }
            if (Schema::hasColumn('funcionarios', 'data_desligamento')) {
                $payload['data_desligamento'] = null;
            }
            if ($payload !== []) {
                Funcionario::query()
                    ->withoutGlobalScope('rh_status_visibility')
                    ->where('empresa_id', $empresaId)
                    ->where('id', $funcionario->id)
                    ->update($payload);
            }
            $this->statusSync->syncFuncionario($funcionario->id, $empresaId);
        }

        session()->flash('flash_sucesso', 'Funcionário reativado com sucesso.');
        return redirect()->route('rh.desligamentos.show', $rescisao->id);
    }

    public function destroy($id)
    {
        abort_unless($this->canManageDesligamentos(), 403, 'Acesso restrito ao administrador.');

        if (!Schema::hasTable('rh_desligamentos')) {
            session()->flash('flash_erro', 'Tabela de desligamentos ainda não instalada. Execute o SQL do patch RH V4.');
            return redirect()->route('rh.desligamentos.index');
        }

        $empresaId = (int) RHContext::empresaId(request());
        $desligamento = RHDesligamento::query()
            ->where('empresa_id', $empresaId)
            ->with('funcionario')
            ->findOrFail($id);

        DB::transaction(function () use ($desligamento, $empresaId) {
            $funcionario = $desligamento->funcionario;
            $funcionarioId = (int) ($funcionario->id ?? $desligamento->funcionario_id);
            $rescisaoId = (int) ($desligamento->rescisao_id ?? 0);

            $desligamento->delete();
            if ($rescisaoId > 0 && Schema::hasTable('rh_rescisoes')) {
                if (Schema::hasTable('rh_rescisao_itens')) {
                    DB::table('rh_rescisao_itens')->where('rescisao_id', $rescisaoId)->delete();
                }

                RHRescisao::query()
                    ->where('empresa_id', $empresaId)
                    ->where('id', $rescisaoId)
                    ->delete();
            }

            if ($funcionarioId > 0) {
                $this->statusSync->syncFuncionario($funcionarioId, $empresaId);
            }
        });

        session()->flash('flash_sucesso', 'Desligamento excluído com sucesso!');
        return redirect()->route('rh.desligamentos.index');
    }

    private function canManageDesligamentos(): bool
    {
        $authUser = auth()->user();
        if ((int) (optional($authUser)->adm ?? 0) === 1) {
            return true;
        }

        $sessionUser = session('user_logged');
        if ((int) data_get($sessionUser, 'adm', 0) === 1) {
            return true;
        }

        $login = (string) (data_get($sessionUser, 'login') ?: optional($authUser)->login ?: '');
        return $login !== '' && function_exists('isSuper') && isSuper($login);
    }
}
