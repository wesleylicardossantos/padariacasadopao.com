<?php

return [
    'monitor_direct_legacy_writes' => env('STOCK_MONITOR_DIRECT_LEGACY_WRITES', true),
    'block_direct_legacy_writes' => env('STOCK_BLOCK_DIRECT_LEGACY_WRITES', false),
    'report_path' => env('STOCK_GOVERNANCE_REPORT_PATH', 'docs/operacao/stock_governance_report.json'),
    'audit_retention_days' => (int) env('STOCK_AUDIT_RETENTION_DAYS', 30),
];
