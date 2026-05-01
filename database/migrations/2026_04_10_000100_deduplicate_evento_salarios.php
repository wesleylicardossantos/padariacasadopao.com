<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('evento_salarios')) {
            return;
        }

        DB::transaction(function () {
            $rows = DB::table('evento_salarios')
                ->select([
                    'id', 'empresa_id', 'nome',
                    Schema::hasColumn('evento_salarios', 'codigo') ? 'codigo' : DB::raw('NULL as codigo'),
                    'tipo', 'metodo', 'condicao', 'tipo_valor', 'ativo',
                    Schema::hasColumn('evento_salarios', 'padrao_sistema') ? 'padrao_sistema' : DB::raw('0 as padrao_sistema'),
                    Schema::hasColumn('evento_salarios', 'sistema_padrao') ? 'sistema_padrao' : DB::raw('0 as sistema_padrao'),
                    Schema::hasColumn('evento_salarios', 'ordem_calculo') ? 'ordem_calculo' : DB::raw('0 as ordem_calculo'),
                    'created_at', 'updated_at',
                ])
                ->orderBy('empresa_id')
                ->orderBy('id')
                ->get();

            $groups = [];
            foreach ($rows as $row) {
                $key = $this->normalizedKey($row);
                $groups[$row->empresa_id . '|' . $key][] = $row;
            }

            foreach ($groups as $items) {
                if (count($items) <= 1) {
                    continue;
                }

                usort($items, function ($a, $b) {
                    return $this->score($b) <=> $this->score($a)
                        ?: ($b->ativo <=> $a->ativo)
                        ?: ($b->updated_at <=> $a->updated_at)
                        ?: ($b->id <=> $a->id);
                });

                $keeper = array_shift($items);
                $duplicateIds = array_map(fn ($item) => $item->id, $items);

                $this->rebindFuncionarioEventos($duplicateIds, $keeper->id);
                $this->rebindApuracaoEventos($duplicateIds, $keeper->id);

                DB::table('evento_salarios')->whereIn('id', $duplicateIds)->delete();
            }
        });
    }

    public function down(): void
    {
        // Migração destrutiva de saneamento; sem rollback automático.
    }

    private function normalizedKey(object $row): string
    {
        $codigo = trim((string) ($row->codigo ?? ''));
        if ($codigo !== '') {
            return 'COD:' . mb_strtoupper($codigo);
        }

        return 'NOME:' . mb_strtoupper(trim((string) ($row->nome ?? '')));
    }

    private function score(object $row): int
    {
        $score = 0;
        $score += (int) ($row->padrao_sistema ?? 0) * 1000;
        $score += (int) ($row->sistema_padrao ?? 0) * 1000;
        $score += (int) ($row->ativo ?? 0) * 100;

        $codigo = mb_strtoupper(trim((string) ($row->codigo ?? $row->nome ?? '')));
        if (in_array($codigo, ['SALARIO', 'SALÁRIO', 'INSS', 'IRRF', 'FGTS'], true)) {
            $score += 50;
        }

        if (($row->condicao ?? null) === 'soma') {
            $score += 5;
        }

        return $score;
    }

    private function rebindFuncionarioEventos(array $duplicateIds, int $keeperId): void
    {
        if (!$duplicateIds || !Schema::hasTable('funcionario_eventos')) {
            return;
        }

        $rows = DB::table('funcionario_eventos')
            ->whereIn('evento_id', $duplicateIds)
            ->orderBy('id')
            ->get();

        foreach ($rows as $row) {
            $existing = DB::table('funcionario_eventos')
                ->where('funcionario_id', $row->funcionario_id)
                ->where('evento_id', $keeperId)
                ->first();

            if ($existing) {
                DB::table('funcionario_eventos')->where('id', $row->id)->delete();
                continue;
            }

            DB::table('funcionario_eventos')->where('id', $row->id)->update(['evento_id' => $keeperId]);
        }
    }

    private function rebindApuracaoEventos(array $duplicateIds, int $keeperId): void
    {
        if (!$duplicateIds || !Schema::hasTable('apuracao_salario_eventos')) {
            return;
        }

        $rows = DB::table('apuracao_salario_eventos')
            ->whereIn('evento_id', $duplicateIds)
            ->orderBy('id')
            ->get();

        foreach ($rows as $row) {
            $existing = DB::table('apuracao_salario_eventos')
                ->where('apuracao_id', $row->apuracao_id)
                ->where('evento_id', $keeperId)
                ->first();

            if ($existing) {
                DB::table('apuracao_salario_eventos')->where('id', $row->id)->delete();
                continue;
            }

            DB::table('apuracao_salario_eventos')->where('id', $row->id)->update(['evento_id' => $keeperId]);
        }
    }
};
