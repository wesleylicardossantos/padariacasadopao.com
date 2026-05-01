<?php

return [
    'pagination' => (int) env('PAGINACAO', 20),
    'maintenance_allowed_ips' => array_values(array_filter(array_map('trim', explode(',', (string) env('MAINTENANCE_ALLOWED_IPS', ''))))),
    'healthcheck' => [
        'required_tables' => [
            'pdv_offline_syncs',
            'stock_movements',
            'financial_audits',
            'job_batches',
            'fiscal_documents',
        ],
    ],
    'schedule' => [
        'project_inventory' => env('SCHEDULE_PROJECT_INVENTORY', '02:10'),
        'saas_snapshot_usage' => env('SCHEDULE_SAAS_SNAPSHOT_USAGE', '0 * * * *'),
        'sefaz_diagnostico' => env('SCHEDULE_SEFAZ_DIAGNOSTICO', '06:30'),
        'pdv_retry' => env('SCHEDULE_PDV_RETRY', '*/10 * * * *'),
        'stock_reconcile' => env('SCHEDULE_STOCK_RECONCILE', '03:40'),
        'schema_drift_report' => env('SCHEDULE_SCHEMA_DRIFT_REPORT', '04:00'),
        'system_healthcheck' => env('SCHEDULE_SYSTEM_HEALTHCHECK', '*/15 * * * *'),
        'refactor_governance_report' => env('SCHEDULE_REFACTOR_GOVERNANCE_REPORT', '04:20'),
        'stock_write_guard_report' => env('SCHEDULE_STOCK_WRITE_GUARD_REPORT', '04:35'),
        'fiscal_operations_report' => env('SCHEDULE_FISCAL_OPERATIONS_REPORT', '04:50'),
        'hardening_final_report' => env('SCHEDULE_HARDENING_FINAL_REPORT', '05:05'),
        'deadcode_candidates_report' => env('SCHEDULE_DEADCODE_CANDIDATES_REPORT', '05:20'),
    ],
    'final' => [
        'enforce_public_surface_review' => (bool) env('HARDENING_ENFORCE_PUBLIC_SURFACE_REVIEW', true),
        'index_review_enabled' => (bool) env('HARDENING_INDEX_REVIEW_ENABLED', true),
    ],
    'security_headers' => [
        'x_frame_options' => env('HARDENING_X_FRAME_OPTIONS', 'SAMEORIGIN'),
        'referrer_policy' => env('HARDENING_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
        'permissions_policy' => env('HARDENING_PERMISSIONS_POLICY', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()'),
        'content_security_policy_report_only' => env('HARDENING_CSP_REPORT_ONLY', "default-src 'self' 'unsafe-inline' 'unsafe-eval' data: blob: https: http:; img-src 'self' data: blob: https: http:; frame-ancestors 'self'; base-uri 'self'; form-action 'self' https: http:"),
        'enable_hsts' => (bool) env('HARDENING_ENABLE_HSTS', false),
        'hsts_max_age' => (int) env('HARDENING_HSTS_MAX_AGE', 31536000),
        'hsts_include_subdomains' => (bool) env('HARDENING_HSTS_INCLUDE_SUBDOMAINS', true),
        'hsts_preload' => (bool) env('HARDENING_HSTS_PRELOAD', false),
    ],
];
