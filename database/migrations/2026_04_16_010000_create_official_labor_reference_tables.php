<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('rh_official_worker_categories')) {
            Schema::create('rh_official_worker_categories', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('codigo', 10)->unique();
                $table->string('descricao', 255);
                $table->string('grupo', 120)->nullable();
                $table->date('inicio_vigencia')->nullable();
                $table->date('fim_vigencia')->nullable();
                $table->boolean('ativo')->default(true);
                $table->string('fonte', 120)->nullable();
                $table->string('fonte_url', 255)->nullable();
                $table->timestamp('fonte_atualizada_em')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rh_official_contract_types')) {
            Schema::create('rh_official_contract_types', function (Blueprint $table) {
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

        if (!Schema::hasTable('rh_official_nature_activities')) {
            Schema::create('rh_official_nature_activities', function (Blueprint $table) {
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

        if (!Schema::hasTable('rh_official_cbo_occupations')) {
            Schema::create('rh_official_cbo_occupations', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('codigo', 10)->unique();
                $table->string('titulo', 255);
                $table->string('titulo_normalizado', 255)->nullable()->index();
                $table->string('fonte', 120)->nullable();
                $table->string('fonte_url', 255)->nullable();
                $table->timestamp('fonte_atualizada_em')->nullable();
                $table->timestamps();
                $table->index('codigo');
            });
        }

        if (!Schema::hasTable('rh_department_references')) {
            Schema::create('rh_department_references', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('codigo', 20)->unique();
                $table->string('descricao', 120);
                $table->unsignedInteger('ordem')->default(0);
                $table->boolean('ativo')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_department_references');
        Schema::dropIfExists('rh_official_cbo_occupations');
        Schema::dropIfExists('rh_official_nature_activities');
        Schema::dropIfExists('rh_official_contract_types');
        Schema::dropIfExists('rh_official_worker_categories');
    }
};
