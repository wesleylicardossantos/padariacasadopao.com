<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('pdv_offline_syncs')) {
            return;
        }

        $columnsToAdd = [];

        if (!Schema::hasColumn('pdv_offline_syncs', 'venda_caixa_id')) {
            $columnsToAdd['venda_caixa_id'] = function (Blueprint $table) {
                $table->unsignedBigInteger('venda_caixa_id')->nullable()->after('status');
            };
        }

        if (!Schema::hasColumn('pdv_offline_syncs', 'request_payload')) {
            $columnsToAdd['request_payload'] = function (Blueprint $table) {
                $table->longText('request_payload')->nullable()->after('venda_caixa_id');
            };
        }

        if (!Schema::hasColumn('pdv_offline_syncs', 'response_payload')) {
            $columnsToAdd['response_payload'] = function (Blueprint $table) {
                $table->longText('response_payload')->nullable()->after('request_payload');
            };
        }

        if (!Schema::hasColumn('pdv_offline_syncs', 'erro')) {
            $columnsToAdd['erro'] = function (Blueprint $table) {
                $table->text('erro')->nullable()->after('response_payload');
            };
        }

        if (!Schema::hasColumn('pdv_offline_syncs', 'sincronizado_em')) {
            $columnsToAdd['sincronizado_em'] = function (Blueprint $table) {
                $table->timestamp('sincronizado_em')->nullable()->after('erro');
            };
        }

        if (!empty($columnsToAdd)) {
            Schema::table('pdv_offline_syncs', function (Blueprint $table) use ($columnsToAdd) {
                foreach ($columnsToAdd as $callback) {
                    $callback($table);
                }
            });
        }

        Schema::table('pdv_offline_syncs', function (Blueprint $table) {
            if (Schema::hasColumn('pdv_offline_syncs', 'venda_caixa_id') && !$this->hasIndex('pdv_offline_syncs', 'pdv_offline_syncs_venda_caixa_id_index')) {
                $table->index('venda_caixa_id', 'pdv_offline_syncs_venda_caixa_id_index');
            }

            if (Schema::hasColumn('pdv_offline_syncs', 'empresa_id') && Schema::hasColumn('pdv_offline_syncs', 'uuid_local') && !$this->hasIndex('pdv_offline_syncs', 'pdv_offline_syncs_empresa_uuid_unique')) {
                $table->unique(['empresa_id', 'uuid_local'], 'pdv_offline_syncs_empresa_uuid_unique');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('pdv_offline_syncs')) {
            return;
        }

        Schema::table('pdv_offline_syncs', function (Blueprint $table) {
            if (Schema::hasColumn('pdv_offline_syncs', 'venda_caixa_id') && $this->hasIndex('pdv_offline_syncs', 'pdv_offline_syncs_venda_caixa_id_index')) {
                $table->dropIndex('pdv_offline_syncs_venda_caixa_id_index');
            }

            if ($this->hasIndex('pdv_offline_syncs', 'pdv_offline_syncs_empresa_uuid_unique')) {
                $table->dropUnique('pdv_offline_syncs_empresa_uuid_unique');
            }
        });

        $dropColumns = array_values(array_filter([
            Schema::hasColumn('pdv_offline_syncs', 'sincronizado_em') ? 'sincronizado_em' : null,
            Schema::hasColumn('pdv_offline_syncs', 'erro') ? 'erro' : null,
            Schema::hasColumn('pdv_offline_syncs', 'response_payload') ? 'response_payload' : null,
            Schema::hasColumn('pdv_offline_syncs', 'request_payload') ? 'request_payload' : null,
            Schema::hasColumn('pdv_offline_syncs', 'venda_caixa_id') ? 'venda_caixa_id' : null,
        ]));

        if (!empty($dropColumns)) {
            Schema::table('pdv_offline_syncs', function (Blueprint $table) use ($dropColumns) {
                $table->dropColumn($dropColumns);
            });
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $schemaManager = method_exists($connection, 'getDoctrineSchemaManager')
            ? $connection->getDoctrineSchemaManager()
            : null;

        if (!$schemaManager) {
            return false;
        }

        $tableDetails = $schemaManager->listTableDetails($table);

        return $tableDetails->hasIndex($indexName);
    }
};
