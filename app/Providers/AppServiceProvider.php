<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Models\Usuario;
use App\Models\Funcionario;
use App\Models\VideoAjuda;
use Illuminate\Support\Facades\URL;
use App\Support\BrandingResolver;
use App\Models\Estoque;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\VendaCaixa;
use App\Observers\FuncionarioObserver;
use App\Observers\ContaPagarObserver;
use App\Observers\ContaReceberObserver;
use App\Observers\DashboardCacheObserver;
use App\Observers\EstoqueObserver;
use App\Modules\Estoque\Support\LegacyStockWriteGuard;
use App\Modules\Financeiro\Support\FinancialMutationGuard;
use App\Support\Observability\SlowQueryMonitor;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(\App\Modules\RH\Repositories\FuncionarioRepository::class);
        $this->app->bind(\App\Modules\RH\Services\RHFolhaModuleService::class);
        $this->app->singleton(LegacyStockWriteGuard::class);
        $this->app->singleton(FinancialMutationGuard::class);
    }

    public function boot()
    {
        $appUrlScheme = parse_url((string) config('app.url'), PHP_URL_SCHEME);
        $shouldForceHttpsUrls = (config('app.force_https') || $appUrlScheme === 'https')
            && ! app()->runningInConsole()
            && ! app()->environment(['local', 'testing']);

        if ($shouldForceHttpsUrls) {
            URL::forceScheme('https');
        }

        Paginator::useBootstrap();

        view()->composer('*', function ($view) {

            $ultimoAcesso = null;
            $branding = app(BrandingResolver::class)->resolve(request());
            $view->with('empresaBranding', $branding);

            if (!get_id_user()) {
                return;
            }

            $user = Usuario::find(get_id_user());

            if ($user) {
                $theme = $user->theme;
                $casasDecimais = 2;
                $colorDefault = '';

                if ($theme) {
                    if ($theme->cabecalho == 'headercolor1') $colorDefault = '#0727D7';
                    if ($theme->cabecalho == 'headercolor2') $colorDefault = '#23282C';
                    if ($theme->cabecalho == 'headercolor3') $colorDefault = '#E10A1F';
                    if ($theme->cabecalho == 'headercolor4') $colorDefault = '#157D4C';
                    if ($theme->cabecalho == 'headercolor5') $colorDefault = '#673AB7';
                    if ($theme->cabecalho == 'headercolor6') $colorDefault = '#795548';
                    if ($theme->cabecalho == 'headercolor7') $colorDefault = '#D3094E';
                    if ($theme->cabecalho == 'headercolor8') $colorDefault = '#FF9800';
                }

                $video_url = $this->getVideoUrl();
                $usuario = Usuario::find(get_id_user());
                $ultimoAcessoEm = null;
                if ($usuario) {
                    $hashSessao = (string) data_get(session('user_logged'), 'hash', '');
                    $ultimoAcesso = $usuario->ultimoAcesso();
                    $ultimoAcessoEm = $usuario->ultimoAcessoExibicao($hashSessao);
                }

                $rotaAtiva = $this->rotaAtiva();

                $view->with('casasDecimais', $casasDecimais);
                $view->with('user', $user);
                $view->with('ultimoAcesso', $ultimoAcesso);
                $view->with('ultimoAcessoEm', $ultimoAcessoEm);
                $view->with('colorDefault', $colorDefault);
                $view->with('theme', $theme);
                $view->with('rotaAtiva', $rotaAtiva);
                $view->with('video_url', $video_url);
                $funcionarioPortal = Funcionario::query()
                    ->where('usuario_id', $user->id)
                    ->where('empresa_id', (int) data_get(session('user_logged'), 'empresa', 0))
                    ->first();

                $view->with('audio', $user->aviso_sonoro);
                $view->with('funcionarioPortalDisponivel', (bool) $funcionarioPortal);
                $view->with('funcionarioPortalVinculado', $funcionarioPortal);
            }
        });

        view()->composer('rh.*', function ($view) {
            $defaults = [
                'mes' => (int) date('m'),
                'ano' => (int) date('Y'),
                'nome' => '',
                'linhas' => collect(),
                'detalhes' => [],
                'alertas' => [],
                'alertasFinanceiros' => [],
                'resumo' => collect(),
                'data' => collect(),
                'funcionario' => null,
                'funcionariosLista' => collect(),
                'receitaBruta' => 0,
                'despesasOperacionais' => 0,
                'folha' => 0,
                'folhaTotal' => 0,
                'margemOperacional' => 0,
                'resultadoOperacional' => 0,
                'totalFuncionarios' => 0,
                'ativos' => 0,
                'inativos' => 0,
                'folhaBase' => 0,
                'turnover' => 0,
                'admissoesMes' => 0,
                'desligamentosMes' => 0,
                'faltasMes' => 0,
                'atestadosMes' => 0,
                'atrasosMes' => 0,
                'movimentacoesRecentes' => collect(),
                'feriasProximas' => collect(),
                'setores' => [],
                'ranking' => [],
                'placar' => [],
                'insights' => [],
                'prioridades' => [],
                'recomendacoes' => [],
                'anomalias' => [],
                'forecast' => ['horizonte' => [], 'resumo' => []],
                'serie' => [],
                'timeline' => [],
                'drivers' => [],
                'cenarios' => [],
                'radar' => [],
                'mapaRisco' => [],
                'topPagar' => [],
                'topReceber' => [],
                'custosRh' => [],
                'categoriasPagar' => [],
                'categoriasReceber' => [],
                'snapshotFinanceiro' => [],
                'ultimoFechamento' => null,
                'fechamentoAtual' => null,
                'fechamentos' => collect(),
                'competenciaFechada' => false,
                'scoreSaude' => 0,
                'statusSaude' => 'atencao',
                'decisao' => ['titulo' => 'Sem decisão', 'descricao' => ''],
                'dre' => [],
            ];

            $data = $view->getData();
            foreach ($defaults as $key => $value) {
                if (!array_key_exists($key, $data)) {
                    $view->with($key, $value);
                }
            }
        });

        Estoque::observe(EstoqueObserver::class);
        ContaReceber::observe(ContaReceberObserver::class);
        ContaPagar::observe(ContaPagarObserver::class);

        ContaReceber::observe(DashboardCacheObserver::class);
        ContaPagar::observe(DashboardCacheObserver::class);
        Venda::observe(DashboardCacheObserver::class);
        VendaCaixa::observe(DashboardCacheObserver::class);
        Produto::observe(DashboardCacheObserver::class);
        Funcionario::observe(FuncionarioObserver::class);

        if (config('infra.slow_query.enabled', true)) {
            DB::listen(fn ($query) => app(SlowQueryMonitor::class)->handle($query));
        }

        view()->composer('rh.folha.index', function ($view) {
            $data = $view->getData();
            if (!array_key_exists('funcionarios', $data)) {
                $view->with('funcionarios', new LengthAwarePaginator([], 0, 20));
            }
        });
    }

    private function getVideoUrl()
    {
        if (url()->full()) {
            $url = url()->full();
            try {
                $video = VideoAjuda::where('url_sistema', $url)->first();
                if ($video == null) return "";
                return $video->url_video;
            } catch (\Exception $e) {
                return "";
            }
        }
    }

        private function rotaAtiva()
    {
        $path = trim(parse_url(request()->getRequestUri(), PHP_URL_PATH), '/');
        $segments = array_values(array_filter(explode('/', $path)));
        $first = $segments[0] ?? '';

        // RH sempre ativo nas rotas novas
        if ($first === 'rh') {
            return 'RH';
        }

        // Mantém RH ativo mesmo em telas legadas que ainda pertencem ao módulo
        $rotasRHLegadas = ['funcionarios', 'eventoSalario', 'funcionarioEventos', 'apuracaoMensal'];
        if (in_array($first, $rotasRHLegadas)) {
            return 'RH';
        }

        $rotaSuper = [
            'empresas', 'planos', 'ibpt', 'contrato', 'financeiro', 'cidades', 'representantes',
            'online', 'etiquetas', 'relatorioSuper', 'ticketsSuper', 'cidadeDelivery',
            'categoriaMasterDelivery', 'produtosDestaque', 'planosPendentes', 'pesquisa', 'alertas',
            'errosLog', 'config', 'appUpdate'
        ];

        $rotaDeCadastros = [
            'categorias', 'produtos', 'clientes', 'fornecedores', 'transportadoras', 'categoria-servico', 'servicos',
            'categoriasConta', 'veiculos', 'usuarios', 'marcas', 'contaBancaria', 'acessores', 'gruposCliente',
            'listaDePrecos', 'formasPagamento'
        ];

        $rotaDeEntradas = ['compraFiscal', 'compraManual', 'compras', 'cotacao', 'dfe', 'devolucao'];
        $rotaDeEstoque = ['estoque', 'inventario', 'transferencia'];
        $rotaFinanceiro = ['conta-pagar', 'conta-receber', 'fluxoCaixa', 'graficos'];
        $rotaConfig = ['configNF', 'escritorio', 'naturezas', 'tributos', 'enviarXml', 'tickets', 'configEmail', 'filial'];
        $rotaPedidos = ['pedidos', 'deliveryComplemento', 'telasPedido', 'controleCozinha', 'mesas'];
        $rotaVenda = ['caixa', 'vendas', 'frenteCaixa', 'orcamentoVenda', 'ordemServico', 'vendasEmCredito', 'agendamentos', 'trocas', 'nfse', 'nferemessa'];
        $rotaCTe = ['cte', 'categoriaDespesa'];
        $rotaCTeOs = ['cteos'];
        $rotaMDFe = ['mdfe'];
        $rotaEvento = ['eventos'];
        $rotaLocacao = ['locacao'];
        $rotaRelatorio = ['relatorios', 'dre'];
        $rotaEcommerce = ['categoriaEcommerce', 'produtoEcommerce', 'configEcommerce', 'carrosselEcommerce', 'pedidosEcommerce', 'autorPost', 'categoriaPosts', 'postBlog', 'contatoEcommerce', 'clienteEcommerce', 'informativoEcommerce', 'cuponsEcommerce'];
        $rotaNuvemShop = ['nuvemshop', 'nuvemshop-pedidos', 'nuvemshop-produtos', 'nuvemshop-clientes'];
        $rotaIfood = ['ifood'];
        $rotaDelivery = ['deliveryCategoria', 'configDelivery', 'deliveryProduto', 'deliveryComplemento', 'funcionamentoDelivery', 'push', 'tamanhosPizza', 'clientesDelivery', 'categoriaDeLoja', 'pedidosDelivery', 'bairrosDeliveryLoja', 'codigoDesconto', 'carrosselDelivery', 'motoboys', 'pedidosMesa', 'mesas'];

        if (in_array($first, $rotaSuper)) return 'SUPER';
        if (in_array($first, $rotaDeCadastros)) return 'Cadastros';
        if (in_array($first, $rotaDeEntradas)) return 'Entradas';
        if (in_array($first, $rotaDeEstoque)) return 'Estoque';
        if (in_array($first, $rotaFinanceiro)) return 'Financeiro';
        if (in_array($first, $rotaConfig)) return 'Configurações';
        if (in_array($first, $rotaVenda)) return 'Vendas';
        if (in_array($first, $rotaCTe)) return 'CTe';
        if (in_array($first, $rotaCTeOs)) return 'CTe Os';
        if (in_array($first, $rotaMDFe)) return 'MDFe';
        if (in_array($first, $rotaEvento)) return 'Eventos';
        if (in_array($first, $rotaRelatorio)) return 'Relatórios';
        if (in_array($first, $rotaLocacao)) return 'Locação';
        if (in_array($first, $rotaPedidos)) return 'Pedidos';
        if (in_array($first, $rotaEcommerce)) return 'Ecommerce';
        if (in_array($first, $rotaNuvemShop)) return 'Nuvem Shop';
        if (in_array($first, $rotaIfood)) return 'iFood';
        if (in_array($first, $rotaDelivery)) return 'Delivery';

        return '';
    }

}
