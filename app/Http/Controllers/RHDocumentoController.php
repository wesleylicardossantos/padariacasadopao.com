<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use App\Models\RHDocumento;
use App\Models\RHDocumentoLog;
use App\Models\RHDocumentoTemplate;
use App\Modules\RH\Support\RHContext;
use App\Services\RH\RHDossieAutomationService;
use App\Services\RH\RHDocumentoAIService;
use App\Services\RH\RHDocumentoPdfService;
use App\Services\RH\RHDocumentoTemplateEngineService;
use App\Support\SchemaSafe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RHDocumentoController extends Controller
{
    public function __construct(
        private RHDossieAutomationService $automation,
        private RHDocumentoTemplateEngineService $templateEngine,
        private RHDocumentoAIService $aiService,
        private RHDocumentoPdfService $pdfService,
    ) {
        $this->middleware('rh.permission:rh.dossie.visualizar')->only(['index', 'preview', 'templatesIndex']);
        $this->middleware('rh.permission:rh.dossie.documentos.gerenciar')->only([
            'create', 'store', 'destroy', 'templatesCreate', 'templatesStore', 'templatesEdit', 'templatesUpdate'
        ]);
        $this->middleware('rh.permission:rh.dossie.documentos.excluir')->only(['destroy', 'templatesDestroy']);
    }

    public function index(Request $request)
    {
        $empresaId = RHContext::empresaId($request);
        $funcionarioId = $request->funcionario_id;
        $data = collect();
        $hasTable = DB::getSchemaBuilder()->hasTable('rh_documentos');
        $templates = collect();
        $templatesAtivos = 0;
        $documentosIa = 0;

        if ($hasTable) {
            $query = DB::table('rh_documentos')
                ->join('funcionarios', 'funcionarios.id', '=', 'rh_documentos.funcionario_id')
                ->select('rh_documentos.*', 'funcionarios.nome as funcionario_nome')
                ->where('rh_documentos.empresa_id', $empresaId);

            if (!empty($funcionarioId)) {
                $query->where('rh_documentos.funcionario_id', $funcionarioId);
            }

            $data = $query->orderBy('rh_documentos.id', 'desc')->paginate(env('PAGINACAO', 20));
            $documentosIa = DB::table('rh_documentos')
                ->where('empresa_id', $empresaId)
                ->whereIn('origem', ['ia', 'ia+template'])
                ->count();
        }

        if (Schema::hasTable('rh_document_templates')) {
            $templates = SchemaSafe::applyEmpresaScope(RHDocumentoTemplate::query(), $empresaId, 'rh_document_templates')
                ->where('ativo', 1)
                ->orderBy('categoria')
                ->orderBy('nome')
                ->get();
            $templatesAtivos = $templates->count();
        }

        $funcionarios = Funcionario::where('empresa_id', $empresaId)->orderBy('nome')->get();
        return view('rh.documentos.index', compact('data', 'funcionarios', 'hasTable', 'funcionarioId', 'templatesAtivos', 'documentosIa', 'templates'));
    }

    public function create()
    {
        $empresaId = RHContext::empresaId(request());
        $funcionarios = Funcionario::where('empresa_id', $empresaId)->orderBy('nome')->get();
        $templates = Schema::hasTable('rh_document_templates')
            ? SchemaSafe::applyEmpresaScope(RHDocumentoTemplate::query(), $empresaId, 'rh_document_templates')
                ->where('ativo', 1)
                ->orderBy('categoria')
                ->orderBy('nome')
                ->get()
            : collect();

        return view('rh.documentos.create', compact('funcionarios', 'templates'));
    }

    public function store(Request $request)
    {
        if (!DB::getSchemaBuilder()->hasTable('rh_documentos')) {
            return redirect()->route('rh.documentos.index')->with('flash_erro', 'Tabela rh_documentos não encontrada. Execute o SQL do módulo RH.');
        }

        if ($request->hasFile('arquivo')) {
            return $this->storeManual($request);
        }

        $request->validate([
            'funcionario_id' => 'required|integer',
            'tipo' => 'required|string|max:80',
            'nome' => 'required|string|max:160',
            'template_id' => 'nullable|integer',
            'categoria' => 'nullable|string|max:60',
            'usar_ia' => 'nullable|boolean',
            'instrucoes_ia' => 'nullable|string|max:5000',
            'observacao' => 'nullable|string|max:5000',
            'validade' => 'nullable|date',
            'data_rescisao' => 'nullable|date',
            'tipo_rescisao' => 'nullable|string|max:120',
            'motivo_documento' => 'nullable|string|max:1000',
            'tipo_contrato_label' => 'nullable|string|max:160',
            'regime_trabalho' => 'nullable|string|max:120',
            'tipo_contrato' => 'nullable|string|max:80',
            'regime' => 'nullable|string|max:80',
            'periodicidade_pagamento' => 'nullable|string|max:120',
            'forma_pagamento_documento' => 'nullable|string|max:255',
            'banco' => 'nullable|string|max:120',
            'agencia' => 'nullable|string|max:120',
            'conta_corrente' => 'nullable|string|max:120',
            'beneficios_descricao' => 'nullable|string|max:2000',
            'autoriza_contribuicao_sindical' => 'nullable|string|max:20',
            'jornada_descricao' => 'nullable|string|max:1000',
            'prazo_contrato_descricao' => 'nullable|string|max:2000',
            'confidencialidade_multa' => 'nullable|string|max:1000',
            'foro_cidade' => 'nullable|string|max:160',
            'funcionario_nacionalidade' => 'nullable|string|max:120',
            'funcionario_estado_civil' => 'nullable|string|max:120',
            'funcionario_profissao' => 'nullable|string|max:160',
            'funcionario_ctps' => 'nullable|string|max:120',
            'funcionario_ctps_serie' => 'nullable|string|max:120',
            'funcionario_atividades' => 'nullable|string|max:3000',
            'empresa_tipo_pessoa' => 'nullable|string|max:160',
            'empresa_representante_legal' => 'nullable|string|max:255',
            'empresa_representante_cpf' => 'nullable|string|max:40',
            'local_trabalho' => 'nullable|string|max:255',
        ]);

        $empresaId = RHContext::empresaId($request);
        $funcionario = Funcionario::query()->comInativos()
            ->where('empresa_id', $empresaId)
            ->findOrFail($request->funcionario_id);

        $template = null;
        $htmlBase = '<p>{{observacoes_adicionais}}</p>';
        if ($request->filled('template_id') && Schema::hasTable('rh_document_templates')) {
            $template = SchemaSafe::applyEmpresaScope(RHDocumentoTemplate::query(), $empresaId, 'rh_document_templates')->find($request->template_id);
            if ($template) {
                $htmlBase = (string) ($template->conteudo_html ?: '<p>{{observacoes_adicionais}}</p>');
            }
        }

        $passthroughExtra = collect($request->except([
            '_token', '_method', 'arquivo', 'template_id', 'usar_ia',
        ]))
            ->map(fn ($value) => is_scalar($value) ? (string) $value : '')
            ->all();

        $extra = array_merge($passthroughExtra, [
            'observacoes' => (string) ($request->observacao ?? ''),
            'motivo' => (string) ($request->motivo_documento ?? ''),
            'tipo_rescisao' => (string) ($request->tipo_rescisao ?? ''),
            'data_rescisao' => (string) ($request->data_rescisao ?? ''),
            'tipo_contrato_label' => (string) ($request->tipo_contrato_label ?? ''),
            'tipo_contrato' => (string) ($request->tipo_contrato ?? ''),
            'regime_trabalho' => (string) ($request->regime_trabalho ?? ''),
            'regime' => (string) ($request->regime ?? ''),
            'periodicidade_pagamento' => (string) ($request->periodicidade_pagamento ?? ''),
            'forma_pagamento_documento' => (string) ($request->forma_pagamento_documento ?? ''),
            'banco' => (string) ($request->banco ?? ''),
            'agencia' => (string) ($request->agencia ?? ''),
            'conta_corrente' => (string) ($request->conta_corrente ?? ''),
            'beneficios_descricao' => (string) ($request->beneficios_descricao ?? ''),
            'autoriza_contribuicao_sindical' => (string) ($request->autoriza_contribuicao_sindical ?? ''),
            'jornada_descricao' => (string) ($request->jornada_descricao ?? ''),
            'prazo_contrato_descricao' => (string) ($request->prazo_contrato_descricao ?? ''),
            'confidencialidade_multa' => (string) ($request->confidencialidade_multa ?? ''),
            'foro_cidade' => (string) ($request->foro_cidade ?? ''),
            'funcionario_nacionalidade' => (string) ($request->funcionario_nacionalidade ?? ''),
            'funcionario_estado_civil' => (string) ($request->funcionario_estado_civil ?? ''),
            'funcionario_profissao' => (string) ($request->funcionario_profissao ?? ''),
            'funcionario_ctps' => (string) ($request->funcionario_ctps ?? ''),
            'funcionario_ctps_serie' => (string) ($request->funcionario_ctps_serie ?? ''),
            'funcionario_atividades' => (string) ($request->funcionario_atividades ?? ''),
            'empresa_tipo_pessoa' => (string) ($request->empresa_tipo_pessoa ?? ''),
            'empresa_representante_legal' => (string) ($request->empresa_representante_legal ?? ''),
            'empresa_representante_cpf' => (string) ($request->empresa_representante_cpf ?? ''),
            'local_trabalho' => (string) ($request->local_trabalho ?? ''),
        ]);

        $variaveis = $this->templateEngine->buildVariables($funcionario, $extra);
        $htmlRenderizado = $this->templateEngine->render($htmlBase, $variaveis);
        $usarIa = (bool) $request->boolean('usar_ia');
        if (($template?->slug ?? null) === 'termo-rescisao') {
            $usarIa = false;
        }

        $aiResponse = $this->aiService->gerarDocumento([
            'usar_ia' => $usarIa,
            'tipo_documento' => (string) $request->tipo,
            'instrucoes' => (string) ($request->instrucoes_ia ?? ''),
            'variaveis' => $variaveis,
            'html_base' => $htmlRenderizado,
        ]);

        $conteudoHtml = (string) ($aiResponse['html'] ?? $htmlRenderizado);
        $conteudoTexto = (string) ($aiResponse['text'] ?? strip_tags($conteudoHtml));
        $hash = hash('sha256', $conteudoHtml);
        $filename = now()->format('YmdHis') . '_' . Str::slug((string) $request->nome) . '.pdf';
        $directory = public_path('uploads/rh_documentos/gerados');
        if (!is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }
        $pdfBinary = $this->pdfService->gerar((string) $request->nome, $conteudoHtml);
        file_put_contents($directory . DIRECTORY_SEPARATOR . $filename, $pdfBinary);

        $storageMirrorDir = storage_path('app/public/uploads/rh_documentos/gerados');
        if (!is_dir($storageMirrorDir)) {
            @mkdir($storageMirrorDir, 0775, true);
        }
        @file_put_contents($storageMirrorDir . DIRECTORY_SEPARATOR . $filename, $pdfBinary);

        $documentoId = DB::table('rh_documentos')->insertGetId([
            'empresa_id' => $empresaId,
            'funcionario_id' => $funcionario->id,
            'template_id' => $template?->id,
            'tipo' => $request->tipo,
            'categoria' => $request->categoria ?: ($template?->categoria ?: 'outro'),
            'nome' => $request->nome,
            'arquivo' => 'uploads/rh_documentos/gerados/' . $filename,
            'conteudo_html' => $conteudoHtml,
            'conteudo_texto' => $conteudoTexto,
            'validade' => $request->validade ?: null,
            'observacao' => $request->observacao ?? '',
            'origem' => $usarIa ? ($template ? 'ia+template' : 'ia') : ($template ? 'template' : 'manual'),
            'status' => 'gerado',
            'hash_conteudo' => $hash,
            'ia_provider' => $aiResponse['provider'] ?? null,
            'ia_model' => $aiResponse['model'] ?? null,
            'metadata_json' => json_encode([
                'usar_ia' => $usarIa,
                'tipo_rescisao' => $request->tipo_rescisao,
                'motivo_documento' => $request->motivo_documento,
                'template_nome' => $template?->nome,
                'ai_error' => $aiResponse['error'] ?? null,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'usuario_id' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (Schema::hasTable('rh_documento_logs')) {
            RHDocumentoLog::create([
                'empresa_id' => $empresaId,
                'documento_id' => $documentoId,
                'funcionario_id' => $funcionario->id,
                'acao' => 'gerado',
                'usuario_id' => auth()->id(),
                'detalhes' => 'Documento inteligente gerado e salvo automaticamente no dossiê.',
                'payload_resumo' => [
                    'usar_ia' => $usarIa,
                    'template_id' => $template?->id,
                    'origem' => $usarIa ? 'ia' : 'template',
                ],
            ]);
        }

        $this->automation->syncFuncionario($funcionario, (int) ($funcionario->empresa_id ?? $empresaId));

        return redirect()->route('rh.dossie.show', $funcionario->id)
            ->with('flash_sucesso', 'Documento inteligente gerado com sucesso e salvo automaticamente no dossiê.');
    }

    private function storeManual(Request $request)
    {
        $request->validate([
            'funcionario_id' => 'required',
            'tipo' => 'required|max:80',
            'nome' => 'required|max:120',
            'arquivo' => 'nullable|file|max:5120'
        ]);

        $arquivo = '';
        if ($request->hasFile('arquivo')) {
            $file = $request->file('arquivo');
            abort_unless($file && $file->isValid(), 422, 'Arquivo inválido para upload.');
            $nome = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $dir = public_path('uploads/rh_documentos');
            if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
            $file->move($dir, $nome);
            $arquivo = 'uploads/rh_documentos/' . $nome;
        }

        DB::table('rh_documentos')->insert([
            'empresa_id' => RHContext::empresaId(request()),
            'funcionario_id' => $request->funcionario_id,
            'tipo' => $request->tipo,
            'categoria' => $request->categoria ?? 'outro',
            'nome' => $request->nome,
            'arquivo' => $arquivo,
            'validade' => $request->validade ?: null,
            'observacao' => $request->observacao ?? '',
            'origem' => 'manual_upload',
            'status' => 'anexado',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $funcionario = Funcionario::query()->comInativos()->find($request->funcionario_id);
        if ($funcionario) {
            $this->automation->syncFuncionario($funcionario, (int) ($funcionario->empresa_id ?? RHContext::empresaId(request())));
        }

        return redirect()->route('rh.documentos.index')->with('flash_sucesso', 'Documento registrado com sucesso!');
    }

    public function preview(Request $request, $id)
    {
        abort_unless(DB::getSchemaBuilder()->hasTable('rh_documentos'), 404);
        $empresaId = RHContext::empresaId($request);
        $documento = RHDocumento::query()
            ->where('empresa_id', $empresaId)
            ->findOrFail($id);

        return view('rh.documentos.preview', compact('documento'));
    }

    public function download(Request $request, $id)
    {
        abort_unless(DB::getSchemaBuilder()->hasTable('rh_documentos'), 404);

        $empresaId = RHContext::empresaId($request);
        $documento = RHDocumento::query()
            ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($id);

        abort_if(empty($documento->arquivo), 404, 'Arquivo não vinculado ao documento.');

        $fullPath = $this->resolveDocumentoPath((string) $documento->arquivo);
        abort_unless($fullPath, 404, 'Arquivo não encontrado no servidor.');

        $downloadName = $this->resolveDocumentoDownloadName($documento);

        return response()->download($fullPath, $downloadName);
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

    public function templatesIndex(Request $request)
    {
        abort_unless(Schema::hasTable('rh_document_templates'), 404);
        $empresaId = RHContext::empresaId($request);
        $data = SchemaSafe::applyEmpresaScope(RHDocumentoTemplate::query(), $empresaId, 'rh_document_templates')
            ->orderBy('categoria')
            ->orderBy('nome')
            ->paginate(env('PAGINACAO', 20));

        return view('rh.documentos.templates.index', compact('data'));
    }

    public function templatesCreate()
    {
        $template = new RHDocumentoTemplate(['usa_ia' => true, 'ativo' => true, 'versao' => '1.0']);
        return view('rh.documentos.templates.form', [
            'template' => $template,
            'action' => route('rh.documentos.templates.store'),
            'method' => 'POST',
        ]);
    }

    public function templatesStore(Request $request)
    {
        abort_unless(Schema::hasTable('rh_document_templates'), 404);
        $payload = $this->buildTemplatePayload($request, true);

        $template = new RHDocumentoTemplate();
        SchemaSafe::fillAndSave($template, $payload);

        return redirect()->route('rh.documentos.templates.index')->with('flash_sucesso', 'Template jurídico salvo com sucesso.');
    }

    public function templatesEdit(Request $request, $id)
    {
        abort_unless(Schema::hasTable('rh_document_templates'), 404);
        $template = $this->findTemplate($request, $id);
        return view('rh.documentos.templates.form', [
            'template' => $template,
            'action' => route('rh.documentos.templates.update', $template->id),
            'method' => 'PUT',
        ]);
    }

    public function templatesUpdate(Request $request, $id)
    {
        abort_unless(Schema::hasTable('rh_document_templates'), 404);
        $template = $this->findTemplate($request, $id);
        $payload = $this->buildTemplatePayload($request, false);
        SchemaSafe::fillAndSave($template, $payload);

        return redirect()->route('rh.documentos.templates.index')->with('flash_sucesso', 'Template jurídico atualizado com sucesso.');
    }

    public function templatesDestroy(Request $request, $id)
    {
        abort_unless(Schema::hasTable('rh_document_templates'), 404);
        $template = $this->findTemplate($request, $id);
        $template->delete();
        return redirect()->route('rh.documentos.templates.index')->with('flash_sucesso', 'Template removido com sucesso.');
    }


    private function buildTemplatePayload(Request $request, bool $creating): array
    {
        $data = $this->validateTemplate($request);
        $payload = array_merge($data, [
            'usa_ia' => $request->boolean('usa_ia'),
            'ativo' => $request->boolean('ativo', true),
            'slug' => Str::slug((string) $data['nome']),
        ]);

        if ($creating) {
            $payload['empresa_id'] = RHContext::empresaId($request);
            $payload['created_by'] = auth()->id();
        }

        $payload['updated_by'] = auth()->id();
        $payload['conteudo_texto'] = strip_tags((string) ($data['conteudo_html'] ?? ''));

        return SchemaSafe::filter('rh_document_templates', $payload);
    }

    private function findTemplate(Request $request, $id): RHDocumentoTemplate
    {
        $empresaId = RHContext::empresaId($request);

        return SchemaSafe::applyEmpresaScope(RHDocumentoTemplate::query(), $empresaId, 'rh_document_templates')
            ->findOrFail($id);
    }

    private function validateTemplate(Request $request): array
    {
        return $request->validate([
            'nome' => 'required|string|max:150',
            'categoria' => 'required|string|max:60',
            'tipo_documento' => 'required|string|max:80',
            'descricao' => 'nullable|string|max:255',
            'conteudo_html' => 'required|string',
            'usa_ia' => 'nullable|boolean',
            'ativo' => 'nullable|boolean',
            'versao' => 'nullable|string|max:20',
        ]);
    }

    public function destroy($id)
    {
        abort_unless(DB::getSchemaBuilder()->hasTable('rh_documentos'), 404);
        $item = DB::table('rh_documentos')->where('id', $id)->first();
        if ($item && !empty($item->arquivo)) {
            $full = public_path($item->arquivo);
            if (file_exists($full)) {
                @unlink($full);
            }
        }
        DB::table('rh_documentos')->where('id', $id)->delete();
        if ($item && !empty($item->funcionario_id)) {
            $funcionario = Funcionario::query()->comInativos()->find($item->funcionario_id);
            if ($funcionario) {
                $this->automation->syncFuncionario($funcionario, (int) ($funcionario->empresa_id ?? 0));
            }
        }
        return redirect()->route('rh.documentos.index')->with('flash_sucesso', 'Documento removido!');
    }
}
