<?php

return [
    'monitor_direct_mutations' => (bool) env('FINANCE_MONITOR_DIRECT_MUTATIONS', env('FINANCE_MONITOR_DIRECT_SETTLEMENT_MUTATIONS', true)),
    'block_direct_settlement_mutations' => (bool) env('FINANCE_BLOCK_DIRECT_SETTLEMENT_MUTATIONS', env('FINANCE_BLOCK_DIRECT_MUTATIONS', true)),
    'log_channel' => env('FINANCE_GOVERNANCE_LOG_CHANNEL', env('LOG_CHANNEL', 'stack')),
];
