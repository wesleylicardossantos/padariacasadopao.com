<?php

namespace App\Support\Routing;

use Illuminate\Filesystem\Filesystem;

class ProjectInventory
{
    public function __construct(private readonly Filesystem $files)
    {
    }

    public function build(): array
    {
        return [
            'generated_at' => now()->toDateTimeString(),
            'routes' => [
                'priority_files' => RouteFileRegistry::priorityFiles(),
                'priority_directories' => RouteFileRegistry::priorityDirectories(),
                'loaded_files' => RouteFileRegistry::web(),
            ],
            'metrics' => [
                'controllers' => $this->countPhpFiles(app_path('Http/Controllers')),
                'models' => $this->countPhpFiles(app_path('Models')),
                'services' => $this->countPhpFiles(app_path('Services')),
                'module_services' => $this->countPhpFiles(app_path('Modules')),
                'migrations' => $this->countPhpFiles(database_path('migrations')),
                'views' => $this->countBladeFiles(resource_path('views')),
                'route_files' => count(RouteFileRegistry::web()),
                'session_user_logged_occurrences' => $this->countTextOccurrences(base_path(), "session('user_logged'"),
                'env_usage_occurrences' => $this->countTextOccurrences(app_path(), 'env('),
            ],
            'modules' => $this->discoverModules(),
            'legacy' => [
                'legacy_routes' => $this->listFiles(base_path('routes/legacy')),
                'web_patches' => $this->listFiles(base_path('routes/patches/web')),
                'admin_patches' => $this->listFiles(base_path('routes/patches/admin')),
            ],
        ];
    }

    private function discoverModules(): array
    {
        $modulesPath = app_path('Modules');
        if (! $this->files->isDirectory($modulesPath)) {
            return [];
        }

        return collect($this->files->directories($modulesPath))
            ->map(function (string $directory) {
                return [
                    'name' => basename($directory),
                    'controllers' => $this->countPhpFiles($directory . '/Controllers'),
                    'services' => $this->countPhpFiles($directory . '/Services'),
                    'routes' => $this->countPhpFiles($directory . '/Routes'),
                    'views' => $this->countBladeFiles($directory . '/Views'),
                ];
            })
            ->sortBy('name')
            ->values()
            ->all();
    }

    private function listFiles(string $path): array
    {
        if (! $this->files->isDirectory($path)) {
            return [];
        }

        return collect($this->files->allFiles($path))
            ->map(fn ($file) => $file->getRelativePathname())
            ->sort()
            ->values()
            ->all();
    }

    private function countPhpFiles(string $path): int
    {
        if (! $this->files->isDirectory($path)) {
            return 0;
        }

        return collect($this->files->allFiles($path))
            ->filter(fn ($file) => $file->getExtension() === 'php')
            ->count();
    }

    private function countBladeFiles(string $path): int
    {
        if (! $this->files->isDirectory($path)) {
            return 0;
        }

        return collect($this->files->allFiles($path))
            ->filter(fn ($file) => str_ends_with($file->getFilename(), '.blade.php'))
            ->count();
    }

    private function countTextOccurrences(string $path, string $needle): int
    {
        if (! $this->files->isDirectory($path)) {
            return 0;
        }

        return collect($this->files->allFiles($path))
            ->filter(fn ($file) => in_array($file->getExtension(), ['php', 'blade.php', 'txt', 'md']))
            ->sum(function ($file) use ($needle) {
                return substr_count($file->getContents(), $needle);
            });
    }
}

