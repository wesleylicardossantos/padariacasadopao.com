<?php

namespace App\Http\Controllers;

use App\Models\ApuracaoMensal;
use App\Models\RHFolhaFechamento;
use App\Services\RHFolhaCompetenciaService;
use App\Support\RHCompetenciaHelper;
use App\Modules\RH\Support\RHContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class RHFolhaProcessamentoController extends Controller
{
    public function __construct(private RHFolhaCompetenciaService $service)
    {
    }

    public function index(Request $request)
    {
        $empresaId = $this->empresaId($request);
        $mes = (int) ($request->get('mes_competencia') ?: date('m'));
        $ano = (int) ($request->get('ano_competencia') ?: date('Y'));

        $apuracoesQuery = ApuracaoMensal::query()
            ->with('funcionario')
            ->when($empresaId > 0 && Schema::hasColumn('apuracao_mensals', 'empresa_id'), fn ($q) => $q->where('empresa_id', $empresaId))
            ->where('mes', RHCompetenciaHelper::nome($mes))
            ->where('ano', $ano);

        $apuracoes = (clone $apuracoesQuery)->orderByDesc('id')->paginate(20)->appends($request->query());

        $resumo = [
            'registros' => (clone $apuracoesQuery)->count(),
            'total_proventos' => (float) (clone $apuracoesQuery)->sum(Schema::hasColumn('apuracao_mensals', 'total_proventos') ? 'total_proventos' : 'valor_final'),
            'total_descontos' => (float) ((Schema::hasColumn('apuracao_mensals', 'total_descontos')) ? (clone $apuracoesQuery)->sum('total_descontos') : 0),
            'total_liquido' => (float) (clone $apuracoesQuery)->sum(Schema::hasColumn('apuracao_mensals', 'liquido') ? 'liquido' : 'valor_final'),
        ];

        $fechamento = Schema::hasTable('rh_folha_fechamentos')
            ? RHFolhaFechamento::query()
                ->when($empresaId > 0, fn ($q) => $q->where('empresa_id', $empresaId))
                ->where('mes', $mes)
                ->where('ano', $ano)
                ->first()
            : null;

        $status = $fechamento?->status ?? ($resumo['registros'] > 0 ? 'processada' : 'aberta');

        return view('rh.folha_processamento.index', compact(
            'mes',
            'ano',
            'apuracoes',
            'resumo',
            'fechamento',
            'status'
        ));
    }

    public function processar(Request $request)
    {
        $data = $request->validate([
            'mes_competencia' => 'required|integer|min:1|max:12',
            'ano_competencia' => 'required|integer|min:2000|max:2100',
            'vencimento_folha' => 'nullable|date',
            'filial_id' => 'nullable',
            'sobrescrever' => 'nullable|in:1',
            'integrar_financeiro' => 'nullable|in:1',
        ]);

        try {
            $gerados = $this->service->processar(
                $this->empresaId($request),
                (int) $data['mes_competencia'],
                (int) $data['ano_competencia'],
                $request->boolean('sobrescrever'),
                $request->boolean('integrar_financeiro'),
                $data['vencimento_folha'] ?? null,
                $request->filled('filial_id') && $request->input('filial_id') !== '-1' ? (int) $request->input('filial_id') : null
            );

            return redirect()->route('rh.folha.processamento.index', [
                'mes_competencia' => (int) $data['mes_competencia'],
                'ano_competencia' => (int) $data['ano_competencia'],
            ])->with('flash_sucesso', 'Processamento concluído com sucesso. Registros gerados: ' . $gerados);
        } catch (\Throwable $e) {
            __saveLogError($e, $this->empresaId($request));

            return redirect()->route('rh.folha.processamento.index', [
                'mes_competencia' => (int) $data['mes_competencia'],
                'ano_competencia' => (int) $data['ano_competencia'],
            ])->with('flash_erro', 'Falha ao processar a folha: ' . $e->getMessage());
        }
    }

    private function empresaId(Request $request): int
    {
        return RHContext::empresaId($request);
    }
}
