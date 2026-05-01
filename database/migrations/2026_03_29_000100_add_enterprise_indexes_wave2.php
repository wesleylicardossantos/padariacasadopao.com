<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conta_recebers', function (Blueprint $table) {
            $table->index(['empresa_id', 'status', 'data_vencimento'], 'cr_empresa_status_vencimento_idx');
            $table->index(['empresa_id', 'cliente_id'], 'cr_empresa_cliente_idx');
            $table->index(['empresa_id', 'filial_id'], 'cr_empresa_filial_idx');
        });

        Schema::table('conta_pagars', function (Blueprint $table) {
            $table->index(['empresa_id', 'status', 'data_vencimento'], 'cp_empresa_status_vencimento_idx');
            $table->index(['empresa_id', 'fornecedor_id'], 'cp_empresa_fornecedor_idx');
            $table->index(['empresa_id', 'filial_id'], 'cp_empresa_filial_idx');
        });

        Schema::table('vendas', function (Blueprint $table) {
            $table->index(['empresa_id', 'cliente_id', 'created_at'], 'vendas_empresa_cliente_created_idx');
            $table->index(['empresa_id', 'filial_id', 'created_at'], 'vendas_empresa_filial_created_idx');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->index(['empresa_id', 'inativo'], 'clientes_empresa_inativo_idx');
        });
    }

    public function down(): void
    {
        Schema::table('conta_recebers', function (Blueprint $table) {
            $table->dropIndex('cr_empresa_status_vencimento_idx');
            $table->dropIndex('cr_empresa_cliente_idx');
            $table->dropIndex('cr_empresa_filial_idx');
        });

        Schema::table('conta_pagars', function (Blueprint $table) {
            $table->dropIndex('cp_empresa_status_vencimento_idx');
            $table->dropIndex('cp_empresa_fornecedor_idx');
            $table->dropIndex('cp_empresa_filial_idx');
        });

        Schema::table('vendas', function (Blueprint $table) {
            $table->dropIndex('vendas_empresa_cliente_created_idx');
            $table->dropIndex('vendas_empresa_filial_created_idx');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex('clientes_empresa_inativo_idx');
        });
    }
};
