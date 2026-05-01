<?php

namespace App\Modules\SaaS\Services;

use App\Services\RHIntelligentAlertsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PremiumNotificationCenterService
{
    public function notifications(int $empresaId, int $mes, int $ano): array
    {
        $items = [];

        foreach (RHIntelligentAlertsService::listar($empresaId, false, 10) as $alerta) {
            $items[] = [
                'channel' => 'rh-alert',
                'level' => $alerta->nivel ?? 'info',
                'title' => $alerta->titulo ?? 'Alerta RH',
                'message' => $alerta->mensagem ?? '',
                'created_at' => (string) ($alerta->created_at ?? now()),
            ];
        }

        if (Schema::hasTable('saas_premium_notifications')) {
            $rows = DB::table('saas_premium_notifications')
                ->where('empresa_id', $empresaId)
                ->orderByDesc('id')
                ->limit(20)
                ->get();

            foreach ($rows as $row) {
                $items[] = [
                    'channel' => (string) $row->channel,
                    'level' => (string) $row->level,
                    'title' => (string) $row->title,
                    'message' => (string) $row->message,
                    'created_at' => (string) ($row->created_at ?? now()),
                ];
            }
        }

        usort($items, fn ($a, $b) => strcmp((string) $b['created_at'], (string) $a['created_at']));

        return array_slice($items, 0, 20);
    }

    public function push(int $empresaId, string $channel, string $level, string $title, string $message, array $payload = []): bool
    {
        if (!Schema::hasTable('saas_premium_notifications')) {
            return false;
        }

        DB::table('saas_premium_notifications')->insert([
            'empresa_id' => $empresaId,
            'channel' => $channel,
            'level' => $level,
            'title' => $title,
            'message' => $message,
            'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return true;
    }
}
