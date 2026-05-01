<?php

return [
    'allow_force_enable' => (bool) env('LEGACY_CUTOFF_ALLOW_FORCE_ENABLE', false),
    'schedule' => [
        'legacy_cutoff_readiness_report' => env('SCHEDULE_LEGACY_CUTOFF_READINESS_REPORT', '05:35'),
        'performance_baseline_report' => env('SCHEDULE_PERFORMANCE_BASELINE_REPORT', '05:50'),
    ],
];
