<?php

namespace App\Http\Controllers;

use App\Jobs\EnviarHoleriteEmailJob;
use App\Models\ApuracaoMensal;
use App\Models\Funcionario;
use App\Models\RHFolhaFechamento;
use App\Models\RHHoleriteEnvio;
use App\Models\RHHoleriteEnvioLote;
use App\Modules\RH\Application\ApuracaoMensal\ApuracaoMensalService;
use App\Modules\RH\Application\Financeiro\FolhaFinanceiroService;
use App\Modules\RH\Http\Controllers\Concerns\InteractsWithRH;
use App\Services\RHHoleritePdfService;
use Illuminate\Http\Request;
use App\Exports\RHHoleriteEnviosExport;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ApuracaoMensalController extends Controller
{
    use InteractsWithRH;

    public function __construct(private ApuracaoMensalService $service, private FolhaFinanceiroService $folhaFinanceiro, private RHHoleritePdfService $holeritePdfService)
    {
    }

    public function index(Request $request)
    {
        $nome = $request->nome;
        $dt_inicio = $request->get('start_date');
        $dt_fim = $request->get('end_date');

        $data = ApuracaoMensal::select('apuracao_mensals.*')
            ->join('funcionarios', 'apuracao_mensals.funcionario_id', '=', 'funcionarios.id')
            ->when($this->empresaId($request) > 0, fn ($q) => $q->where('funcionarios.empresa_id', $this->empresaId($request)))
            ->when(!empty($nome), fn ($q) => $q->where('funcionarios.nome', 'like', '%' . $nome . '%'))
            ->when(!empty($dt_inicio), fn ($q) => $q->whereDate('apuracao_mensals.created_at', '>=', $dt_inicio))
            ->when(!empty($dt_fim), fn ($q) => $q->whereDate('apuracao_mensals.created_at', '<=', $dt_fim))
            ->with('funcionario')
            ->orderByDesc('apuracao_mensals.created_at')
            ->paginate($this->perPage());

        $lotesRecentes = Schema::hasTable('rh_holerite_envio_lotes')
            ? $this->queryLotesCompetencia($request)->limit(10)->get()
            : collect();

        return view('apuracao_mensal.index', compact('nome', 'dt_inicio', 'dt_fim', 'data', 'lotesRecentes'));
    }

    public function gerarAutomatica(Request $request)
    {
        $data = $request->validate([
            'mes_competencia' => 'required|integer|min:1|max:12',
            'ano_competencia' => 'required|integer|min:2000|max:2100',
            'sobrescrever' => 'nullable|in:1',
            'acao_pos_geracao' => 'nullable|in:nenhuma,listar_holerites,baixar_zip',
            'enviar_holerites_email' => 'nullable|in:1',
        ]);

        $mes = (int) $data['mes_competencia'];
        $ano = (int) $data['ano_competencia'];

        if ($this->competenciaFechada($request, $mes, $ano)) {
            return redirect()->route('apuracaoMensal.index')
                ->with('flash_erro', 'Folha dessa competência está fechada. A geração automática foi bloqueada.');
        }

        try {
            $gerados = $this->service->gerarAutomatica(
                $this->empresaId($request),
                $mes,
                $ano,
                $request->boolean('sobrescrever'),
                $request->boolean('integrar_financeiro'),
                $request->input('vencimento_folha'),
                $request->filled('filial_id') && $request->input('filial_id') !== '-1' ? (int) $request->input('filial_id') : null
            );

            if ($gerados === 0) {
                return redirect()->route('apuracaoMensal.index')
                    ->with('flash_erro', 'Nenhuma apuração foi gerada. Verifique se há funcionários com eventos ativos nessa empresa.');
            }

            $mensagens = ['Apuração automática gerada com sucesso. Registros criados: ' . $gerados];

            if ($request->boolean('enviar_holerites_email')) {
                $lote = $this->criarLoteEnvioHolerites($request, $mes, $ano, 'Lote criado automaticamente após a apuração.');
                if ($lote) {
                    $mensagens[] = sprintf('Envio profissional iniciado em fila. Lote #%d criado com %d registro(s).', $lote->id, $lote->total);
                }
            }

            if (($data['acao_pos_geracao'] ?? 'nenhuma') === 'baixar_zip') {
                session()->flash('flash_sucesso', implode(' ', $mensagens));

                return $this->baixarHoleritesCompetenciaZipResponse($request, $mes, $ano);
            }

            if (($data['acao_pos_geracao'] ?? 'nenhuma') === 'listar_holerites') {
                return redirect()->route('apuracaoMensal.holerites_competencia', [
                    'mes_competencia' => $mes,
                    'ano_competencia' => $ano,
                ])->with('flash_sucesso', implode(' ', $mensagens));
            }

            return redirect()->route('apuracaoMensal.index')
                ->with('flash_sucesso', implode(' ', $mensagens));
        } catch (\Throwable $e) {
            __saveLogError($e, $this->empresaId($request));
            return redirect()->route('apuracaoMensal.index')
                ->with('flash_erro', 'Falha ao gerar apuração automática: ' . $e->getMessage());
        }
    }

    public function integrarFinanceiro(Request $request)
    {
        $data = $request->validate([
            'mes_competencia' => 'required|integer|min:1|max:12',
            'ano_competencia' => 'required|integer|min:2000|max:2100',
            'vencimento_folha' => 'nullable|date',
            'filial_id' => 'nullable',
        ]);

        try {
            $sincronizados = $this->folhaFinanceiro->sincronizarCompetencia(
                $this->empresaId($request),
                (int) $data['mes_competencia'],
                (int) $data['ano_competencia'],
                $data['vencimento_folha'] ?? null,
                $request->filled('filial_id') && $request->input('filial_id') !== '-1' ? (int) $request->input('filial_id') : null
            );

            if ($sincronizados === 0) {
                return redirect()->route('apuracaoMensal.index')
                    ->with('flash_erro', 'Nenhuma apuração encontrada para essa competência.');
            }

            return redirect()->route('apuracaoMensal.index')
                ->with('flash_sucesso', 'Integração com financeiro concluída. Contas a pagar sincronizadas: ' . $sincronizados);
        } catch (\Throwable $e) {
            __saveLogError($e, $this->empresaId($request));
            return redirect()->route('apuracaoMensal.index')
                ->with('flash_erro', 'Falha ao integrar folha com financeiro: ' . $e->getMessage());
        }
    }

    public function create(Request $request)
    {
        $funcionarios = Funcionario::orderBy('nome')
            ->when($this->empresaId($request) > 0, fn ($q) => $q->where('empresa_id', $this->empresaId($request)))
            ->get();

        $mesAtual = (int) date('m') - 1;
        return view('apuracao_mensal.create', compact('mesAtual', 'funcionarios'));
    }

    public function getEventos(Request $request, $id)
    {
        try {
            $item = Funcionario::query()
                ->when($this->empresaId($request) > 0, fn ($q) => $q->where('empresa_id', $this->empresaId($request)))
                ->with('eventos.evento')
                ->findOrFail($id);

            if ($item->eventos->isEmpty()) {
                return response()->json('', 200);
            }

            return view('apuracao_mensal.eventos', compact('item'));
        } catch (\Throwable $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    public function store(Request $request)
    {
        if ($this->competenciaFechada($request, $request->mes, $request->ano)) {
            session()->flash('flash_erro', 'Folha dessa competência está fechada. Apuração não pode ser alterada.');
            return redirect()->route('apuracaoMensal.index');
        }

        try {
            $this->service->store($request, $this->empresaId($request));
            session()->flash('flash_sucesso', 'Salvo com sucesso!');
        } catch (\Throwable $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, $this->empresaId($request));
        }

        return redirect()->route('apuracaoMensal.index');
    }

    public function destroy(Request $request, $id)
    {
        $item = ApuracaoMensal::findOrFail($id);
        if ($this->competenciaFechada($request, $item->mes, $item->ano)) {
            session()->flash('flash_erro', 'Folha dessa competência está fechada. Apuração não pode ser removida.');
            return redirect()->back();
        }

        try {
            $item->eventos()->delete();
            $item->delete();
            session()->flash('flash_sucesso', 'Registro removido!');
        } catch (\Throwable $e) {
            session()->flash('flash_erro', 'Algo deu Errado: ' . $e->getMessage());
            __saveLogError($e, $this->empresaId($request));
        }

        return redirect()->back();
    }

    public function holeritesCompetencia(Request $request)
    {
        $mes = (int) ($request->get('mes_competencia') ?: date('m'));
        $ano = (int) ($request->get('ano_competencia') ?: date('Y'));

        $data = $this->buscarApuracoesCompetencia($request, $mes, $ano);
        $lotes = $this->carregarLotesCompetencia($request, $mes, $ano);

        return view('apuracao_mensal.holerites', compact('data', 'mes', 'ano', 'lotes'));
    }

    public function painelHoleritesCompetencia(Request $request)
    {
        $mes = (int) ($request->get('mes_competencia') ?: date('m'));
        $ano = (int) ($request->get('ano_competencia') ?: date('Y'));
        $lotes = $this->carregarLotesCompetencia($request, $mes, $ano);

        return response()->json([
            'html' => view('apuracao_mensal.partials.painel_lotes', compact('lotes', 'mes', 'ano'))->render(),
            'active' => $lotes->contains(fn ($lote) => in_array($lote->status, ['na_fila', 'fila', 'processando'])),
            'has_lotes' => $lotes->isNotEmpty(),
        ]);
    }

    public function baixarHoleritesCompetenciaZip(Request $request)
    {
        $mes = (int) ($request->get('mes_competencia') ?: date('m'));
        $ano = (int) ($request->get('ano_competencia') ?: date('Y'));

        return $this->baixarHoleritesCompetenciaZipResponse($request, $mes, $ano);
    }

    public function enviarHoleritesCompetenciaEmail(Request $request)
    {
        $mes = (int) ($request->get('mes_competencia') ?: date('m'));
        $ano = (int) ($request->get('ano_competencia') ?: date('Y'));

        $lote = $this->criarLoteEnvioHolerites($request, $mes, $ano, 'Lote iniciado manualmente na tela de holerites.');

        return redirect()->route('apuracaoMensal.holerites_competencia', [
            'mes_competencia' => $mes,
            'ano_competencia' => $ano,
        ])->with(
            $lote ? 'flash_sucesso' : 'flash_erro',
            $lote
                ? sprintf('Envio iniciado em fila com sucesso. Lote #%d criado com %d registro(s).', $lote->id, $lote->total)
                : 'Nenhuma apuração encontrada para criar o lote de envio.'
        );
    }

    public function reenfileirarHoleritesCompetenciaEmail(Request $request, int $loteId)
    {
        $lote = RHHoleriteEnvioLote::query()
            ->where('id', $loteId)
            ->where('empresa_id', $this->empresaId($request))
            ->firstOrFail();

        $envios = $lote->envios()->whereIn('status', ['falha', 'sem_email'])->with('funcionario')->get();
        $reenfileirados = 0;

        foreach ($envios as $envio) {
            $emailAtual = trim((string) ($envio->funcionario->email ?? $envio->email ?? ''));
            $envio->email = $emailAtual;

            if ($emailAtual === '') {
                $envio->status = 'sem_email';
                $envio->update($this->envioFillableCompat([
                    'status' => 'sem_email',
                    'ultima_falha' => 'Funcionário segue sem e-mail cadastrado.',
                ]));
                continue;
            }

            $envio->update($this->envioFillableCompat([
                'status' => 'fila',
                'ultima_falha' => null,
            ]));

            EnviarHoleriteEmailJob::dispatch($envio->id)
                ->onConnection(config('queue.default'))
                ->onQueue('holerites');
            $reenfileirados++;
        }

        $lote->status = 'fila';
        $lote->recalculateStatus();

        return redirect()->route('apuracaoMensal.holerites_competencia', [
            'mes_competencia' => $lote->mes,
            'ano_competencia' => $lote->ano,
        ])->with(
            $reenfileirados > 0 ? 'flash_sucesso' : 'flash_erro',
            $reenfileirados > 0
                ? sprintf('%d envio(s) retornaram para a fila.', $reenfileirados)
                : 'Nenhum envio pôde ser reenfileirado. Verifique se os funcionários possuem e-mail cadastrado.'
        );
    }

    public function cancelarLoteHoleritesCompetenciaEmail(Request $request, int $loteId)
    {
        $lote = RHHoleriteEnvioLote::query()
            ->where('id', $loteId)
            ->where('empresa_id', $this->empresaId($request))
            ->firstOrFail();

        if (in_array($lote->status, ['concluido', 'concluido_com_falhas', 'finalizado'])) {
            return redirect()->route('apuracaoMensal.holerites_competencia', [
                'mes_competencia' => $lote->mes,
                'ano_competencia' => $lote->ano,
            ])->with('flash_erro', 'Esse lote já foi concluído e não pode mais ser cancelado.');
        }

        $afetados = $lote->envios()
            ->whereIn('status', ['fila', 'processando'])
            ->update($this->envioFillableCompat([
                'status' => 'cancelado',
                'ultima_falha' => 'Lote cancelado manualmente pelo usuário.',
            ]));

        $lote->status = 'cancelado';
        $lote->recalculateStatus();

        return redirect()->route('apuracaoMensal.holerites_competencia', [
            'mes_competencia' => $lote->mes,
            'ano_competencia' => $lote->ano,
        ])->with('flash_sucesso', sprintf('Lote #%d cancelado. %d registro(s) foram interrompidos.', $lote->id, $afetados));
    }

    public function exportarLoteHoleritesCompetenciaExcel(Request $request, int $loteId)
    {
        $lote = RHHoleriteEnvioLote::query()
            ->where('id', $loteId)
            ->where('empresa_id', $this->empresaId($request))
            ->with(['envios' => function ($q) {
                $q->with('funcionario')->orderBy('id');
            }])
            ->firstOrFail();

        return Excel::download(
            new RHHoleriteEnviosExport($lote),
            sprintf('log_holerites_lote_%d_%02d_%04d.xlsx', $lote->id, $lote->mes, $lote->ano)
        );
    }

    private function baixarHoleritesCompetenciaZipResponse(Request $request, int $mes, int $ano)
    {
        $data = $this->buscarApuracoesCompetencia($request, $mes, $ano);

        if ($data->isEmpty()) {
            return redirect()->route('apuracaoMensal.holerites_competencia', [
                'mes_competencia' => $mes,
                'ano_competencia' => $ano,
            ])->with('flash_erro', 'Nenhuma apuração encontrada para gerar o ZIP dos holerites.');
        }

        $dir = storage_path('app/tmp/holerites');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $zipName = sprintf('holerites_competencia_%02d_%04d_%s.zip', $mes, $ano, Str::random(8));
        $zipPath = $dir . DIRECTORY_SEPARATOR . $zipName;

        $zip = new \ZipArchive();
        $status = $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($status !== true) {
            throw new \RuntimeException('Não foi possível criar o arquivo ZIP dos holerites.');
        }

        foreach ($data as $item) {
            $pdf = $this->holeritePdfService->gerarPdfPorFuncionario($request, (int) $item->funcionario_id, $mes, $ano);
            $zip->addFromString($item->id . '_' . $pdf['filename'], $pdf['content']);
        }

        $zip->close();

        return response()->download($zipPath, sprintf('holerites_%02d_%04d.zip', $mes, $ano))->deleteFileAfterSend(true);
    }


    private function loteFillableCompat(array $attributes): array
    {
        $table = (new RHHoleriteEnvioLote())->getTable();
        $allowed = [];

        foreach ($attributes as $column => $value) {
            if (Schema::hasColumn($table, $column)) {
                $allowed[$column] = $value;
            }
        }

        if (!array_key_exists('competencia', $allowed) && Schema::hasColumn($table, 'competencia') && isset($attributes['mes'], $attributes['ano'])) {
            $allowed['competencia'] = sprintf('%02d/%04d', (int) $attributes['mes'], (int) $attributes['ano']);
        }

        if (isset($allowed['status'])) {
            $allowed['status'] = $this->normalizarStatusLote((string) $allowed['status']);
        }

        return $allowed;
    }

    private function envioFillableCompat(array $attributes): array
    {
        $table = (new RHHoleriteEnvio())->getTable();
        $allowed = [];

        foreach ($attributes as $column => $value) {
            if (Schema::hasColumn($table, $column)) {
                $allowed[$column] = $value;
            }
        }

        if (array_key_exists('ultima_falha', $attributes) && Schema::hasColumn($table, 'erro')) {
            $allowed['erro'] = $attributes['ultima_falha'];
        }

        if (isset($allowed['status'])) {
            $allowed['status'] = $this->normalizarStatusLote((string) $allowed['status'], true);
        }

        return $allowed;
    }

    private function normalizarStatusLote(string $status, bool $envio = false): string
    {
        $mapa = $envio
            ? ['na_fila' => 'fila', 'concluido' => 'enviado', 'concluido_com_falhas' => 'falha']
            : ['na_fila' => 'fila', 'concluido' => 'finalizado', 'concluido_com_falhas' => 'finalizado'];

        return $mapa[$status] ?? $status;
    }

    private function criarLoteEnvioHolerites(Request $request, int $mes, int $ano, ?string $observacao = null): ?RHHoleriteEnvioLote
    {
        if (!Schema::hasTable('rh_holerite_envio_lotes') || !Schema::hasTable('rh_holerite_envios')) {
            return null;
        }

        $data = $this->buscarApuracoesCompetencia($request, $mes, $ano);
        if ($data->isEmpty()) {
            return null;
        }

        $empresaId = $this->empresaId($request);
        $queueConnection = config('queue.default', env('QUEUE_CONNECTION', 'sync'));
        $queueName = 'holerites';

        $lote = RHHoleriteEnvioLote::create($this->loteFillableCompat([
            'empresa_id' => $empresaId,
            'mes' => $mes,
            'ano' => $ano,
            'status' => 'fila',
            'queue_connection' => $queueConnection,
            'queue_name' => $queueName,
            'solicitado_por' => (string) data_get(session('user_logged', []), 'nome', 'Usuário do sistema'),
            'observacao' => $observacao,
            'iniciado_em' => now(),
        ]));

        foreach ($data as $item) {
            $email = trim((string) ($item->funcionario->email ?? ''));
            $envio = RHHoleriteEnvio::create($this->envioFillableCompat([
                'lote_id' => $lote->id,
                'empresa_id' => $empresaId,
                'apuracao_mensal_id' => $item->id,
                'funcionario_id' => $item->funcionario_id,
                'email' => $email,
                'status' => $email === '' ? 'sem_email' : 'fila',
                'ultima_falha' => $email === '' ? 'Funcionário sem e-mail cadastrado.' : null,
                'payload' => [
                    'mes' => $mes,
                    'ano' => $ano,
                    'funcionario' => $item->funcionario->nome ?? null,
                ],
            ]));

            if ($email !== '') {
                EnviarHoleriteEmailJob::dispatch($envio->id)
                    ->onConnection(config('queue.default'))
                    ->onQueue('holerites');
            }
        }

        $lote->recalculateStatus();

        return $lote->fresh();
    }

    private function buscarApuracoesCompetencia(Request $request, int $mes, int $ano)
    {
        $meses = $this->resolverValoresMes($mes);

        return ApuracaoMensal::select('apuracao_mensals.*')
            ->join('funcionarios', 'apuracao_mensals.funcionario_id', '=', 'funcionarios.id')
            ->when($this->empresaId($request) > 0, fn ($q) => $q->where('funcionarios.empresa_id', $this->empresaId($request)))
            ->where(function ($q) use ($meses) {
                foreach ($meses as $mesValor) {
                    $q->orWhere('apuracao_mensals.mes', $mesValor);
                }
            })
            ->where('apuracao_mensals.ano', $ano)
            ->with('funcionario')
            ->orderBy('funcionarios.nome')
            ->get();
    }

    private function carregarLotesCompetencia(Request $request, int $mes, int $ano)
    {
        return Schema::hasTable('rh_holerite_envio_lotes')
            ? $this->queryLotesCompetencia($request, $mes, $ano)
                ->with(['envios' => function ($q) {
                    $q->with('funcionario')->orderByRaw("FIELD(status, 'falha', 'sem_email', 'fila', 'processando', 'cancelado', 'enviado')")->orderBy('id', 'desc');
                }])
                ->limit(5)
                ->get()
            : collect();
    }


    private function resolverValoresMes(int $mes): array
    {
        $mesIndice = max(1, min(12, $mes));
        $meses = ApuracaoMensal::mesesApuracao();
        $mesNome = $meses[$mesIndice - 1] ?? null;

        return array_values(array_filter([
            $mesIndice,
            (string) $mesIndice,
            str_pad((string) $mesIndice, 2, '0', STR_PAD_LEFT),
            $mesNome,
        ], fn ($valor) => $valor !== null && $valor !== ''));
    }

    private function queryLotesCompetencia(Request $request, ?int $mes = null, ?int $ano = null)
    {
        return RHHoleriteEnvioLote::query()
            ->when($this->empresaId($request) > 0, fn ($q) => $q->where('empresa_id', $this->empresaId($request)))
            ->when($mes !== null, fn ($q) => $q->where('mes', $mes))
            ->when($ano !== null, fn ($q) => $q->where('ano', $ano))
            ->orderByDesc('id');
    }

    private function competenciaFechada(Request $request, $mes, $ano): bool
    {
        if (!Schema::hasTable('rh_folha_fechamentos')) {
            return false;
        }

        $mapaMeses = [1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril', 5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto', 9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro'];
        $mesNumerico = is_numeric($mes)
            ? (int) $mes
            : ((int) (array_search(mb_strtolower((string) $mes), $mapaMeses, true) ?: 0));

        return RHFolhaFechamento::where('empresa_id', $this->empresaId($request))
            ->where('mes', $mesNumerico)
            ->where('ano', (int) $ano)
            ->where('status', 'fechado')
            ->exists();
    }
}
