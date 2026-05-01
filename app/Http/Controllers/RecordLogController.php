<?php

namespace App\Http\Controllers;

use App\Models\RecordLog;
use Illuminate\Http\Request;

class RecordLogController extends Controller
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

    public function index(Request $request)
    {
        $this->ensureAdmin();

        $empresaId = $this->empresaId();
        $tipo = $request->get('tipo');
        $tabela = $request->get('tabela');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $data = RecordLog::with('usuario')
            ->where('empresa_id', $empresaId)
            ->when(!empty($tipo), function ($q) use ($tipo) {
                return $q->where('tipo', $tipo);
            })
            ->when(!empty($tabela), function ($q) use ($tabela) {
                return $q->where('tabela', 'like', "%{$tabela}%");
            })
            ->when(!empty($startDate), function ($q) use ($startDate) {
                return $q->whereDate('created_at', '>=', $startDate);
            })
            ->when(!empty($endDate), function ($q) use ($endDate) {
                return $q->whereDate('created_at', '<=', $endDate);
            })
            ->orderBy('id', 'desc')
            ->paginate(env('PAGINACAO', 20));

        $tipos = [
            '' => 'Todos',
            'criar' => 'Criar',
            'atualizar' => 'Atualizar',
            'deletar' => 'Deletar',
            'emissao' => 'Emissão',
            'cancelamento' => 'Cancelamento',
        ];

        return view('record_logs.index', $this->viewDefaults(compact('data', 'tipos')));
    }
}
