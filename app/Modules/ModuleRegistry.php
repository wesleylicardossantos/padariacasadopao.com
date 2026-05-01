<?php

namespace App\Modules;

class ModuleRegistry
{
    /**
     * Canonical module names that participate in the enterprise architecture.
     *
     * @return array<int, string>
     */
    public static function names(): array
    {
        return [
            'Core',
            'RH',
            'Financeiro',
            'Estoque',
            'Comercial',
            'PDV',
            'BI',
            'Fiscal',
            'SaaS',
            'AI',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function webRouteFiles(): array
    {
        $files = [];

        foreach (self::names() as $module) {
            $candidate = base_path("app/Modules/{$module}/Routes/web.php");
            if (is_file($candidate)) {
                $files[] = $candidate;
            }
        }

        return $files;
    }
}
