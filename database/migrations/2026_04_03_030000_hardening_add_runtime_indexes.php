<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('pdv_offline_syncs')) {
            Schema::table('pdv_offline_syncs', function (Blueprint $table) {
                try { $table->index(['empresa_id', 'status'], 'idx_pdv_sync_empresa_status'); } catch (\Throwable $e) {}
                try { $table->index(['empresa_id', 'created_at'], 'idx_pdv_sync_empresa_created'); } catch (\Throwable $e) {}
            });
        }

        if (Schema::hasTable('stock_movements')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                try { $table->index(['empresa_id', 'produto_id'], 'idx_stock_empresa_produto'); } catch (\Throwable $e) {}
                try { $table->index(['empresa_id', 'created_at'], 'idx_stock_empresa_created'); } catch (\Throwable $e) {}
            });
        }

        if (Schema::hasTable('fiscal_documents')) {
            Schema::table('fiscal_documents', function (Blueprint $table) {
                try { $table->index(['empresa_id', 'status'], 'idx_fiscal_empresa_status'); } catch (\Throwable $e) {}
                try { $table->index(['document_type', 'chave'], 'idx_fiscal_document_type_chave'); } catch (\Throwable $e) {}
            });
        }

        if (Schema::hasTable('financial_audits')) {
            Schema::table('financial_audits', function (Blueprint $table) {
                try { $table->index(['empresa_id', 'created_at'], 'idx_fin_audit_empresa_created'); } catch (\Throwable $e) {}
                try { $table->index(['entidade', 'entidade_id'], 'idx_fin_audit_entidade'); } catch (\Throwable $e) {}
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pdv_offline_syncs')) {
            Schema::table('pdv_offline_syncs', function (Blueprint $table) {
                try { $table->dropIndex('idx_pdv_sync_empresa_status'); } catch (\Throwable $e) {}
                try { $table->dropIndex('idx_pdv_sync_empresa_created'); } catch (\Throwable $e) {}
            });
        }

        if (Schema::hasTable('stock_movements')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                try { $table->dropIndex('idx_stock_empresa_produto'); } catch (\Throwable $e) {}
                try { $table->dropIndex('idx_stock_empresa_created'); } catch (\Throwable $e) {}
            });
        }

        if (Schema::hasTable('fiscal_documents')) {
            Schema::table('fiscal_documents', function (Blueprint $table) {
                try { $table->dropIndex('idx_fiscal_empresa_status'); } catch (\Throwable $e) {}
                try { $table->dropIndex('idx_fiscal_document_type_chave'); } catch (\Throwable $e) {}
            });
        }

        if (Schema::hasTable('financial_audits')) {
            Schema::table('financial_audits', function (Blueprint $table) {
                try { $table->dropIndex('idx_fin_audit_empresa_created'); } catch (\Throwable $e) {}
                try { $table->dropIndex('idx_fin_audit_entidade'); } catch (\Throwable $e) {}
            });
        }
    }
};
