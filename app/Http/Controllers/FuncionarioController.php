<?php

namespace App\Http\Controllers;

use App\Models\Cidade;
use App\Models\Funcionario;
use App\Models\FuncionarioFichaAdmissao;
use App\Models\Usuario;
use App\Modules\RH\Application\Funcionario\FuncionarioService;
use App\Modules\RH\Application\Actions\CreateEmployeeAction;
use App\Modules\RH\Application\Actions\UpdateEmployeeAction;
use App\Modules\RH\Application\Actions\ChangeEmployeeStatusAction;
use App\Modules\RH\Application\DTOs\CreateEmployeeData;
use App\Modules\RH\Application\DTOs\UpdateEmployeeData;
use App\Modules\RH\Application\DTOs\ChangeEmployeeStatusData;
use App\Modules\RH\Support\Enums\EmployeeStatus;
use App\Modules\RH\Support\Concerns\InteractsWithRH;
use App\Rules\ValidaDocumento;
use App\Services\RHFolhaLockService;
use App\Models\RHPortalFuncionario;
use App\Models\RHPortalPerfil;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Builder;
use App\Services\RHDesligamentoStatusSyncService;
use App\Services\OfficialLaborReferenceService;

class FuncionarioController extends Controller
{
    use InteractsWithRH;

    public function __construct(
        private FuncionarioService $service,
        private CreateEmployeeAction $createEmployeeAction,
        private UpdateEmployeeAction $updateEmployeeAction,
        private ChangeEmployeeStatusAction $changeEmployeeStatusAction,
        private RHDesligamentoStatusSyncService $desligamentoStatusSyncService,
        private OfficialLaborReferenceService $officialLaborReferenceService,
    ) {
    }

    public function index(Request $request)
    {
        try {
            $this->desligamentoStatusSyncService->syncEmpresa($this->empresaId($request));
        } catch (\Throwable $e) {
            // sincronização preventiva não pode derrubar a listagem.
        }

        $arquivoMorto = $request->boolean('arquivo_morto');
        $statusAtual = $request->query('status');
        $statusFiltro = $statusAtual;

        if (DB::getSchemaBuilder()->hasColumn('funcionarios', 'ativo') && ($statusAtual === null || $statusAtual === '')) {
            $statusFiltro = $arquivoMorto ? '0' : '1';
        }

        $filters = [
            'nome' => $request->query('nome', ''),
            'cpf' => $request->query('cpf', ''),
            'status' => $statusFiltro,
            'arquivo_morto' => $arquivoMorto ? '1' : '0',
        ];

        $data = $this->applyListFilters($this->withFichaAdmissao($this->baseQuery($request)), $filters)
            ->paginate($this->perPage())
            ->appends($filters);

        $resumo = $this->baseQuery($request, true)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw(DB::getSchemaBuilder()->hasColumn('funcionarios', 'ativo') ? "SUM(CASE WHEN ativo IN (1, '1') THEN 1 ELSE 0 END) as ativos" : 'COUNT(*) as ativos')
            ->selectRaw(DB::getSchemaBuilder()->hasColumn('funcionarios', 'ativo') ? "SUM(CASE WHEN ativo IN (0, '0') THEN 1 ELSE 0 END) as inativos" : '0 as inativos')
            ->first();

        return view('funcionarios.index', [
            'data' => $data,
            'filters' => $filters,
            'arquivoMorto' => $arquivoMorto,
            'resumoLista' => [
                'total' => (int) ($resumo->total ?? 0),
                'ativos' => (int) ($resumo->ativos ?? 0),
                'inativos' => (int) ($resumo->inativos ?? 0),
            ],
        ]);
    }

    public function create(Request $request)
    {
        $this->loadOfficialReferences();

        $usuarios = Usuario::query()
            ->when($this->empresaId($request) > 0, fn ($q) => $q->where('empresa_id', $this->empresaId($request)))
            ->get();

        $cidades = Cidade::all();
        $officialReferences = $this->resolveOfficialReferences();

        return view('funcionarios.create', compact('cidades', 'usuarios', 'officialReferences'));
    }

    public function store(Request $request)
    {
        $this->validateForm($request);

        try {
            $this->createEmployeeAction->execute(CreateEmployeeData::fromRequest($request, $this->empresaId($request)));
            session()->flash('flash_sucesso', 'Funcionário cadastrado!');
        } catch (\Throwable $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, $this->empresaId($request));
        }

