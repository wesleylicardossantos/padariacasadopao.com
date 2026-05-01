<?php

namespace App\Services;

use App\Models\RHDossie;
use App\Models\RHDossieEvento;
use App\Models\RHDocumento;
use App\Modules\RH\Services\RHFolhaModuleService;
use App\Modules\RH\Support\RHContext;
use App\Support\RHCompetenciaHelper;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RHHoleritePdfService
{
    public function __construct(private RHFolhaModuleService $service)
    {
    }

    public function gerarPdfPorFuncionario(Request $request, int $funcionarioId, int $mes, int $ano): array
    {
        $empresaId = RHContext::empresaId($request);

        return $this->gerarPdfPorFuncionarioEmpresa($empresaId, $funcionarioId, $mes, $ano);
    }

    public function gerarPdfPorFuncionarioEmpresa(int $empresaId, int $funcionarioId, int $mes, int $ano): array
    {
        $mes = RHCompetenciaHelper::numero($mes);
        $ano = (int) $ano > 0 ? (int) $ano : (int) date('Y');
        $payload = $this->service->montarRecibo($empresaId, $funcionarioId, $mes, $ano);
        $assinatura = $this->gerarAssinatura($payload, $empresaId, $funcionarioId);
        $payload['assinatura_digital'] = $assinatura;
        $content = $this->renderPdf($payload);
        $filename = $this->nomeArquivo($payload, $funcionarioId);
        $persistencia = $this->persistirPdf($empresaId, $funcionarioId, $mes, $ano, $filename, $content);

        $artifact = $this->sincronizarArtefatosDossie(
            $payload,
            $empresaId,
            $funcionarioId,
            $mes,
            $ano,
            $filename,
            $assinatura,
            $persistencia['relative_path'] ?? null,
            $persistencia['public_url'] ?? null,
        );

        return [
            'payload' => $payload,
            'filename' => $filename,
            'content' => $content,
            'hash' => $assinatura,
            'storage_path' => $persistencia['storage_path'] ?? null,
            'public_url' => $persistencia['public_url'] ?? null,
            'relative_path' => $persistencia['relative_path'] ?? null,
            'documento_id' => $artifact['documento_id'] ?? null,
            'dossie_evento_id' => $artifact['evento_id'] ?? null,
        ];
    }

    public function renderPdf(array $payload): string
    {
        $html = view('rh.holerite.pdf', [
            'empresa' => $payload['empresa'],
            'funcionario' => $payload['funcionario'],
            'mes' => $payload['mes'],
            'ano' => $payload['ano'],
            'salarioBase' => $payload['salarioBase'],
            'eventos' => $payload['eventos'],
            'descontos' => $payload['descontos'],
            'proventos' => $payload['proventos'],
            'liquido' => $payload['liquido'],
            'valores' => $payload['valores'],
            'assinatura_digital' => $payload['assinatura_digital'] ?? null,
        ])->render();

        $dompdf = new Dompdf(['enable_remote' => true]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    public function nomeArquivo(array $payload, ?int $funcionarioId = null): string
    {
        $nome = trim((string) data_get($payload, 'funcionario.nome', 'funcionario'));
        $nome = Str::slug(Str::limit($nome, 60, ''), '_');
        $nome = $nome !== '' ? $nome : 'funcionario_' . ($funcionarioId ?: 'rh');

        return sprintf(
            'holerite_%s_%02d_%04d_%s.pdf',
            $nome,
            RHCompetenciaHelper::numero($payload['mes'] ?? date('m')),
            (int) ($payload['ano'] ?? date('Y')),
            $funcionarioId ?: ((int) data_get($payload, 'funcionario.id', 0))
        );
    }

    private function gerarAssinatura(array $payload, int $empresaId, int $funcionarioId): string
    {
        $base = json_encode([
            'empresa_id' => $empresaId,
            'funcionario_id' => $funcionarioId,
            'mes' => $payload['mes'] ?? null,
            'ano' => $payload['ano'] ?? null,
            'liquido' => $payload['liquido'] ?? null,
            'salario_base' => $payload['salarioBase'] ?? null,
            'cpf' => data_get($payload, 'funcionario.cpf'),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return strtoupper(substr(hash('sha256', $base . '|' . config('app.key')), 0, 24));
    }

    private function persistirPdf(int $empresaId, int $funcionarioId, int $mes, int $ano, string $filename, string $content): array
    {
        $relativePath = sprintf('holerites/%d/%04d/%02d/%d/%s', $empresaId, $ano, RHCompetenciaHelper::numero($mes), $funcionarioId, $filename);
        $storagePath = storage_path('app/public/' . $relativePath);
        File::ensureDirectoryExists(dirname($storagePath));
        File::put($storagePath, $content);

        $appUrl = rtrim((string) config('app.url'), '/');
        $publicUrl = $appUrl !== '' ? $appUrl . '/storage/' . $relativePath : null;

        return [
            'relative_path' => $relativePath,
            'storage_path' => $storagePath,
            'public_url' => $publicUrl,
        ];
    }

    private function sincronizarArtefatosDossie(array $payload, int $empresaId, int $funcionarioId, int $mes, int $ano, string $filename, string $assinatura, ?string $relativePath, ?string $publicUrl): array
    {
        $resultado = [
            'documento_id' => null,
            'evento_id' => null,
        ];

        if ($empresaId <= 0 || $funcionarioId <= 0 || empty($relativePath)) {
            return $resultado;
        }

        if (Schema::hasTable('rh_dossies')) {
            RHDossie::query()->updateOrCreate(
                [
                    'empresa_id' => $empresaId,
                    'funcionario_id' => $funcionarioId,
                ],
                [
                    'status' => 'ativo',
                    'ultima_atualizacao_em' => now(),
                ]
            );
        }

        $documento = null;
        if (Schema::hasTable('rh_documentos')) {
            $documento = RHDocumento::query()->firstOrNew([
                'empresa_id' => $empresaId,
                'funcionario_id' => $funcionarioId,
                'origem' => 'holerite',
                'nome' => sprintf('Holerite %02d/%04d', RHCompetenciaHelper::numero($mes), $ano),
            ]);

            $payloadDocumento = [
                'empresa_id' => $empresaId,
                'funcionario_id' => $funcionarioId,
                'tipo' => 'holerite',
                'categoria' => 'folha',
                'nome' => sprintf('Holerite %02d/%04d', RHCompetenciaHelper::numero($mes), $ano),
                'arquivo' => $relativePath,
                'observacao' => 'Gerado automaticamente pelo motor de folha e integrado ao dossiê do colaborador.',
                'origem' => 'holerite',
                'metadata_json' => [
                    'mes' => RHCompetenciaHelper::numero($mes),
                    'ano' => $ano,
                    'hash' => $assinatura,
                    'filename' => $filename,
                    'public_url' => $publicUrl,
                    'funcionario_nome' => data_get($payload, 'funcionario.nome'),
                ],
                'usuario_id' => auth()->id(),
            ];

            foreach ($payloadDocumento as $coluna => $valor) {
                if ($this->documentColumnExists($coluna)) {
                    $documento->{$coluna} = $valor;
                }
            }

            if ($this->documentColumnExists('status')) {
                $documento->status = 'disponivel';
            }
            if ($this->documentColumnExists('hash_conteudo')) {
                $documento->hash_conteudo = $assinatura;
            }
            if ($this->documentColumnExists('validade')) {
                $documento->validade = Carbon::create($ano, RHCompetenciaHelper::numero($mes), 1)->endOfMonth()->toDateString();
            }

            $documento->save();
            $resultado['documento_id'] = (int) $documento->id;
        }

        if (Schema::hasTable('rh_dossie_eventos')) {
            $evento = RHDossieEvento::query()->firstOrNew([
                'empresa_id' => $empresaId,
                'funcionario_id' => $funcionarioId,
                'origem' => 'holerite',
                'source_uid' => sprintf('holerite:%d:%d:%04d:%02d', $empresaId, $funcionarioId, $ano, RHCompetenciaHelper::numero($mes)),
            ]);

            $eventoPayload = [
                'empresa_id' => $empresaId,
                'dossie_id' => Schema::hasTable('rh_dossies')
                    ? optional(RHDossie::query()->where('empresa_id', $empresaId)->where('funcionario_id', $funcionarioId)->first())->id
                    : null,
                'funcionario_id' => $funcionarioId,
                'categoria' => 'folha',
                'titulo' => sprintf('Holerite disponível %02d/%04d', RHCompetenciaHelper::numero($mes), $ano),
                'descricao' => 'Documento de folha gerado automaticamente e publicado no portal do colaborador.',
                'origem' => 'holerite',
                'source_uid' => sprintf('holerite:%d:%d:%04d:%02d', $empresaId, $funcionarioId, $ano, RHCompetenciaHelper::numero($mes)),
                'origem_id' => $resultado['documento_id'],
                'data_evento' => Carbon::create($ano, RHCompetenciaHelper::numero($mes), 1)->endOfMonth()->toDateString(),
                'visibilidade_portal' => true,
                'payload_json' => [
                    'documento_id' => $resultado['documento_id'],
                    'hash' => $assinatura,
                    'filename' => $filename,
                    'public_url' => $publicUrl,
                ],
                'usuario_id' => auth()->id(),
            ];

            foreach ($eventoPayload as $coluna => $valor) {
                if ($this->dossieEventColumnExists($coluna)) {
                    $evento->{$coluna} = $valor;
                }
            }

            $evento->save();
            $resultado['evento_id'] = (int) $evento->id;
        }

        return $resultado;
    }

    private function documentColumnExists(string $column): bool
    {
        static $columns = null;
        $columns ??= Schema::hasTable('rh_documentos') ? Schema::getColumnListing('rh_documentos') : [];

        return in_array($column, $columns, true);
    }

    private function dossieEventColumnExists(string $column): bool
    {
        static $columns = null;
        $columns ??= Schema::hasTable('rh_dossie_eventos') ? Schema::getColumnListing('rh_dossie_eventos') : [];

        return in_array($column, $columns, true);
    }

}
