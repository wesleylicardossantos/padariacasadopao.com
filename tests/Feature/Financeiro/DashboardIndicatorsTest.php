<?php

namespace Tests\Feature\Financeiro;

use App\Services\DashboardService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DashboardIndicatorsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');
        DB::setDefaultConnection('sqlite');

        $this->createTables();
        Cache::flush();
    }

    private function createTables(): void
    {
        Schema::dropAllTables();

        Schema::create('vendas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('filial_id')->nullable();
            $table->dateTime('data_registro')->nullable();
            $table->decimal('valor_total', 16, 2)->default(0);
            $table->decimal('desconto', 16, 2)->default(0);
            $table->string('estado_emissao')->nullable();
            $table->unsignedInteger('cliente_id')->nullable();
            $table->string('tipo_pagamento')->nullable();
            $table->string('forma_pagamento')->nullable();
            $table->timestamps();
        });

        Schema::create('venda_caixas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('filial_id')->nullable();
            $table->dateTime('data_registro')->nullable();
            $table->decimal('valor_total', 16, 2)->default(0);
            $table->decimal('desconto', 16, 2)->default(0);
            $table->string('estado_emissao')->nullable();
            $table->boolean('rascunho')->default(false);
            $table->unsignedInteger('cliente_id')->nullable();
            $table->string('nome')->nullable();
            $table->string('tipo_pagamento')->nullable();
            $table->string('forma_pagamento')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('conta_recebers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('filial_id')->nullable();
            $table->decimal('valor_integral', 16, 2)->default(0);
            $table->decimal('valor_recebido', 16, 2)->default(0);
            $table->boolean('status')->default(false);
            $table->date('data_recebimento')->nullable();
            $table->date('data_vencimento')->nullable();
            $table->timestamps();
        });

        Schema::create('conta_pagars', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('filial_id')->nullable();
            $table->decimal('valor_integral', 16, 2)->default(0);
            $table->decimal('valor_pago', 16, 2)->default(0);
            $table->boolean('status')->default(false);
            $table->date('data_pagamento')->nullable();
            $table->date('data_vencimento')->nullable();
            $table->timestamps();
        });

        Schema::create('produtos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('filial_id')->nullable();
            $table->string('locais')->nullable();
            $table->boolean('inativo')->default(false);
            $table->string('nome')->nullable();
            $table->decimal('valor_compra', 16, 2)->default(0);
            $table->timestamps();
        });
    }

    public function test_vendas_mes_deve_excluir_canceladas_rascunhos_e_soft_deleted(): void
    {
        $agora = now()->startOfMonth()->addDays(5);

        DB::table('vendas')->insert([
            [
                'empresa_id' => 1,
                'filial_id' => 1,
                'data_registro' => $agora,
                'valor_total' => 1000,
                'estado_emissao' => 'aprovado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'empresa_id' => 1,
                'filial_id' => 1,
                'data_registro' => $agora,
                'valor_total' => 500,
                'estado_emissao' => 'cancelado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('venda_caixas')->insert([
            [
                'empresa_id' => 1,
                'filial_id' => 1,
                'data_registro' => $agora,
                'valor_total' => 300,
                'estado_emissao' => 'aprovado',
                'rascunho' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'empresa_id' => 1,
                'filial_id' => 1,
                'data_registro' => $agora,
                'valor_total' => 200,
                'estado_emissao' => 'aprovado',
                'rascunho' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'empresa_id' => 1,
                'filial_id' => 1,
                'data_registro' => $agora,
                'valor_total' => 400,
                'estado_emissao' => 'aprovado',
                'rascunho' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => now(),
            ],
        ]);

        $snapshot = app(DashboardService::class)->getCardsSnapshot(1, 1);

        $this->assertEquals(1300.0, $snapshot['vendas_mes']);
        $this->assertEquals(2, $snapshot['qtd_vendas']);
        $this->assertEquals(650.0, $snapshot['ticket_medio']);
    }

    public function test_contas_em_aberto_devem_refletir_saldo_residual(): void
    {
        DB::table('conta_recebers')->insert([
            [
                'empresa_id' => 1,
                'filial_id' => 1,
                'valor_integral' => 1000,
                'valor_recebido' => 400,
                'status' => 0,
                'data_vencimento' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'empresa_id' => 1,
                'filial_id' => 1,
                'valor_integral' => 500,
                'valor_recebido' => 500,
                'status' => 1,
                'data_recebimento' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('conta_pagars')->insert([
            [
                'empresa_id' => 1,
                'filial_id' => 1,
                'valor_integral' => 900,
                'valor_pago' => 300,
                'status' => 0,
                'data_vencimento' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $snapshot = app(DashboardService::class)->getCardsSnapshot(1, 1);

        $this->assertEquals(600.0, $snapshot['conta_receber']);
        $this->assertEquals(600.0, $snapshot['conta_pagar']);
        $this->assertEquals(0.0, $snapshot['saldo_previsto']);
    }

    public function test_produtos_cadastrados_deve_considerar_apenas_ativos(): void
    {
        DB::table('produtos')->insert([
            [
                'empresa_id' => 1,
                'filial_id' => 1,
                'locais' => '[1]',
                'inativo' => 0,
                'nome' => 'Produto ativo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'empresa_id' => 1,
                'filial_id' => 1,
                'locais' => '[1]',
                'inativo' => 1,
                'nome' => 'Produto inativo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $snapshot = app(DashboardService::class)->getCardsSnapshot(1, 1);

        $this->assertEquals(1, $snapshot['produtos']);
    }

    public function test_auditoria_financeira_deve_calcular_percentuais_e_status(): void
    {
        $agora = now()->startOfMonth()->addDays(2);

        DB::table('vendas')->insert([
            [
                'empresa_id' => 1,
                'filial_id' => 1,
                'data_registro' => $agora,
                'valor_total' => 1000,
                'estado_emissao' => 'aprovado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('conta_recebers')->insert([
            [
                'empresa_id' => 1,
                'filial_id' => 1,
                'valor_integral' => 1000,
                'valor_recebido' => 250,
                'status' => 1,
                'data_recebimento' => now()->toDateString(),
                'data_vencimento' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('conta_pagars')->insert([
            [
                'empresa_id' => 1,
                'filial_id' => 1,
                'valor_integral' => 500,
                'valor_pago' => 100,
                'status' => 0,
                'data_vencimento' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $audit = app(DashboardService::class)->getFinancialAudit(1, 1);

        $this->assertEquals(1000.0, $audit['faturamento_total_mes']);
        $this->assertEquals(250.0, $audit['recebido_no_mes']);
        $this->assertEquals(25.0, $audit['percentual_recebido_faturado']);
        $this->assertEquals(750.0, $audit['diferenca_faturado_recebido']);
        $this->assertEquals('critico', $audit['status_fluxo']);
    }
    public function test_vendas_pdv_com_filial_null_entram_no_dashboard_da_filial(): void
    {
        $agora = now()->startOfMonth()->addDays(3);

        DB::table('venda_caixas')->insert([
            [
                'empresa_id' => 1,
                'filial_id' => null,
                'data_registro' => $agora,
                'valor_total' => 7773.50,
                'estado_emissao' => 'novo',
                'rascunho' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        $snapshot = app(DashboardService::class)->getCardsSnapshot(1, 1);

        $this->assertEquals(7773.5, $snapshot['vendas_mes']);
        $this->assertEquals(7773.5, $snapshot['vendas_historico']);
        $this->assertEquals(1, $snapshot['qtd_vendas']);
        $this->assertEquals(7773.5, $snapshot['ticket_medio']);
    }

    public function test_bump_cache_version_forca_recalculo_do_snapshot(): void
    {
        $agora = now()->startOfMonth()->addDays(1);
        $service = app(DashboardService::class);

        DB::table('venda_caixas')->insert([
            'empresa_id' => 1,
            'filial_id' => null,
            'data_registro' => $agora,
            'valor_total' => 100,
            'estado_emissao' => 'novo',
            'rascunho' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $primeiro = $service->getCardsSnapshot(1, 1);
        $this->assertEquals(100.0, $primeiro['vendas_mes']);

        DB::table('venda_caixas')->insert([
            'empresa_id' => 1,
            'filial_id' => null,
            'data_registro' => $agora,
            'valor_total' => 250,
            'estado_emissao' => 'novo',
            'rascunho' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $cacheAntigo = $service->getCardsSnapshot(1, 1);
        $this->assertEquals(100.0, $cacheAntigo['vendas_mes']);

        $service->bumpCacheVersion(1);
        $recalculado = $service->getCardsSnapshot(1, 1);

        $this->assertEquals(350.0, $recalculado['vendas_mes']);
        $this->assertEquals(2, $recalculado['qtd_vendas']);
    }

}
