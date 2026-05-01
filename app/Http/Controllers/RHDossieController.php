<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use App\Models\FuncionarioDependente;
use App\Models\FuncionarioFichaAdmissao;
use App\Models\RHDocumento;
use App\Models\RHDossie;
use App\Models\RHDossieEvento;
use App\Models\RHFerias;
use App\Models\RHMovimentacao;
use App\Modules\RH\Support\RHContext;
use App\Services\RH\RHDossieAutomationService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RHDossieController extends Controller
{
    public function __construct(private RHDossieAutomationService $automation)
    {
        $this->middleware('rh.permission:rh.dossie.visualizar')->only(['show', 'downloadDocumento']);
        $this->middleware('rh.permission:rh.dossie.documentos.gerenciar')->only(['storeDocumento', 'destroyDocumento']);
        $this->middleware('rh.permission:rh.dossie.eventos.gerenciar')->only(['storeEvento', 'destroyEvento']);
    }


    public function show(Request $request, $id)
    {
        $empresaId = RHContext::empresaId($request);

        $funcionario = Funcionario::query()
            ->comInativos()
            ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($id);

        $dossie = $this->firstOrCreateDossie($funcionario, $empresaId);
        $this->automation->syncFuncionario($funcionario, $empresaId ?: (int) $funcionario->empresa_id);

        $ficha = Schema::hasTable('funcionarios_ficha_admissao')
            ? FuncionarioFichaAdmissao::where('funcionario_id', $funcionario->id)->first()
            : null;

        $dependentes = Schema::hasTable('funcionarios_dependentes')
            ? FuncionarioDependente::query()
                ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
                ->where('funcionario_id', $funcionario->id)
                ->orderBy('nome')
                ->get()
            : collect();

        $documentos = Schema::hasTable('rh_documentos')
            ? RHDocumento::query()
                ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
                ->where('funcionario_id', $funcionario->id)
                ->orderByDesc('validade')
                ->orderByDesc('id')
                ->get()
            : collect();

        $movimentacoes = Schema::hasTable('rh_movimentacoes')
            ? RHMovimentacao::query()
                ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
                ->where('funcionario_id', $funcionario->id)
                ->orderByDesc('data_movimentacao')
                ->get()
            : collect();

        $ferias = Schema::hasTable('rh_ferias')
            ? RHFerias::query()
                ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
                ->where('funcionario_id', $funcionario->id)
                ->orderByDesc('data_inicio')
                ->get()
            : collect();

        $faltas = Schema::hasTable('rh_faltas')
            ? DB::table('rh_faltas')
                ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
                ->where('funcionario_id', $funcionario->id)
                ->orderByDesc('data_referencia')
                ->get()
            : collect();

        $desligamentos = Schema::hasTable('rh_desligamentos')
            ? DB::table('rh_desligamentos')
                ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
                ->where('funcionario_id', $funcionario->id)
                ->orderByDesc('data_desligamento')
                ->get()
            : collect();

        $holerites = Schema::hasTable('apuracao_mensals')
            ? DB::table('apuracao_mensals')
                ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
                ->where('funcionario_id', $funcionario->id)
                ->orderByDesc('ano')
                ->orderByDesc(DB::raw("LPAD(mes, 2, '0')"))
                ->limit(24)
                ->get()
            : collect();

        $eventosManuais = Schema::hasTable('rh_dossie_eventos')
            ? RHDossieEvento::query()
                ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
                ->where('funcionario_id', $funcionario->id)
                ->orderByDesc('data_evento')
                ->orderByDesc('id')
                ->get()
            : collect();

        $timeline = $this->buildTimeline(
            funcionario: $funcionario,
            ficha: $ficha,
            documentos: $documentos,
            movimentacoes: $movimentacoes,
            ferias: $ferias,
            faltas: $faltas,
            desligamentos: $desligamentos,
            holerites: $holerites,
            eventosManuais: $eventosManuais,
        );

        $stats = [
            'documentos' => $documentos->count(),
            'documentos_vencidos' => $documentos->filter(fn ($doc) => !empty($doc->validade) && now()->startOfDay()->gt(
                $doc->validade instanceof \Carbon\Carbon ? $doc->validade->copy()->startOfDay() : \Carbon\Carbon::parse($doc->validade)->startOfDay()
            ))->count(),
            'movimentacoes' => $movimentacoes->count(),
            'ferias' => $ferias->count(),
            'faltas' => $faltas->count(),
            'dependentes' => $dependentes->count(),
            'holerites' => $holerites->count(),
        ];

        $categoriasDocumento = [
            'admissao' => 'Admissão',
            'identificacao' => 'Identificação',
            'contrato' => 'Contrato / Aditivo',
            'saude' => 'Saúde ocupacional',
            'folha' => 'Folha / Holerite',
            'ferias' => 'Férias',
            'disciplinar' => 'Advertência / Compliance',
            'desligamento' => 'Desligamento',
            'outro' => 'Outro',
        ];

        $categoriasEvento = [
            'admissao' => 'Admissão',
            'movimentacao' => 'Movimentação',
            'ferias' => 'Férias',
            'falta' => 'Falta / Ocorrência',
            'folha' => 'Folha',
            'documento' => 'Documento',
            'compliance' => 'Compliance',
            'desligamento' => 'Desligamento',
            'outro' => 'Outro',
        ];

        return view('rh.dossie.show', compact(
            'funcionario',
            'dossie',
            'ficha',
            'dependentes',
            'documentos',
            'movimentacoes',
            'ferias',
            'faltas',
            'desligamentos',
            'holerites',
            'timeline',
            'stats',
            'categoriasDocumento',
            'categoriasEvento'
        ));
    }

    public function storeDocumento(Request $request, $id)
    {
        abort_unless(Schema::hasTable('rh_documentos'), 404, 'Tabela rh_documentos não encontrada.');

        $empresaId = RHContext::empresaId($request);
        $funcionario = Funcionario::query()->comInativos()
            ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($id);

        $data = $request->validate([
            'tipo' => 'required|string|max:80',
            'categoria' => 'nullable|string|max:60',
            'nome' => 'required|string|max:120',
            'validade' => 'nullable|date',
            'observacao' => 'nullable|string',
            'arquivo' => 'required|file|max:10240',
        ]);

        $file = $request->file('arquivo');
        abort_unless($file && $file->isValid(), 422, 'Arquivo inválido para upload.');

        $filename = now()->format('YmdHis') . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
        $directory = public_path('uploads/rh_documentos');
        if (!is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        $size = $file->getSize();
        $mime = $file->getClientMimeType();
        $originalName = $file->getClientOriginalName();
        $file->move($directory, $filename);

        RHDocumento::create([
            'empresa_id' => $empresaId ?: (int) $funcionario->empresa_id,
            'funcionario_id' => $funcionario->id,
            'tipo' => $data['tipo'],
            'categoria' => $data['categoria'] ?? 'outro',
            'nome' => $data['nome'],
            'arquivo' => 'uploads/rh_documentos/' . $filename,
            'validade' => $data['validade'] ?? null,
            'observacao' => $data['observacao'] ?? null,
            'origem' => 'dossie',
            'metadata_json' => [
                'nome_original' => $originalName,
                'mime' => $mime,
                'size' => $size,
            ],
            'usuario_id' => auth()->id(),
        ]);

        $this->touchDossie($funcionario, $empresaId);
        $this->automation->syncFuncionario($funcionario, $empresaId ?: (int) $funcionario->empresa_id);

        return redirect()->route('rh.dossie.show', $funcionario->id)->with('flash_sucesso', 'Documento anexado ao dossiê com sucesso.');
    }

    public function destroyDocumento(Request $request, $id, $documentoId)
    {
        $empresaId = RHContext::empresaId($request);
        $funcionario = Funcionario::query()->comInativos()
            ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($id);

        $documento = RHDocumento::query()
            ->where('funcionario_id', $funcionario->id)
            ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($documentoId);

        $arquivoRemovido = false;
        if (!empty($documento->arquivo)) {
            $fullPath = public_path($documento->arquivo);
            if (is_file($fullPath)) {
                $arquivoRemovido = @unlink($fullPath);
            }
        }

        $nomeDocumento = (string) ($documento->nome ?? 'Documento');
        $documento->delete();

        $this->touchDossie($funcionario, $empresaId);
        $this->automation->syncFuncionario($funcionario, $empresaId ?: (int) $funcionario->empresa_id);

        logger()->info('rh.dossie.documento_excluido', [
            'empresa_id' => $empresaId ?: (int) $funcionario->empresa_id,
            'funcionario_id' => $funcionario->id,
            'documento_nome' => $nomeDocumento,
            'documento_id' => $documentoId,
            'arquivo_removido' => $arquivoRemovido,
            'usuario_id' => auth()->id(),
        ]);

        return redirect()->route('rh.dossie.show', $funcionario->id)->with('flash_sucesso', 'Documento excluído do dossiê com sucesso.');
    }

    public function downloadDocumento(Request $request, $id, $documentoId)
    {
        $empresaId = RHContext::empresaId($request);
        $funcionario = Funcionario::query()->comInativos()
            ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($id);

        $documento = RHDocumento::query()
            ->where('funcionario_id', $funcionario->id)
            ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($documentoId);

        abort_if(empty($documento->arquivo), 404, 'Arquivo não vinculado ao documento.');

        $fullPath = $this->resolveDocumentoPath((string) $documento->arquivo);
        abort_unless($fullPath, 404, 'Arquivo não encontrado no servidor.');

        $downloadName = $this->resolveDocumentoDownloadName($documento);

        return Response::download($fullPath, $downloadName);
    }

    public function destroyEvento(Request $request, $id, $eventoId)
    {
        abort_unless(Schema::hasTable('rh_dossie_eventos'), 404, 'Tabela rh_dossie_eventos não encontrada.');

        $empresaId = RHContext::empresaId($request);
        $funcionario = Funcionario::query()->comInativos()
            ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($id);

        $evento = RHDossieEvento::query()
            ->where('funcionario_id', $funcionario->id)
            ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($eventoId);

        $titulo = (string) ($evento->titulo ?? 'Evento');
        $evento->delete();

        $this->touchDossie($funcionario, $empresaId);
        $this->automation->syncFuncionario($funcionario, $empresaId ?: (int) $funcionario->empresa_id);

        logger()->info('rh.dossie.evento_excluido', [
            'empresa_id' => $empresaId ?: (int) $funcionario->empresa_id,
            'funcionario_id' => $funcionario->id,
            'evento_id' => $eventoId,
            'titulo' => $titulo,
            'usuario_id' => auth()->id(),
        ]);

        return redirect()->route('rh.dossie.show', $funcionario->id)->with('flash_sucesso', 'Evento removido da timeline do dossiê com sucesso.');
    }

    public function storeEvento(Request $request, $id)
    {
        abort_unless(Schema::hasTable('rh_dossie_eventos'), 404, 'Tabela rh_dossie_eventos não encontrada.');

        $empresaId = RHContext::empresaId($request);
        $funcionario = Funcionario::query()->comInativos()
            ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($id);
        $dossie = $this->firstOrCreateDossie($funcionario, $empresaId);

        $data = $request->validate([
            'categoria' => 'required|string|max:40',
            'titulo' => 'required|string|max:120',
            'descricao' => 'nullable|string',
            'data_evento' => 'required|date',
            'visibilidade_portal' => 'nullable|boolean',
        ]);

        RHDossieEvento::create([
            'empresa_id' => $empresaId ?: (int) $funcionario->empresa_id,
            'dossie_id' => $dossie->id,
            'funcionario_id' => $funcionario->id,
            'categoria' => $data['categoria'],
            'titulo' => $data['titulo'],
            'descricao' => $data['descricao'] ?? null,
            'origem' => 'manual',
            'source_uid' => 'manual:' . md5($funcionario->id . '|' . $data['categoria'] . '|' . $data['titulo'] . '|' . $data['data_evento']),
            'data_evento' => $data['data_evento'],
            'visibilidade_portal' => (bool) ($data['visibilidade_portal'] ?? false),
            'payload_json' => null,
            'usuario_id' => auth()->id(),
        ]);

        $this->touchDossie($funcionario, $empresaId);
        $this->automation->syncFuncionario($funcionario, $empresaId ?: (int) $funcionario->empresa_id);

        return redirect()->route('rh.dossie.show', $funcionario->id)->with('flash_sucesso', 'Evento registrado no dossiê com sucesso.');
    }

    protected function firstOrCreateDossie(Funcionario $funcionario, int $empresaId): RHDossie
    {
        if (!Schema::hasTable('rh_dossies')) {
            return new RHDossie([
                'empresa_id' => $empresaId ?: (int) $funcionario->empresa_id,
                'funcionario_id' => $funcionario->id,
                'status' => 'ativo',
                'ultima_atualizacao_em' => now(),
            ]);
        }

        return RHDossie::firstOrCreate(
            [
                'empresa_id' => $empresaId ?: (int) $funcionario->empresa_id,
                'funcionario_id' => $funcionario->id,
            ],
            [
                'status' => $this->resolveDossieStatus($funcionario),
                'ultima_atualizacao_em' => now(),
            ]
        );
    }

    protected function touchDossie(Funcionario $funcionario, int $empresaId): void
    {
        if (!Schema::hasTable('rh_dossies')) {
            return;
        }

        RHDossie::query()->updateOrCreate(
            [
                'empresa_id' => $empresaId ?: (int) $funcionario->empresa_id,
                'funcionario_id' => $funcionario->id,
            ],
            [
                'status' => $this->resolveDossieStatus($funcionario),
                'ultima_atualizacao_em' => now(),
            ]
        );
    }

    protected function resolveDossieStatus(Funcionario $funcionario): string
    {
        $ativo = $funcionario->ativo;
        if (in_array($ativo, [0, '0', 'N', 'n', 'NAO', 'nao', 'NÃO', 'não', 'I', 'i'], true)) {
            return 'arquivado';
        }

        return 'ativo';
    }

    protected function resolveDocumentoPath(?string $arquivo): ?string
    {
        $relative = ltrim((string) $arquivo, '/\\');
        if ($relative === '') {
            return null;
        }

        $candidatos = array_filter(array_unique([
            Str::startsWith((string) $arquivo, ['/', '\\']) ? (string) $arquivo : null,
            public_path($relative),
            storage_path('app/public/' . $relative),
            storage_path('app/' . $relative),
            base_path($relative),
        ]));

        foreach ($candidatos as $candidato) {
            if (is_file($candidato)) {
                return $candidato;
            }
        }

        return null;
    }

    protected function resolveDocumentoDownloadName(RHDocumento $documento): string
    {
        $metadata = $documento->metadata_json;
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $metadata = $decoded;
            }
        }

        $originalName = is_array($metadata) ? ($metadata['nome_original'] ?? null) : null;
        if (is_string($originalName) && trim($originalName) !== '') {
            return trim($originalName);
        }

        $arquivo = trim((string) $documento->arquivo);
        if ($arquivo !== '') {
            return basename($arquivo);
        }

        return Str::slug((string) ($documento->nome ?: 'documento')) . '.pdf';
    }

    protected function buildTimeline(...$sources): Collection
    {
        $items = collect();
        $funcionario = $sources['funcionario'];
        $ficha = $sources['ficha'];

        if ($ficha && !empty($ficha->data_admissao)) {
            $items->push([
                'data' => $ficha->data_admissao,
                'categoria' => 'admissao',
                'titulo' => 'Admissão do colaborador',
                'descricao' => trim('Cadastro admissional e início do vínculo de ' . $funcionario->nome . '.'),
                'origem' => 'ficha_admissao',
            ]);
        }

        foreach ($sources['movimentacoes'] as $item) {
            $items->push([
                'data' => $item->data_movimentacao,
                'categoria' => 'movimentacao',
                'titulo' => RHMovimentacao::tipos()[$item->tipo] ?? ucfirst((string) $item->tipo),
                'descricao' => $item->descricao,
                'origem' => 'rh_movimentacoes',
            ]);
        }

        foreach ($sources['ferias'] as $item) {
            $inicio = $item->data_inicio instanceof \Carbon\Carbon ? $item->data_inicio->format('d/m/Y') : date('d/m/Y', strtotime((string) $item->data_inicio));
            $fim = $item->data_fim instanceof \Carbon\Carbon ? $item->data_fim->format('d/m/Y') : date('d/m/Y', strtotime((string) $item->data_fim));

            $items->push([
                'data' => $item->data_inicio,
                'categoria' => 'ferias',
                'titulo' => 'Férias ' . ucfirst(strtolower((string) $item->status)),
                'descricao' => sprintf('Gozo de %s até %s (%s dias).', $inicio, $fim, $item->dias),
                'origem' => 'rh_ferias',
            ]);
        }

        foreach ($sources['faltas'] as $item) {
            $items->push([
                'data' => $item->data_referencia,
                'categoria' => 'falta',
                'titulo' => 'Ocorrência de ponto / falta',
                'descricao' => trim(($item->tipo ?? 'Ocorrência') . ' - ' . ($item->descricao ?? 'Sem descrição detalhada.')),
                'origem' => 'rh_faltas',
            ]);
        }

        foreach ($sources['documentos'] as $item) {
            $items->push([
                'data' => $item->created_at ?? now(),
                'categoria' => 'documento',
                'titulo' => 'Documento: ' . $item->nome,
                'descricao' => trim(($item->tipo ?? 'Documento') . (!empty($item->observacao) ? ' - ' . $item->observacao : '')),
                'origem' => 'rh_documentos',
                'documento_id' => $item->id ?? null,
                'can_delete_documento' => !empty($item->id),
            ]);
        }

        foreach ($sources['holerites'] as $item) {
            $items->push([
                'data' => sprintf('%s-%s-01', $item->ano, str_pad((string) $item->mes, 2, '0', STR_PAD_LEFT)),
                'categoria' => 'folha',
                'titulo' => 'Fechamento de folha / holerite',
                'descricao' => sprintf('Competência %s/%s - valor final R$ %s.', str_pad((string) $item->mes, 2, '0', STR_PAD_LEFT), $item->ano, number_format((float) $item->valor_final, 2, ',', '.')),
                'origem' => 'apuracao_mensals',
            ]);
        }

        foreach ($sources['desligamentos'] as $item) {
            $items->push([
                'data' => $item->data_desligamento,
                'categoria' => 'desligamento',
                'titulo' => 'Desligamento registrado',
                'descricao' => trim(($item->tipo ?? 'Desligamento') . ' - ' . ($item->motivo ?? 'Sem motivo informado')),
                'origem' => 'rh_desligamentos',
            ]);
        }

        foreach ($sources['eventosManuais'] as $item) {
            $items->push([
                'data' => $item->data_evento,
                'categoria' => $item->categoria,
                'titulo' => $item->titulo,
                'descricao' => $item->descricao,
                'origem' => 'rh_dossie_eventos',
                'evento_id' => $item->id ?? null,
                'can_delete_evento' => !empty($item->id),
            ]);
        }

        return $items
            ->filter(fn ($item) => !empty($item['data']))
            ->sortByDesc(fn ($item) => strtotime((string) $item['data']))
            ->values();
    }
}
