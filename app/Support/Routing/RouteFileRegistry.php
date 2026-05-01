<?php

namespace App\Support\Routing;

use App\Modules\ModuleRegistry;

class RouteFileRegistry
{
    public static function web(): array
    {
        return array_values(array_filter(array_unique(array_merge(
            self::priorityFiles(),
            self::optionalFiles()
        )), 'is_file'));
    }

    public static function priorityFiles(): array
    {
        $files = [
            base_path('routes/web.php'),
            base_path('routes/web_export_routes.php'),
            base_path('routes/aliases/web.php'),
            base_path('routes/patches/web/controle_relatorio_vendas_resumido.php'),
        ];

        if (self::shouldLoadLegacyRoutes()) {
            $files[] = base_path('routes/legacy/web_export_routes.php');
        }

        return array_merge($files, ModuleRegistry::webRouteFiles());
    }

    public static function priorityDirectories(): array
    {
        $directories = [
            base_path('routes'),
            base_path('routes/aliases'),
            base_path('routes/modules'),
            app_path('Modules'),
        ];

        if (self::shouldLoadLegacyRoutes()) {
            $directories[] = base_path('routes/legacy');
        }

        if (self::shouldLoadPatchRoutes()) {
            $directories[] = base_path('routes/patches');
        }

        return array_values(array_filter($directories, 'is_dir'));
    }

    public static function optionalFiles(): array
    {
        $files = [
            base_path('routes/modules/pdv/web.php'),
        ];

        if (self::shouldLoadPatchRoutes()) {
            $files = array_merge($files, [
                base_path('routes/admin_pro_plus_routes_patch.php'),
                base_path('routes/admin_dashboard_routes_patch.php'),
                base_path('routes/admin_routes_patch.php'),
                base_path('routes/ultimate_patch_routes.php'),
                base_path('routes/web_patch_ia_aprendizado.php'),
                base_path('routes/web_patch_ia_aprendizado_method.php'),
                base_path('routes/web_patch_ia_aprovacao.php'),
                base_path('routes/web_patch_ia_aprovacao_method.php'),
                base_path('routes/web_patch_ia_autonoma.php'),
                base_path('routes/web_patch_nivel_absurdo_maximo.php'),
                base_path('routes/web_patch_nivel_maximo.php'),
                base_path('routes/web_patch_preditivo_alertas.php'),
                base_path('routes/web_patch_snippet.php'),
                base_path('routes/web_patch_tudo_nivel_maximo.php'),
            ]);
        }

        return $files;
    }

    public static function shouldLoadLegacyRoutes(): bool
    {
        return filter_var(env('APP_LOAD_LEGACY_ROUTES', false), FILTER_VALIDATE_BOOL);
    }

    public static function shouldLoadPatchRoutes(): bool
    {
        return filter_var(env('APP_LOAD_PATCH_ROUTES', false), FILTER_VALIDATE_BOOL);
    }
}
