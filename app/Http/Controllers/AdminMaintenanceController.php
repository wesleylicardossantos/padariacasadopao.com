<?php

namespace App\Http\Controllers;

use App\Models\AberturaCaixa;
use App\Models\ContaPagar;
use App\Models\ErroLog;
use App\Models\RecordLog;
use App\Models\Usuario;
use App\Models\VendaCaixa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class AdminMaintenanceController extends Controller
{
    private function empresaId()
    {
        $user = session('user_logged');
        return $user['empresa'] ?? null;
    }

    private function ensureAdmin()
    {
        $user = session('user_logged');
        if (!$user) {
            abort(403, 'Sessão inválida');
        }

        $login = $user['login'] ?? '';
        $isAdmin = ($user['adm'] ?? 0) == 1;
        if (!$isAdmin && !isSuper($login)) {
            abort(403, 'Acesso restrito ao administrador');
        }
    }

    private function viewDefaults(array $data = [])
    {
        $data['theme'] = $data['theme'] ?? null;
        $data['colorDefault'] = $data['colorDefault'] ?? '';
        $data['video_url'] = $data['video_url'] ?? null;
        $data['ultimoAcesso'] = $data['ultimoAcesso'] ?? null;
        $data['casasDecimais'] = $data['casasDecimais'] ?? 2;
        $data['audio'] = $data['audio'] ?? null;
        return $data;
    }

    private function saveAudit($tipo, $tabela, $registroId)
    {
        try {
            if (function_exists('__saveLog')) {
                __saveLog([
                    'tipo' => $tipo,
                    'usuario_log_id' => get_id_user(),
                    'tabela' => $tabela,
                    'registro_id' => $registroId,
                    'empresa_id' => $this->empresaId(),
                ]);
            }
        } catch (\Exception $e) {
        }
    }

    public function index()
    {
        $this->ensureAdmin();

        $empresaId = $this->empresaId();
        $hoje = date('Y-m-d');

        $vendasHoje = VendaCaixa::where('empresa_id', $empresaId)
            ->whereDate('created_at', $hoje)
            ->sum('valor_total');

        $contasPendentes = ContaPagar::where('empresa_id', $empresaId)
            ->where('status', 0)
            ->count();

        $caixasAbertos = AberturaCaixa::where('empresa_id', $empresaId)
            ->where('status', 1)
            ->count();

        $usuariosAtivos = Usuario::where('empresa_id', $empresaId)
            ->where('ativo', 1)
            ->count();

        $logsRecentes = RecordLog::with('usuario')
            ->where('empresa_id', $empresaId)
            ->orderBy('id', 'desc')
            ->limit(12)
            ->get();

        $errosRecentes = ErroLog::where('empresa_id', $empresaId)
            ->orderBy('id', 'desc')
            ->limit(8)
            ->get();

        return view('admin.maintenance', $this->viewDefaults(compact(
            'vendasHoje',
            'contasPendentes',
            'caixasAbertos',
            'usuariosAtivos',
            'logsRecentes',
            'errosRecentes'
        )));
    }

    public function clearAll()
    {
        $this->ensureAdmin();

        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        $this->saveAudit('atualizar', 'empresas', $this->empresaId());

        session()->flash('flash_sucesso', 'Cache, config, rotas e views limpos com sucesso!');
        return redirect()->route('admin.panel');
    }

    public function backupDatabase()
    {
        $this->ensureAdmin();

        $db = env('DB_DATABASE');
        $tables = DB::select('SHOW TABLES');
        $sql = "-- Backup simples do banco {$db}\n";
        $sql .= "-- Gerado em: " . date('d/m/Y H:i:s') . "\n\n";

        foreach ($tables as $table) {
            $tableName = array_values((array) $table)[0];
            $rows = DB::table($tableName)->get();

            foreach ($rows as $row) {
                $values = [];
                foreach ((array) $row as $value) {
                    $values[] = $value === null ? 'NULL' : "'" . addslashes($value) . "'";
                }
                $sql .= "INSERT INTO `{$tableName}` VALUES(" . implode(',', $values) . ");\n";
            }
            $sql .= "\n";
        }

        $this->saveAudit('atualizar', 'empresas', $this->empresaId());

        return response($sql)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="backup_' . $db . '_' . date('Ymd_His') . '.sql"');
    }
}
