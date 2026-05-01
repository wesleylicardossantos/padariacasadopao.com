<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('mercadopago_webhook_events')) {
            Schema::create('mercadopago_webhook_events', function (Blueprint $table) {
                $table->id();
                $table->string('topic', 50)->nullable();
                $table->string('resource_id', 100)->nullable()->index();
                $table->string('action', 100)->nullable();
                $table->string('event_hash', 255)->unique();
                $table->longText('headers')->nullable();
                $table->longText('payload')->nullable();
                $table->string('status', 50)->default('received')->index();
                $table->text('error_message')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (! Schema::hasColumn('payments', 'external_reference')) {
                    $table->string('external_reference', 100)->nullable()->after('transacao_id');
                }
                if (! Schema::hasColumn('payments', 'notification_url')) {
                    $table->string('notification_url', 255)->nullable()->after('external_reference');
                }
                if (! Schema::hasColumn('payments', 'raw_response')) {
                    $table->longText('raw_response')->nullable()->after('qr_code');
                }
                if (! Schema::hasColumn('payments', 'paid_at')) {
                    $table->timestamp('paid_at')->nullable()->after('raw_response');
                }
                if (! Schema::hasColumn('payments', 'mp_status_last_sync_at')) {
                    $table->timestamp('mp_status_last_sync_at')->nullable()->after('paid_at');
                }
            });
        }

        if (Schema::hasTable('saas_subscription_cycles')) {
            Schema::table('saas_subscription_cycles', function (Blueprint $table) {
                if (! Schema::hasColumn('saas_subscription_cycles', 'mp_payment_id')) {
                    $table->string('mp_payment_id', 100)->nullable()->index();
                }
                if (! Schema::hasColumn('saas_subscription_cycles', 'payment_status')) {
                    $table->string('payment_status', 50)->nullable();
                }
                if (! Schema::hasColumn('saas_subscription_cycles', 'paid_at')) {
                    $table->timestamp('paid_at')->nullable();
                }
            });
        }

        if (Schema::hasTable('plano_empresas')) {
            Schema::table('plano_empresas', function (Blueprint $table) {
                if (! Schema::hasColumn('plano_empresas', 'status_pagamento')) {
                    $table->string('status_pagamento', 50)->nullable();
                }
                if (! Schema::hasColumn('plano_empresas', 'data_pagamento')) {
                    $table->timestamp('data_pagamento')->nullable();
                }
            });
        }

        if (Schema::hasTable('conta_recebers')) {
            Schema::table('conta_recebers', function (Blueprint $table) {
                if (! Schema::hasColumn('conta_recebers', 'mp_payment_id')) {
                    $table->string('mp_payment_id', 100)->nullable()->index();
                }
                if (! Schema::hasColumn('conta_recebers', 'valor_pago')) {
                    $table->decimal('valor_pago', 16, 7)->nullable();
                }
                if (! Schema::hasColumn('conta_recebers', 'data_pagamento')) {
                    $table->timestamp('data_pagamento')->nullable();
                }
                if (! Schema::hasColumn('conta_recebers', 'status_pagamento')) {
                    $table->string('status_pagamento', 50)->nullable()->index();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mercadopago_webhook_events')) {
            Schema::dropIfExists('mercadopago_webhook_events');
        }
    }
};
