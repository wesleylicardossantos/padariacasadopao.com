<?php

namespace App\Modules\RH\Support;

use App\Models\Funcionario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ResolveEmpresaId
{
    public static function fromRequest(?Request $request = null): int
    {
        $request = $request ?: request();

        $candidatos = [
            $request?->get('empresa_id'),
            $request?->input('empresa_id'),
            data_get(session('empresa_selecionada'), 'empresa_id'),
            data_get(session('empresa_selecionada'), 'id'),
            session('empresa_id'),
            data_get(session('user_logged'), 'empresa'),
            data_get(session('user_logged'), 'empresa_id'),
            data_get(session('usuario'), 'empresa'),
            data_get(session('usuario'), 'empresa_id'),
            optional(Auth::user())->empresa_id,
            data_get(Auth::user(), 'empresa'),
        ];

        foreach ($candidatos as $candidato) {
            if (is_numeric($candidato) && (int) $candidato > 0) {
                return (int) $candidato;
            }
        }

        return self::fallbackFromDatabase();
    }

    private static function fallbackFromDatabase(): int
    {
        try {
            if (!Schema::hasTable('funcionarios') || !Schema::hasColumn('funcionarios', 'empresa_id')) {
                return 0;
            }

            return (int) (Funcionario::query()
                ->whereNotNull('empresa_id')
                ->orderByDesc('id')
                ->value('empresa_id') ?: 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