        return redirect()->route('funcionarios.index');
    }

    public function edit(Request $request, $id)
    {
        $this->loadOfficialReferences();

        $item = $this->findFuncionarioOrFail($request, $id, $this->defaultFuncionarioRelations(['cidade']));
        $this->assertAccess($item);

        $usuarios = Usuario::query()
            ->when($this->empresaId($request) > 0, fn ($q) => $q->where('empresa_id', $this->empresaId($request)))
            ->get();

        $cidades = Cidade::all();
        $officialReferences = $this->resolveOfficialReferences();

        return view('funcionarios.edit', compact('item', 'usuarios', 'cidades', 'officialReferences'));
    }

    public function update(Request $request, $id)
    {
        if (RHFolhaLockService::bloquearSeFechada($this->empresaId($request), $request->mes, $request->ano)) {
            session()->flash('flash_erro', 'Folha fechada para esta competência. Não é permitido alterar funcionário/salário.');
            return redirect()->route('funcionarios.index');
        }

        $item = $this->findFuncionarioOrFail($request, $id);
        $this->assertAccess($item);
        $this->validateForm($request);

        try {
            $this->updateEmployeeAction->execute(UpdateEmployeeData::fromRequest($item, $request, $this->empresaId($request)));
            session()->flash('flash_sucesso', 'Cadastro atualizado!');
        } catch (\Throwable $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, $this->empresaId($request));
        }

        return redirect()->route('funcionarios.index');
    }

    public function destroy(Request $request, $id)
    {
        $empresaId = $this->empresaId($request);
        if (RHFolhaLockService::bloquearSeFechada($empresaId, $request->mes, $request->ano)) {
            session()->flash('flash_erro', 'Folha fechada para esta competência. Não é permitido excluir funcionário.');
            return redirect()->route('funcionarios.index');
        }

        $item = $this->findFuncionarioOrFail($request, $id);
        $this->assertAccess($item);

        try {
            DB::transaction(function () use ($item, $empresaId) {
                $funcionarioId = (int) $item->id;
                $tables = [
                    'funcionario_eventos',
                    'evento_funcionarios',
                    'funcionarios_dependentes',
                    'funcionarios_ficha_admissao',
                    'historico_funcionarios',
                    'contato_funcionarios',
                    'apuracao_mensals',
                    'comissao_vendas',
                    'agendamentos',
                    'funcionario_os',
                    'rh_documentos',
                    'rh_dossie_eventos',
                    'rh_dossies',
                    'rh_movimentacoes',
                    'rh_ocorrencias',
                    'rh_ferias',
                    'rh_desligamentos',
                    'rh_portal_funcionarios',
                    'rh_holerite_envios',
                    'portal_api_tokens',
                    'portal_audit_logs',
                    'integracao_logs',
                    'atividade_eventos',
                    'venda_caixa_pre_vendas',
                ];

                foreach ($tables as $table) {
                    if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'funcionario_id')) {
                        continue;
                    }

                    $delete = DB::table($table)->where('funcionario_id', $funcionarioId);
                    if ($empresaId > 0 && Schema::hasColumn($table, 'empresa_id')) {
                        $delete->where(function ($query) use ($empresaId) {
                            $query->where('empresa_id', $empresaId)->orWhereNull('empresa_id');
                        });
                    }
                    $delete->delete();
                }

                if (Schema::hasTable('clientes') && Schema::hasColumn('clientes', 'funcionario_id')) {
                    $clientes = DB::table('clientes')->where('funcionario_id', $funcionarioId);
                    if ($empresaId > 0 && Schema::hasColumn('clientes', 'empresa_id')) {
                        $clientes->where('empresa_id', $empresaId);
                    }
                    $clientes->update(['funcionario_id' => null]);
                }

                foreach ($item->documentosRh as $documento) {
                    if (!empty($documento->arquivo)) {
                        $arquivo = public_path($documento->arquivo);
                        if (File::exists($arquivo)) {
                            File::delete($arquivo);
                        }
                    }
                }

                $item->delete();
            });

            session()->flash('flash_sucesso', 'Funcionário excluído com sucesso!');
        } catch (\Throwable $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, $empresaId);
        }

        return redirect()->route('funcionarios.index');
    }

    public function imprimir(Request $request, $id)
    {
        $item = $this->findFuncionarioOrFail($request, $id);
        $this->assertAccess($item);

        $ficha = $this->resolveFichaAdmissao($item->id);
        $html = view('funcionarios.impressao', compact('item', 'ficha'))->render();

        $domPdf = new Dompdf(['enable_remote' => true]);
        $domPdf->loadHtml($html, 'UTF-8');
        $domPdf->setPaper('A4');
        $domPdf->render();

        return response($domPdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="FichaCadastral-' . $item->id . '.pdf"');
    }

    public function show(Request $request, $id)
    {
        $item = $this->findFuncionarioOrFail($request, $id);
        $this->assertAccess($item);

        $ficha = $this->resolveFichaAdmissao($item->id);
        $acessoPortal = null;
        $perfisPortal = collect();

        if (Schema::hasTable('rh_portal_funcionarios')) {
            $acessoPortal = RHPortalFuncionario::query()
                ->with('perfil')
                ->where('empresa_id', $item->empresa_id)
                ->where('funcionario_id', $item->id)
                ->first();
        }

        if (Schema::hasTable('rh_portal_perfis')) {
            $perfisPortal = RHPortalPerfil::query()
                ->where(function ($q) use ($item) {
                    $q->whereNull('empresa_id')->orWhere('empresa_id', $item->empresa_id);
                })
                ->where('ativo', 1)
                ->orderBy('nome')
                ->get();
        }

        return view('funcionarios.show', compact('item', 'ficha', 'acessoPortal', 'perfisPortal'));
    }

    public function comissao(Request $request)
    {
        $vendedor = $this->baseQuery($request)->get();
        if (!__valida_objeto($vendedor)) {
            abort(403);
        }

        return view('funcionarios.comissao', compact('vendedor'));
    }

    public function toggleStatus(Request $request, $id)
    {
        if (RHFolhaLockService::bloquearSeFechada($this->empresaId($request), $request->mes, $request->ano)) {
            session()->flash('flash_erro', 'Folha fechada para esta competência. Não é permitido alterar status de funcionário.');
            return redirect()->route('funcionarios.index');
        }

        $item = $this->findFuncionarioOrFail($request, $id);
        $this->assertAccess($item);

        if (DB::getSchemaBuilder()->hasColumn('funcionarios', 'ativo')) {
            $status = $item->isActive() ? EmployeeStatus::ARCHIVED : EmployeeStatus::ACTIVE;
            $this->changeEmployeeStatusAction->execute(new ChangeEmployeeStatusData(
                funcionario: $item,
                status: $status,
                usuarioId: get_id_user() ?: null,
                motivo: $item->isActive() ? 'Funcionário movido para arquivo morto.' : 'Funcionário reativado.',
            ));
            session()->flash('flash_sucesso', 'Status atualizado com sucesso!');
        } else {
            session()->flash('flash_erro', 'Campo de status não encontrado na tabela de funcionários.');
        }

        return redirect()->route('funcionarios.index');
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->resolveFilters($request);

        $data = $this->applyListFilters($this->withFichaAdmissao($this->baseQuery($request)), $filters)
            ->orderBy('nome')
            ->get();

        $html = view('funcionarios.relatorio_pdf', compact('data'))->render();
        $domPdf = new Dompdf(['enable_remote' => true]);
        $domPdf->loadHtml($html, 'UTF-8');
        $domPdf->setPaper('A4', 'portrait');
        $domPdf->render();

        return response($domPdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="funcionarios.pdf"');
    }

    public function exportExcel(Request $request)
    {
        $filters = $this->resolveFilters($request);

        $data = $this->applyListFilters($this->withFichaAdmissao($this->baseQuery($request)), $filters)
            ->orderBy('nome')
            ->get();

        $csv = "ID;CPF;Nome;Data de Admissão;Função;Salário;Status\n";
        foreach ($data as $i => $item) {
            $ficha = $this->resolveFichaAdmissao($item->id);
            $dataAdmissao = $ficha && $ficha->data_admissao ? __data_pt($ficha->data_admissao, false) : '';
            $status = (!isset($item->ativo) || $item->ativo) ? 'Ativo' : 'Inativo';
            $csv .= ($i + 1) . ';' . $item->cpf . ';' . $item->nome . ';' . $dataAdmissao . ';' . ($item->funcao ?? '') . ';' . number_format((float) $item->salario, 2, ',', '.') . ';' . $status . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="funcionarios.csv"');
    }


    private function resolveFilters(Request $request): array
    {
        $arquivoMorto = $request->boolean('arquivo_morto');
        $statusAtual = $request->query('status');
        $statusFiltro = $statusAtual;

        if (DB::getSchemaBuilder()->hasColumn('funcionarios', 'ativo') && ($statusAtual === null || $statusAtual === '')) {
            $statusFiltro = $arquivoMorto ? '0' : '1';
        }

        return [
            'nome' => $request->query('nome', ''),
            'cpf' => $request->query('cpf', ''),
            'status' => $statusFiltro,
            'arquivo_morto' => $arquivoMorto ? '1' : '0',
        ];
    }

    private function applyListFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when(($filters['nome'] ?? '') !== '', fn (Builder $q) => $q->where('nome', 'like', '%' . $filters['nome'] . '%'))
            ->when(($filters['cpf'] ?? '') !== '', fn (Builder $q) => $q->where('cpf', 'like', '%' . $filters['cpf'] . '%'))
            ->when(($filters['status'] ?? null) !== null && ($filters['status'] ?? '') !== '', function (Builder $q) use ($filters) {
                return DB::getSchemaBuilder()->hasColumn('funcionarios', 'ativo')
                    ? $q->where('ativo', (int) $filters['status'])
                    : $q;
            });
    }

    private function validateForm(Request $request): void
    {
        $this->validate($request, [
            'nome' => 'required|max:80',
            'cpf' => ['required', new ValidaDocumento('cpf')],
            'rua' => 'required|max:80',
            'numero' => 'required|max:10',
            'bairro' => 'required|max:50',
            'telefone' => ['required', 'max:20', 'regex:/^\(?\d{2}\)?\s?\d{4,5}-?\d{4}$/'],
            'celular' => ['required', 'max:20', 'regex:/^\(?\d{2}\)?\s?\d{4,5}-?\d{4}$/'],
            'email' => 'nullable|max:40|email',
            'cidade_id' => 'required',
            'rg' => ['required', 'max:30'],
            'salario' => 'required',
            'data_registro' => ['required', 'date'],
            'funcao' => 'required|max:80',
        ], [
            'nome.required' => 'Nome é obrigatório.',
            'rua.required' => 'O campo Rua é obrigatório.',
            'numero.required' => 'O campo Número é obrigatório.',
            'cidade_id.required' => 'O campo Cidade é obrigatório.',
            'bairro.required' => 'O campo Bairro é obrigatório.',
            'telefone.required' => 'O campo Telefone é obrigatório.',
            'telefone.regex' => 'Telefone inválido.',
            'celular.required' => 'Campo Celular é obrigatório.',
            'celular.regex' => 'Celular inválido.',
            'email.email' => 'Email inválido.',
            'rg.required' => 'Campo RG é obrigatório.',
            'cpf.required' => 'Campo CPF é obrigatório.',
            'salario.required' => 'Campo Salário é obrigatório.',
            'data_registro.required' => 'Escolha uma Data.',
            'funcao.required' => 'Campo Função é obrigatório.',
        ]);
    }

    private function baseQuery(Request $request, bool $includeInativos = false)
    {
        $query = Funcionario::query();

        if ($includeInativos) {
            $query->withoutGlobalScope('rh_status_visibility');
        }

        return $this->scopeEmpresa($query, $request, 'funcionarios');
    }

    private function findFuncionarioOrFail(Request $request, int|string $id, array $with = []): Funcionario
    {
        $query = $this->baseQuery($request, true);
        $with = $this->sanitizeFuncionarioRelations($with);
        if ($with !== []) {
            $query->with($with);
        }

        return $query->findOrFail($id);
    }




    private function canUseFichaAdmissao(): bool
    {
        return Schema::hasTable('funcionarios_ficha_admissao');
    }

    private function withFichaAdmissao(Builder $query): Builder
    {
        return $this->canUseFichaAdmissao() ? $query->with('fichaAdmissao') : $query;
    }

    private function resolveFichaAdmissao(int|string $funcionarioId): ?FuncionarioFichaAdmissao
    {
        if (! $this->canUseFichaAdmissao()) {
            return null;
        }

        return FuncionarioFichaAdmissao::where('funcionario_id', $funcionarioId)->first();
    }

    private function sanitizeFuncionarioRelations(array $with): array
    {
        if ($this->canUseFichaAdmissao()) {
            return array_values(array_unique($with));
        }

        return array_values(array_filter($with, fn (string $relation) => $relation !== 'fichaAdmissao'));
    }

    private function defaultFuncionarioRelations(array $extra = []): array
    {
        $relations = array_merge($extra, $this->canUseFichaAdmissao() ? ['fichaAdmissao'] : []);

        return array_values(array_unique($relations));
    }

    private function loadOfficialReferences(): void
    {
        try {
            $this->officialLaborReferenceService->ensureSynced();
        } catch (\Throwable $e) {
            // Não pode impedir a abertura da tela.
        }
    }

    private function resolveOfficialReferences(): array
    {
        return [
            'categorias' => $this->officialLaborReferenceService->getWorkerCategories(),
            'tiposContrato' => $this->officialLaborReferenceService->getContractTypes(),
            'naturezasAtividade' => $this->officialLaborReferenceService->getNatureActivities(),
            'departamentos' => $this->officialLaborReferenceService->getDepartments(),
            'indicativosAdmissao' => $this->officialLaborReferenceService->getAdmissionIndicators(),
            'funcoesOficiais' => collect(),
        ];
    }

    private function assertAccess(Funcionario $funcionario): void
    {
        if (!__valida_objeto($funcionario)) {
            abort(403);
        }
    }
}
