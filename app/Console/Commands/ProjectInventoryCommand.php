<?php

namespace App\Console\Commands;

use App\Support\Routing\ProjectInventory;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ProjectInventoryCommand extends Command
{
    protected $signature = 'project:inventory {--write : Grava os arquivos em docs/arquitetura}';

    protected $description = 'Gera inventário técnico do projeto e da estratégia de refatoração.';

    public function handle(ProjectInventory $inventory, Filesystem $files): int
    {
        $data = $inventory->build();

        $this->info('Inventário do projeto');
        $this->line('Controllers: ' . $data['metrics']['controllers']);
        $this->line('Models: ' . $data['metrics']['models']);
        $this->line('Services: ' . $data['metrics']['services']);
        $this->line('Views: ' . $data['metrics']['views']);
        $this->line('Arquivos de rota carregados: ' . count($data['routes']['loaded_files']));

        if ($this->option('write')) {
            $targetDir = base_path('docs/arquitetura');
            $files->ensureDirectoryExists($targetDir);
            $files->put($targetDir . '/inventario_projeto.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            $files->put($targetDir . '/inventario_projeto.md', $this->asMarkdown($data));
            $this->info('Arquivos gravados em docs/arquitetura.');
        }

        return self::SUCCESS;
    }

    private function asMarkdown(array $data): string
    {
        $moduleLines = collect($data['modules'])->map(function (array $module) {
            return sprintf(
                '- %s: %d controllers, %d services, %d rotas, %d views',
                $module['name'],
                $module['controllers'],
                $module['services'],
                $module['routes'],
                $module['views']
            );
        })->implode("\n");

        return "# Inventário do projeto\n\n"
            . "Gerado em: {$data['generated_at']}\n\n"
            . "## Métricas\n"
            . "- Controllers: {$data['metrics']['controllers']}\n"
            . "- Models: {$data['metrics']['models']}\n"
            . "- Services: {$data['metrics']['services']}\n"
            . "- Module services: {$data['metrics']['module_services']}\n"
            . "- Migrations: {$data['metrics']['migrations']}\n"
            . "- Views: {$data['metrics']['views']}\n\n"
            . "## Módulos\n"
            . ($moduleLines ?: '- Nenhum módulo encontrado') . "\n\n"
            . "## Rotas carregadas\n"
            . implode("\n", array_map(fn ($file) => '- ' . str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file), $data['routes']['loaded_files'])) . "\n";
    }
}
