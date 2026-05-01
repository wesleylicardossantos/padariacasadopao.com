<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('rh_official_admission_indicators')) {
            Schema::create('rh_official_admission_indicators', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('codigo', 10)->unique();
                $table->string('descricao', 255);
                $table->boolean('ativo')->default(true);
                $table->string('fonte', 120)->nullable();
                $table->string('fonte_url', 255)->nullable();
                $table->timestamp('fonte_atualizada_em')->nullable();
                $table->timestamps();
            });
        }

        $now = now();
        $rows = [
            ['codigo' => '1', 'descricao' => 'Admissão normal'],
            ['codigo' => '2', 'descricao' => 'Decorrente de ação fiscal'],
            ['codigo' => '3', 'descricao' => 'Decorrente de decisão judicial'],
        ];

        foreach ($rows as $row) {
            DB::table('rh_official_admission_indicators')->updateOrInsert(
                ['codigo' => $row['codigo']],
                [
                    'descricao' => $row['descricao'],
                    'ativo' => 1,
                    'fonte' => 'eSocial S-2200/S-2300',
                    'fonte_url' => 'https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-versao-s-1-3-nt-06-2026-rev-09-04-2026/index.html',
                    'fonte_atualizada_em' => $now,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_official_admission_indicators');
    }
};
