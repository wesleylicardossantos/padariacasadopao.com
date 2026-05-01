<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\RHRescisao;
use App\Modules\RH\Support\RescisaoPdfDataBuilder;
use App\Modules\RH\Support\RescisaoPdfRenderer;
use App\Modules\RH\Support\RHContext;

class RHDocumentoGeradoController extends Controller
{
    public function __construct(
        private RescisaoPdfDataBuilder $pdfDataBuilder,
        private RescisaoPdfRenderer $pdfRenderer,
    ) {
    }

    public function contrato($id)
    {
        $empresaId = RHContext::empresaId(request());

        $funcionario = Funcionario::where('empresa_id', $empresaId)->findOrFail($id);
        $ficha = class_exists('\\App\\Models\\FuncionarioFichaAdmissao')
            ? \App\Models\FuncionarioFichaAdmissao::where('funcionario_id', $funcionario->id)->first()
            : null;

        $empresa = Empresa::with('cidade')->find($empresaId);
        $documentoNumero = sprintf('CT-%s-%s', str_pad((string) $funcionario->id, 6, '0', STR_PAD_LEFT), now()->format('Ymd'));
        $documentoHash = strtoupper(substr(sha1(implode('|', [
            (string) $empresaId,
            (string) $funcionario->id,
            (string) ($funcionario->cpf ?? ''),
            (string) ($ficha->data_admissao ?? ''),
            (string) ($funcionario->updated_at ?? now()),
        ])), 0, 16));

        return view('rh.documentos.contrato', compact(
            'funcionario',
            'ficha',
            'empresa',
            'documentoNumero',
            'documentoHash'
        ));
    }

    public function avisoFerias($id)
    {
        $funcionario = Funcionario::where('empresa_id', RHContext::empresaId(request()))->findOrFail($id);
        $ferias = class_exists('\\App\\Models\\RHFerias')
            ? \App\Models\RHFerias::where('funcionario_id', $funcionario->id)->orderBy('data_inicio', 'desc')->first()
            : null;

        return view('rh.documentos.aviso_ferias', compact('funcionario', 'ferias'));
    }

    public function termoDesligamento($id)
    {
        $funcionario = Funcionario::where('empresa_id', RHContext::empresaId(request()))->findOrFail($id);
        $desligamento = class_exists('\\App\\Models\\RHDesligamento')
            ? \App\Models\RHDesligamento::where('funcionario_id', $funcionario->id)->orderBy('data_desligamento', 'desc')->first()
            : null;

        return view('rh.documentos.termo_desligamento', compact('funcionario', 'desligamento'));
    }

    public function trct($id)
    {
        return $this->renderRescisaoDocument((int) $id, 'trct');
    }

    public function trctPdf($id)
    {
        return $this->renderRescisaoDocument((int) $id, 'trct', true);
    }

    public function tqrct($id)
    {
        return $this->renderRescisaoDocument((int) $id, 'tqrct');
    }

    public function tqrctPdf($id)
    {
        return $this->renderRescisaoDocument((int) $id, 'tqrct', true);
    }

    public function homologacao($id)
    {
        return $this->renderRescisaoDocument((int) $id, 'homologacao');
    }

    public function homologacaoPdf($id)
    {
        return $this->renderRescisaoDocument((int) $id, 'homologacao', true);
    }

    private function findRescisao(int $id): RHRescisao
    {
        $query = RHRescisao::query()
            ->with(['funcionario.cidade', 'funcionario.fichaAdmissao', 'itens', 'desligamento']);

        $empresaId = RHContext::empresaId(request());
        if ($empresaId > 0) {
            $query->where('empresa_id', $empresaId);
        }

        if ($this->isPortalExterno()) {
            $funcionarioId = (int) session('funcionario_portal.funcionario_id');
            abort_if($funcionarioId <= 0, 403, 'Sessão do portal inválida.');
            $query->where('funcionario_id', $funcionarioId);
        }

        return $query->findOrFail($id);
    }

    private function isPortalExterno(): bool
    {
        $routeName = (string) optional(request()->route())->getName();

        return str_starts_with((string) request()->path(), 'portal/')
            || str_starts_with($routeName, 'rh.portal_externo.');
    }

    private function renderRescisaoDocument(int $id, string $documentType, bool $forcePdf = false)
    {
        $rescisao = $this->findRescisao($id);
        $data = $this->buildRescisaoViewData($rescisao, $documentType);

        $requestedPdf = $forcePdf || request()->boolean('pdf') || request()->query('format') === 'pdf';

        if ($requestedPdf) {
            $driver = (string) request()->query('driver', 'dompdf');
            $html = view($this->resolvePdfView($documentType), $data)->render();
            return $this->pdfRenderer->render($html, $data['fileName'], $driver);
        }

        $data['previewLabel'] = $this->resolvePreviewLabel($documentType);

        return view('rh.documentos.preview_rescisao_pdf', $data);
    }

    private function resolvePdfView(string $documentType): string
    {
        return match ($documentType) {
            'trct' => 'rh.documentos.pdf.trct_juridico_pdf',
            default => 'rh.documentos.pdf.documento_rescisao_pdf',
        };
    }

    private function resolvePreviewLabel(string $documentType): string
    {
        return match ($documentType) {
            'trct' => 'Pré-visualização técnica do modelo jurídico oficial do TRCT',
            'tqrct' => 'Pré-visualização técnica do TQRCT ajustada para DOMPDF / Snappy PDF',
            'homologacao' => 'Pré-visualização técnica da homologação ajustada para DOMPDF / Snappy PDF',
            default => 'Pré-visualização técnica ajustada para DOMPDF / Snappy PDF',
        };
    }

    private function buildRescisaoViewData(RHRescisao $rescisao, string $documentType = 'trct'): array
    {
        $empresa = Empresa::with('cidade')->find($rescisao->empresa_id) ?? new Empresa();
        $funcionario = $rescisao->funcionario;
        $desligamento = $rescisao->desligamento;
        $itensProventos = $rescisao->itens->where('tipo', 'provento');
        $itensDescontos = $rescisao->itens->where('tipo', 'desconto');

        return array_merge(
            compact('empresa', 'funcionario', 'desligamento', 'rescisao', 'itensProventos', 'itensDescontos'),
            $this->pdfDataBuilder->build($empresa, $rescisao, $documentType),
        );
    }
}
