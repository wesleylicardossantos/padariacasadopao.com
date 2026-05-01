<?php

namespace App\Http\Controllers;

use App\Models\ApuracaoMensal;
use App\Models\Funcionario;
use App\Models\Produto;
use App\Models\RHPortalFuncionario;
use App\Models\RHRescisao;
use App\Models\RHDossie;
use App\Models\RHDossieEvento;
use App\Models\RHDocumento;
use App\Models\RHHoleriteEnvio;
use App\Services\RHHoleritePdfService;
use App\Support\RHCompetenciaHelper;
use App\Support\Cache\TenantCache;
use App\Support\Tenancy\InteractsWithTenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class RHPortalFuncionarioController extends Controller
{
    use InteractsWithTenantContext;

    public function __construct(private RHHoleritePdfService $holeritePdfService)
    {
        $this->middleware('tenant.context');
    }

    public function index(Request $request)
    {
        $funcionario = $this->funcionarioLogado($request);

        if (!$funcionario) {
            return redirect('/graficos')->with('flash_erro', 'Seu usuário não está vinculado a um funcionário para acessar o portal de holerites.');
        }

        return $this->renderPortal($request, $funcionario, false);
    }

    public function pdf(Request $request, int $apuracaoId)
    {
        $funcionario = $this->funcionarioLogado($request);

        if (!$funcionario) {
            abort(403, 'Usuário sem vínculo com funcionário.');
        }

        return $this->gerarPdfDoFuncionario($funcionario, $apuracaoId);
    }

    public function externo(Request $request)
    {
        $funcionario = $this->funcionarioPortal($request);

        if (!$funcionario) {
            return redirect('/portal')->with('flash_erro', 'Faça login para acessar o portal do funcionário.');
        }

        return $this->renderPortal($request, $funcionario, true, false);
    }

    public function holeritesExterno(Request $request)
    {
        $funcionario = $this->funcionarioPortal($request);

        if (!$funcionario) {
            return redirect('/portal')->with('flash_erro', 'Faça login para acessar o portal do funcionário.');
        }

        $acessoPortal = $this->acessoPortal($funcionario);
        if (!$this->temPermissaoPortal($acessoPortal, 'holerites.visualizar')) {
            return redirect()->route('rh.portal_externo.dashboard')->with('flash_erro', 'Seu perfil não possui acesso aos holerites.');
        }

        return $this->renderPortal($request, $funcionario, true, true);
    }

    public function produtosExterno(Request $request)
    {
        $funcionario = $this->funcionarioPortal($request);

        if (!$funcionario) {
            return redirect('/portal')->with('flash_erro', 'Faça login para acessar o portal do funcionário.');
        }

        $acessoPortal = $this->acessoPortal($funcionario);
        if (!$this->temPermissaoPortal($acessoPortal, 'produtos.visualizar')) {
            return redirect()->route('rh.portal_externo.dashboard')->with('flash_erro', 'Seu perfil não possui acesso à consulta de produtos.');
        }

        $busca = trim((string) $request->input('busca', ''));
        $pagina = max(1, (int) $request->input('page', 1));
        $ttl = now()->addSeconds(max(30, (int) config('infra.cache.tenant_ttl_seconds', 60)));

        $produtos = TenantCache::remember(
            'portal-produtos',
            (int) $funcionario->empresa_id,
            md5($busca . '|page:' . $pagina),
            $ttl,
            function () use ($funcionario, $request) {
                return Produto::query()
                    ->where('empresa_id', $funcionario->empresa_id)
                    ->when($request->filled('busca'), function ($q) use ($request) {
                        $busca = trim((string) $request->busca);
                        $q->where(function ($sub) use ($busca) {
                            $sub->where('nome', 'like', '%' . $busca . '%');
                            if (ctype_digit($busca)) {
                                $sub->orWhere('id', (int) $busca);
                            }
                        });
                    })
                    ->when(Schema::hasColumn('produtos', 'inativo'), fn ($q) => $q->where(function ($sub) {
                        $sub->whereNull('inativo')->orWhere('inativo', 0);
                    }))
                    ->select('id', 'nome', 'valor_venda')
                    ->orderBy('nome')
                    ->paginate(20)
                    ->appends($request->query());
            }
        );

        return view('rh.portal_funcionario.produtos_externo', compact('funcionario', 'produtos', 'acessoPortal'));
    }

    public function dossieExterno(Request $request)
    {
        $funcionario = $this->funcionarioPortal($request);

        if (!$funcionario) {
            return redirect('/portal')->with('flash_erro', 'Faça login para acessar o portal do funcionário.');
        }

        $acessoPortal = $this->acessoPortal($funcionario);
        if (!$this->temPermissaoPortal($acessoPortal, 'dossie.visualizar') && !$this->temPermissaoPortal($acessoPortal, 'documentos.visualizar')) {
            return redirect()->route('rh.portal_externo.dashboard')->with('flash_erro', 'Seu perfil não possui acesso ao dossiê.');
        }

        $dossie = Schema::hasTable('rh_dossies')
            ? RHDossie::query()->where('empresa_id', $funcionario->empresa_id)->where('funcionario_id', $funcionario->id)->first()
            : null;

        $eventos = ($dossie && Schema::hasTable('rh_dossie_eventos'))
            ? RHDossieEvento::query()
                ->where('empresa_id', $funcionario->empresa_id)
                ->where('funcionario_id', $funcionario->id)
                ->when(Schema::hasColumn('rh_dossie_eventos', 'visibilidade_portal'), fn ($q) => $q->where('visibilidade_portal', 1))
                ->orderByDesc('data_evento')
                ->orderByDesc('id')
                ->limit(30)
                ->get()
            : collect();

        $documentos = Schema::hasTable('rh_documentos')
            ? RHDocumento::query()
                ->where('empresa_id', $funcionario->empresa_id)
                ->where('funcionario_id', $funcionario->id)
                ->orderByDesc('id')
                ->limit(20)
                ->get()
            : collect();

        $rescisoes = Schema::hasTable('rh_rescisoes')
            ? RHRescisao::query()->where('empresa_id', $funcionario->empresa_id)->where('funcionario_id', $funcionario->id)->orderByDesc('data_rescisao')->limit(5)->get()
            : collect();

        return view('rh.portal_funcionario.dossie_externo', compact('funcionario', 'acessoPortal', 'dossie', 'eventos', 'documentos', 'rescisoes'));
    }

    public function pdfExterno(Request $request, int $apuracaoId)
    {
        $funcionario = $this->funcionarioPortal($request);

        if (!$funcionario) {
            abort(403, 'Funcionário não autenticado no portal.');
        }

        return $this->gerarPdfDoFuncionario($funcionario, $apuracaoId);
    }

    private function renderPortal(Request $request, Funcionario $funcionario, bool $externo = false, bool $somenteHolerites = false)
    {
        $cacheKey = md5(json_encode([
            'funcionario_id' => (int) $funcionario->id,
            'ano' => $request->input('ano'),
            'mes' => $request->input('mes'),
            'somente_holerites' => $somenteHolerites,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $ttl = now()->addSeconds(max(30, (int) config('infra.cache.tenant_ttl_seconds', 60)));

        $payload = TenantCache::remember(
            'portal-dashboard',
            (int) $funcionario->empresa_id,
            $cacheKey,
            $ttl,
            function () use ($request, $funcionario) {
                $baseApuracoes = ApuracaoMensal::query()
                    ->where('empresa_id', $funcionario->empresa_id)
                    ->where('funcionario_id', $funcionario->id)
                    ->when($request->filled('ano'), fn ($q) => $q->where('ano', (int) $request->ano))
                    ->when($request->filled('mes'), function ($q) use ($request) {
                        $mesNumero = RHCompetenciaHelper::numero($request->mes);
                        $mesNome = RHCompetenciaHelper::nome($mesNumero);
                        $mesPadded = RHCompetenciaHelper::padded($mesNumero);
                        $q->where(function ($sub) use ($mesNumero, $mesNome, $mesPadded) {
                            $sub->where('mes', $mesNome)
                                ->orWhere('mes', (string) $mesNumero)
                                ->orWhere('mes', $mesPadded)
                                ->orWhereRaw('LOWER(CAST(mes AS CHAR)) = ?', [mb_strtolower($mesNome)]);
                        });
                    });

                $apuracoes = (clone $baseApuracoes)
                    ->with('funcionario')
                    ->orderByDesc('ano')
                    ->orderByRaw(RHCompetenciaHelper::orderByMesCase('mes') . ' desc')
                    ->paginate(12)
                    ->appends($request->query());

                $historicoEnvios = collect();

                if (class_exists(RHHoleriteEnvio::class) && method_exists(RHHoleriteEnvio::class, 'query') && Schema::hasTable('rh_holerite_envios')) {
                    $queryEnvios = RHHoleriteEnvio::query()
                        ->with('lote')
                        ->where('funcionario_id', $funcionario->id);

                    if (Schema::hasColumn('rh_holerite_envios', 'empresa_id')) {
                        $queryEnvios->where('empresa_id', $funcionario->empresa_id);
                    } elseif (Schema::hasColumn('rh_holerite_envios', 'lote_id') && Schema::hasTable('rh_holerite_envio_lotes')) {
                        $queryEnvios->whereHas('lote', function ($q) use ($funcionario) {
                            if (Schema::hasColumn('rh_holerite_envio_lotes', 'empresa_id')) {
                                $q->where('empresa_id', $funcionario->empresa_id);
                            }
                        });
                    }

                    $historicoEnvios = $queryEnvios
                        ->orderByDesc('id')
                        ->limit(20)
                        ->get();
                }

                $competencias = ApuracaoMensal::query()
                    ->select('mes', 'ano')
                    ->where('empresa_id', $funcionario->empresa_id)
                    ->where('funcionario_id', $funcionario->id)
                    ->orderByDesc('ano')
                    ->orderByRaw(RHCompetenciaHelper::orderByMesCase('mes') . ' desc')
                    ->get();

                $agregado = ApuracaoMensal::query()
                    ->where('empresa_id', $funcionario->empresa_id)
                    ->where('funcionario_id', $funcionario->id)
                    ->selectRaw('COUNT(*) as total_holerites, COALESCE(SUM(valor_final), 0) as total_recebido')
                    ->first();

                $ultimoHolerite = ApuracaoMensal::query()
                    ->where('empresa_id', $funcionario->empresa_id)
                    ->where('funcionario_id', $funcionario->id)
                    ->orderByDesc('ano')
                    ->orderByRaw(RHCompetenciaHelper::orderByMesCase('mes') . ' desc')
                    ->first();

                return [
                    'apuracoes' => $apuracoes,
                    'historicoEnvios' => $historicoEnvios,
                    'competencias' => $competencias,
                    'resumo' => [
                        'total_holerites' => (int) ($agregado->total_holerites ?? 0),
                        'total_recebido' => (float) ($agregado->total_recebido ?? 0),
                        'ultimo_holerite' => $ultimoHolerite,
                    ],
                ];
            }
        );

        $acessoPortal = $this->acessoPortal($funcionario);
        $permissoesPortal = $acessoPortal ? $acessoPortal->permissoesEfetivas() : ['dashboard.visualizar', 'holerites.visualizar'];
        $atalhosPortal = [
            'holerites' => in_array('holerites.visualizar', $permissoesPortal, true),
            'produtos' => in_array('produtos.visualizar', $permissoesPortal, true),
            'dossie' => in_array('dossie.visualizar', $permissoesPortal, true) || in_array('documentos.visualizar', $permissoesPortal, true),
            'rescisao' => in_array('documentos.rescisao.visualizar', $permissoesPortal, true) || in_array('documentos.visualizar', $permissoesPortal, true),
        ];

        return view($externo ? ($somenteHolerites ? 'rh.portal_funcionario.holerites_externo' : 'rh.portal_funcionario.index_externo') : 'rh.portal_funcionario.index', array_merge($payload, compact(
            'funcionario',
            'externo',
            'acessoPortal',
            'permissoesPortal',
            'somenteHolerites'
        )));
    }

    private function gerarPdfDoFuncionario(Funcionario $funcionario, int $apuracaoId)
    {
        $apuracao = ApuracaoMensal::query()
            ->where('id', $apuracaoId)
            ->where('empresa_id', $funcionario->empresa_id)
            ->where('funcionario_id', $funcionario->id)
            ->firstOrFail();

        $pdf = $this->holeritePdfService->gerarPdfPorFuncionarioEmpresa(
            (int) $funcionario->empresa_id,
            (int) $funcionario->id,
            RHCompetenciaHelper::numero($apuracao->mes),
            (int) $apuracao->ano,
        );

        $disposition = request()->boolean('download') ? 'attachment' : 'inline';

        return response($pdf['content'])
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', $disposition . '; filename="' . $pdf['filename'] . '"');
    }



    public function documentosRescisaoExterno(Request $request)
    {
        $funcionario = $this->funcionarioPortal($request);
        if (!$funcionario) {
            return redirect()->route('rh.portal_externo.login')->with('flash_erro', 'Sessão do portal expirada.');
        }

        $acessoPortal = $this->acessoPortal($funcionario);
        if (!$this->temPermissaoPortal($acessoPortal, 'documentos.rescisao.visualizar') && !$this->temPermissaoPortal($acessoPortal, 'documentos.visualizar')) {
            return redirect()->route('rh.portal_externo.dashboard')->with('flash_erro', 'Seu perfil não possui acesso aos documentos de rescisão.');
        }

        $rescisoes = Schema::hasTable('rh_rescisoes')
            ? RHRescisao::query()->where('empresa_id', $funcionario->empresa_id)->where('funcionario_id', $funcionario->id)->orderByDesc('data_rescisao')->get()
            : collect();

        return view('rh.portal_funcionario.documentos_rescisao_externo', compact('funcionario', 'acessoPortal', 'rescisoes'));
    }

    private function acessoPortal(Funcionario $funcionario): ?RHPortalFuncionario
    {
        if (!Schema::hasTable('rh_portal_funcionarios')) {
            return null;
        }

        return RHPortalFuncionario::query()
            ->with(['perfil:id,empresa_id,nome,slug,descricao,ativo,permissoes'])
            ->where('empresa_id', $funcionario->empresa_id)
            ->where('funcionario_id', $funcionario->id)
            ->first();
    }

    private function temPermissaoPortal(?RHPortalFuncionario $acessoPortal, string $permissao): bool
    {
        if ($acessoPortal && $acessoPortal->hasPermission($permissao)) {
            return true;
        }

        if (Gate::allows('portal.' . $permissao)) {
            return true;
        }

        return !$acessoPortal && in_array($permissao, ['dashboard.visualizar', 'holerites.visualizar'], true);
    }

    private function funcionarioPortal(Request $request): ?Funcionario
    {
        $funcionarioId = (int) session('funcionario_portal.funcionario_id');
        $empresaId = (int) (session('funcionario_portal.empresa_id') ?: session('tenant.empresa_id') ?: 0);

        if ($funcionarioId <= 0 || $empresaId <= 0) {
            return null;
        }

        return Funcionario::query()
            ->where('id', $funcionarioId)
            ->where('empresa_id', $empresaId)
            ->first();
    }

    private function funcionarioLogado(Request $request): ?Funcionario
    {
        $usuarioId = (int) ($this->tenantUserId($request) ?: 0);
        $empresaId = $this->tenantEmpresaId($request);

        if ($usuarioId <= 0 || $empresaId <= 0) {
            return null;
        }

        return Funcionario::query()
            ->where('usuario_id', $usuarioId)
            ->where('empresa_id', $empresaId)
            ->first();
    }
}
