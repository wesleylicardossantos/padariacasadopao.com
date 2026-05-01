<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('rh_portal_funcionarios')) {
            return;
        }

        Schema::create('rh_portal_funcionarios', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('empresa_id')->unsigned();
            $table->integer('funcionario_id')->unsigned()->unique();
            $table->boolean('ativo')->default(true);
            $table->string('senha')->nullable();
            $table->string('token_primeiro_acesso', 120)->nullable();
            $table->string('token_recuperacao', 120)->nullable();
            $table->dateTime('token_expira_em')->nullable();
            $table->dateTime('ultimo_login_em')->nullable();
            $table->string('ultimo_login_ip', 60)->nullable();
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('funcionario_id')->references('id')->on('funcionarios')->onDelete('cascade');
            $table->index(['empresa_id', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_portal_funcionarios');
    }
};
