<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RequestTelemetry
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('infra.observability.audit_requests_enabled', true)) {
            return $next($request);
        }

        $startedAt = microtime(true);
        /** @var Response $response */
        $response = $next($request);

        if (!$this->shouldAudit($request, $response)) {
            return $response;
        }

        $payload = [
            'empresa_id' => (int) ($request->attributes->get('tenant_empresa_id')
                ?: $request->get('empresa_id')
                ?: data_get(session('funcionario_portal'), 'empresa_id')
                ?: data_get(session('user_logged'), 'empresa')
                ?: (auth()->user()->empresa_id ?? 0)
                ?: 0),
            'usuario_id' => (int) (data_get(session('user_logged'), 'id') ?: auth()->id() ?: 0),
            'funcionario_portal_id' => (int) (data_get(session('funcionario_portal'), 'funcionario_id') ?: 0),
            'route_name' => optional($request->route())->getName(),
            'path' => $request->path(),
            'method' => $request->method(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
            'ip' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
            'occurred_at' => now()->toIso8601String(),
        ];

        Log::channel(config('infra.observability.audit_log_channel', 'security_audit'))
            ->info('Tenant request audit', $payload);

        if (!config('infra.observability.store_request_audits_database', true) || !Schema::hasTable('performance_events')) {
            return $response;
        }

        try {
            DB::table('performance_events')->insert([
                'empresa_id' => $payload['empresa_id'] ?: null,
                'event_type' => 'request_audit',
                'context' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Throwable) {
            // observabilidade não deve bloquear a request produtiva
        }

        return $response;
    }

    private function shouldAudit(Request $request, Response $response): bool
    {
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return false;
        }

        $path = trim($request->path(), '/');
        foreach ((array) config('infra.observability.audit_path_prefixes', []) as $prefix) {
            $prefix = trim((string) $prefix, '/');
            if ($prefix !== '' && str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return $response->getStatusCode() >= 400;
    }
}
