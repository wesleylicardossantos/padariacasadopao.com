<?php

namespace App\Support\Refactor;

use App\Modules\ModuleRegistry;
use App\Support\Routing\RouteFileRegistry;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

class ProjectInventory
{
    public static function build(): array
    {
        return [
            'generated_at' => now()->toIso8601String(),
            'modules' => self::moduleInventory(),
            'routes' => [
                'web_files' => RouteFileRegistry::web(),
                'web_file_count' => count(RouteFileRegistry::web()),
            ],
            'counts' => [
                'controllers' => self::countPhpFiles(app_path('Http/Controllers')),
                'models' => self::countPhpFiles(app_path('Models')),
                'services' => self::countPhpFiles(app_path('Services')),
                'helpers' => self::countPhpFiles(app_path('Helpers')),
                'module_controllers' => self::countPhpFiles(app_path('Modules')),
                'tests' => self::countPhpFiles(base_path('tests')),
                'migrations' => self::countPhpFiles(database_path('migrations')),
            ],
            'hotspots' => [
                'autoloaded_helper_files' => self::autoloadedHelperFiles(),
                'legacy_route_directories' => [
                    base_path('routes/patches/web'),
                    base_path('routes/patches/admin'),
                    base_path('routes/legacy'),
                ],
            ],
        ];
    }

    private static function moduleInventory(): array
    {
        $modules = [];

        foreach (ModuleRegistry::names() as $module) {
            $modulePath = app_path("Modules/{$module}");
            $modules[$module] = [
                'path' => $modulePath,
                'exists' => is_dir($modulePath),
                'controllers' => self::countPhpFiles("{$modulePath}/Controllers"),
                'services' => self::countPhpFiles("{$modulePath}/Services"),
                'repositories' => self::countPhpFiles("{$modulePath}/Repositories"),
                'models' => self::countPhpFiles("{$modulePath}/Models"),
                'routes' => self::countPhpFiles("{$modulePath}/Routes"),
            ];
        }

        return $modules;
    }

    private static function autoloadedHelperFiles(): array
    {
        $composer = base_path('composer.json');
        if (! is_file($composer)) {
            return [];
        }

        $decoded = json_decode(file_get_contents($composer), true);

        return $decoded['autoload']['files'] ?? [];
    }

    private static function countPhpFiles(string $path): int
    {
        if (! is_dir($path)) {
            return 0;
        }

        $count = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
                $count++;
            }
        }

        return $count;
    }
}
