<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AdminProController extends Controller
{
    private function layoutDefaults()
    {
        return [
            'theme' => null,
            'colorDefault' => '',
            'video_url' => null,
            'ultimoAcesso' => null,
            'audio' => null,
            'casasDecimais' => 2,
        ];
    }

    public function index()
    {
        return $this->dashboard();
    }

    public function dashboard()
    {
        extract($this->layoutDefaults());

        $vendasHoje = 0;
        $faturamentoMes = 0;
        $contasPendentes = 0;
        $caixasAbertos = 0;
        $usuariosOnline = 0;
        $topProdutos = [];
        $topClientes = [];
        $graficoVendas = [];
        $estoqueBaixo = collect();

        try {
            if (DB::getSchemaBuilder()->hasTable('vendas')) {
                $vendasHoje = DB::table('vendas')->whereDate('created_at', date('Y-m-d'))->count();
                if (DB::getSchemaBuilder()->hasColumn('vendas', 'valor_total')) {
                    $faturamentoMes = (float) DB::table('vendas')
                        ->whereMonth('created_at', date('m'))
                        ->whereYear('created_at', date('Y'))
                        ->sum('valor_total');
                }
                if (DB::getSchemaBuilder()->hasColumn('vendas', 'valor_total')) {
                    $graficoTmp = DB::table('vendas')
                        ->selectRaw('DATE(created_at) as dia, SUM(COALESCE(valor_total,0)) as total')
                        ->whereMonth('created_at', date('m'))
                        ->whereYear('created_at', date('Y'))
                        ->groupBy(DB::raw('DATE(created_at)'))
                        ->orderBy('dia', 'asc')
                        ->limit(31)
                        ->get();
                    foreach ($graficoTmp as $g) {
                        $graficoVendas[] = ['dia' => date('d/m', strtotime($g->dia)), 'total' => (float) $g->total];
                    }
                }
                if (DB::getSchemaBuilder()->hasColumn('vendas', 'cliente_id') && DB::getSchemaBuilder()->hasTable('clientes')) {
                    $tmpClientes = DB::table('vendas')
                        ->leftJoin('clientes', 'clientes.id', '=', 'vendas.cliente_id')
                        ->selectRaw('COALESCE(clientes.nome, "Cliente") as nome, COUNT(*) as total')
                        ->groupBy('vendas.cliente_id', 'clientes.nome')
                        ->orderBy('total', 'desc')
                        ->limit(5)
                        ->get();
                    foreach ($tmpClientes as $c) {
                        $topClientes[] = ['nome' => $c->nome, 'total' => (int) $c->total];
                    }
                }
            }
        } catch (\Exception $e) {}

        try {
            if (DB::getSchemaBuilder()->hasTable('item_vendas') && DB::getSchemaBuilder()->hasTable('produtos') && DB::getSchemaBuilder()->hasColumn('item_vendas', 'produto_id')) {
                $tmpProdutos = DB::table('item_vendas')
                    ->leftJoin('produtos', 'produtos.id', '=', 'item_vendas.produto_id')
                    ->selectRaw('COALESCE(produtos.nome, "Produto") as nome, COUNT(*) as total')
                    ->groupBy('item_vendas.produto_id', 'produtos.nome')
                    ->orderBy('total', 'desc')
                    ->limit(5)
                    ->get();
                foreach ($tmpProdutos as $p) {
                    $topProdutos[] = ['nome' => $p->nome, 'total' => (int) $p->total];
                }
            }
        } catch (\Exception $e) {}

        try {
            if (DB::getSchemaBuilder()->hasTable('conta_pagar')) {
                if (DB::getSchemaBuilder()->hasColumn('conta_pagar', 'status')) {
                    $contasPendentes = DB::table('conta_pagar')->where('status', 'pendente')->count();
                } elseif (DB::getSchemaBuilder()->hasColumn('conta_pagar', 'estado')) {
                    $contasPendentes = DB::table('conta_pagar')->where('estado', 'pendente')->count();
                }
            }
        } catch (\Exception $e) {}

        try {
            if (DB::getSchemaBuilder()->hasTable('caixas') && DB::getSchemaBuilder()->hasColumn('caixas', 'status')) {
                $caixasAbertos = DB::table('caixas')->where('status', 'aberto')->count();
            } elseif (DB::getSchemaBuilder()->hasTable('abertura_caixas')) {
                if (DB::getSchemaBuilder()->hasColumn('abertura_caixas', 'status')) {
                    $caixasAbertos = DB::table('abertura_caixas')->where('status', 1)->count();
                } elseif (DB::getSchemaBuilder()->hasColumn('abertura_caixas', 'data_fechamento')) {
                    $caixasAbertos = DB::table('abertura_caixas')->whereNull('data_fechamento')->count();
                }
            }
        } catch (\Exception $e) {}

        try {
            if (DB::getSchemaBuilder()->hasTable('users')) {
                $usuariosOnline = DB::table('users')->count();
            }
        } catch (\Exception $e) {}

        try {
            if (DB::getSchemaBuilder()->hasTable('produtos') && DB::getSchemaBuilder()->hasColumn('produtos', 'estoque')) {
                $estoqueBaixo = DB::table('produtos')->select('id','nome','estoque')->where('estoque', '<', 10)->orderBy('estoque','asc')->limit(8)->get();
            }
        } catch (\Exception $e) {}

        return view('admin.pro_dashboard', compact('theme','colorDefault','video_url','ultimoAcesso','audio','casasDecimais','vendasHoje','faturamentoMes','contasPendentes','caixasAbertos','usuariosOnline','topProdutos','topClientes','graficoVendas','estoqueBaixo'));
    }

    public function monitor()
    {
        extract($this->layoutDefaults());
        $dbName = env('DB_DATABASE');
        $tables = [];
        $dbSizeMb = null;
        $erroCountHoje = 0;
        $logsRecentes = collect();
        $backups = [];
        $tempoResposta = 'OK';

        try { $tables = DB::select('SHOW TABLES'); } catch (\Exception $e) {}
        try {
            $row = DB::selectOne('SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS total FROM information_schema.TABLES WHERE table_schema = ?', [$dbName]);
            if ($row && isset($row->total)) { $dbSizeMb = $row->total; }
        } catch (\Exception $e) {}
        try {
            if (DB::getSchemaBuilder()->hasTable('erro_logs')) {
                $logsRecentes = DB::table('erro_logs')->orderBy('id', 'desc')->limit(10)->get();
                if (DB::getSchemaBuilder()->hasColumn('erro_logs', 'created_at')) {
                    $erroCountHoje = DB::table('erro_logs')->whereDate('created_at', date('Y-m-d'))->count();
                } else {
                    $erroCountHoje = DB::table('erro_logs')->count();
                }
            }
        } catch (\Exception $e) {}
        $backupPath = storage_path('backups');
        if (File::exists($backupPath)) {
            foreach (File::files($backupPath) as $file) {
                $backups[] = ['nome' => $file->getFilename(), 'tamanho' => round($file->getSize() / 1024 / 1024, 2) . ' MB', 'data' => date('d/m/Y H:i', $file->getMTime())];
            }
        }
        return view('admin.monitor', compact('theme','colorDefault','video_url','ultimoAcesso','audio','casasDecimais','dbName','tables','dbSizeMb','erroCountHoje','logsRecentes','backups','tempoResposta'));
    }

    public function audit(Request $request)
    {
        extract($this->layoutDefaults());
        $logs = collect();
        try {
            if (DB::getSchemaBuilder()->hasTable('record_logs')) {
                $query = DB::table('record_logs')->orderBy('id', 'desc');
                if ($request->filled('tabela') && DB::getSchemaBuilder()->hasColumn('record_logs', 'tabela')) { $query->where('tabela', $request->tabela); }
                if ($request->filled('tipo') && DB::getSchemaBuilder()->hasColumn('record_logs', 'tipo')) { $query->where('tipo', $request->tipo); }
                if ($request->filled('data_inicial') && DB::getSchemaBuilder()->hasColumn('record_logs', 'created_at')) { $query->whereDate('created_at', '>=', $request->data_inicial); }
                if ($request->filled('data_final') && DB::getSchemaBuilder()->hasColumn('record_logs', 'created_at')) { $query->whereDate('created_at', '<=', $request->data_final); }
                $logs = $query->limit(100)->get();
            }
        } catch (\Exception $e) {}
        return view('admin.audit', compact('theme','colorDefault','video_url','ultimoAcesso','audio','casasDecimais','logs'));
    }

    public function backups()
    {
        extract($this->layoutDefaults());
        $items = [];
        $backupPath = storage_path('backups');
        if (File::exists($backupPath)) {
            foreach (File::files($backupPath) as $file) {
                $items[] = ['nome' => $file->getFilename(), 'tamanho' => round($file->getSize() / 1024 / 1024, 2) . ' MB', 'data' => date('d/m/Y H:i', $file->getMTime())];
            }
        }
        return view('admin.backups', compact('theme','colorDefault','video_url','ultimoAcesso','audio','casasDecimais','items'));
    }

    public function errors()
    {
        extract($this->layoutDefaults());
        $items = collect();
        try { if (DB::getSchemaBuilder()->hasTable('erro_logs')) { $items = DB::table('erro_logs')->orderBy('id', 'desc')->limit(100)->get(); } } catch (\Exception $e) {}
        return view('admin.errors', compact('theme','colorDefault','video_url','ultimoAcesso','audio','casasDecimais','items'));
    }

    public function usersOnline()
    {
        extract($this->layoutDefaults());
        $items = collect();
        try { if (DB::getSchemaBuilder()->hasTable('users')) { $items = DB::table('users')->orderBy('id', 'desc')->limit(50)->get(); } } catch (\Exception $e) {}
        return view('admin.users_online', compact('theme','colorDefault','video_url','ultimoAcesso','audio','casasDecimais','items'));
    }

    public function backupNow()
    {
        $backupPath = storage_path('backups');
        if (!File::exists($backupPath)) { File::makeDirectory($backupPath, 0755, true); }
        $db = env('DB_DATABASE');
        $host = env('DB_HOST');
        $port = env('DB_PORT', '3306');
        $user = env('DB_USERNAME');
        $pass = env('DB_PASSWORD');
        $file = $backupPath . '/backup_' . date('Y_m_d_H_i_s') . '.sql';
        $command = 'mysqldump --host=' . escapeshellarg($host) . ' --port=' . escapeshellarg($port) . ' --user=' . escapeshellarg($user) . ' --password=' . escapeshellarg($pass) . ' ' . escapeshellarg($db) . ' > ' . escapeshellarg($file);
        @exec($command);
        return redirect('/__admin/backups');
    }

    public function salesChart()
    {
        $data = [];
        try {
            if (DB::getSchemaBuilder()->hasTable('vendas') && DB::getSchemaBuilder()->hasColumn('vendas','valor_total')) {
                $rows = DB::table('vendas')->selectRaw('DATE(created_at) as dia, SUM(COALESCE(valor_total,0)) as total')->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->groupBy(DB::raw('DATE(created_at)'))->orderBy('dia', 'asc')->limit(31)->get();
                foreach ($rows as $r) { $data[] = ['dia' => date('d/m', strtotime($r->dia)), 'total' => (float) $r->total]; }
            }
        } catch (\Exception $e) {}
        return response()->json($data);
    }

    public function lowStock()
    {
        extract($this->layoutDefaults());
        $items = collect();
        try {
            if (DB::getSchemaBuilder()->hasTable('produtos') && DB::getSchemaBuilder()->hasColumn('produtos', 'estoque')) {
                $items = DB::table('produtos')->select('id', 'nome', 'estoque')->where('estoque', '<', 10)->orderBy('estoque', 'asc')->limit(50)->get();
            }
        } catch (\Exception $e) {}
        return view('admin.low_stock', compact('theme','colorDefault','video_url','ultimoAcesso','audio','casasDecimais','items'));
    }
}
