<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class RHMemoryService
{
    public static function salvar($empresaId, $dados)
    {
        DB::table('rh_memoria')->insert([
            'empresa_id' => $empresaId,
            'dados' => json_encode($dados),
            'created_at' => now()
        ]);
    }

    public static function historico($empresaId)
    {
        return DB::table('rh_memoria')
            ->where('empresa_id', $empresaId)
            ->orderBy('id','desc')
            ->limit(10)
            ->get();
    }
}
