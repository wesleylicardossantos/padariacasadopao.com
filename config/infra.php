<?php

return [
    'redis_enabled' => (bool) env('REDIS_ENABLED', false),
    'queue_enabled' => (bool) env('QUEUE_ENABLED', true),
    'reports_force_async_generation' => (bool) env('REPORTS_FORCE_ASYNC_GENERATION', false),
    'slow_query' => [
        'enabled' => (bool) env('PERFORMANCE_SLOW_QUERY_MONITOR_ENABLED', true),
        'threshold_ms' => (int) env('PERFORMANCE_SLOW_QUERY_THRESHOLD_MS', 350),
        'sample_rate' => max(1, (int) env('PERFORMANCE_SLOW_QUERY_SAMPLE_RATE', 1)),
        'store_database_events' => (bool) env('PERFORMANCE_STORE_DATABASE_EVENTS', true),
        'max_sql_length' => (int) env('PERFORMANCE_MAX_SQL_LENGTH', 4000),
    ],
    'slow_request' => [
        'enabled' => (bool) env('PERFORMANCE_SLOW_REQUEST_MONITOR_ENABLED', true),
        'threshold_ms' => (int) env('PERFORMANCE_SLOW_REQUEST_THRESHOLD_MS', 1200),
        'sample_rate' => max(1, (int) env('PERFORMANCE_SLOW_REQUEST_SAMPLE_RATE', 1)),
        'store_database_events' => (bool) env('PERFORMANCE_STORE_DATABASE_EVENTS', true),
    ],
    'observability' => [
        'audit_requests_enabled' => (bool) env('OBS_AUDIT_REQUESTS_ENABLED', true),
        'audit_log_channel' => env('OBS_AUDIT_LOG_CHANNEL', 'security_audit'),
        'performance_log_channel' => env('OBS_PERFORMANCE_LOG_CHANNEL', 'observability'),
        'store_request_audits_database' => (bool) env('OBS_STORE_REQUEST_AUDITS_DATABASE', true),
        'audit_path_prefixes' => [
            'pedidos',
            'vendas',
            'frenteCaixa',
            'preVenda',
            'rh',
            'portal',
            'api/pedidos',
            'api/vendaCaixa',
            'api/controle_comandas',
        ],
        'sentry' => [
            'enabled' => (bool) env('SENTRY_ENABLED', false),
            'dsn' => env('SENTRY_LARAVEL_DSN'),
            'environment' => env('SENTRY_ENVIRONMENT', env('APP_ENV', 'production')),
            'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.0),
        ],
        'elastic' => [
            'enabled' => (bool) env('ELASTIC_LOG_ENABLED', false),
            'host' => env('ELASTIC_LOG_HOST'),
            'index' => env('ELASTIC_LOG_INDEX', 'wave-runtime'),
        ],
    ],
    'cache' => [
        'tenant_ttl_seconds' => (int) env('TENANT_CACHE_TTL_SECONDS', 60),
    ],
];
