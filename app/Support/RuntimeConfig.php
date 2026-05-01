<?php

namespace App\Support;

class RuntimeConfig
{
    public static function pagination(): int
    {
        return max(1, (int) config('hardening.pagination', 20));
    }

    public static function maintenanceAllowedIps(): array
    {
        $ips = config('hardening.maintenance_allowed_ips', []);
        return is_array($ips) ? array_values(array_filter(array_map('strval', $ips))) : [];
    }
}
