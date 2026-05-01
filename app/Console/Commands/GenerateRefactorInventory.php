<?php

namespace App\Console\Commands;

use App\Support\Refactor\ProjectInventory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateRefactorInventory extends Command
{
    protected $signature = 'refactor:inventory {--write : Persist the inventory under storage/app/refactor}';

    protected $description = 'Generate a technical inventory to support safe refactoring.';

    public function handle(): int
    {
        $inventory = ProjectInventory::build();

        $this->info('Project inventory generated successfully.');
        $this->line(json_encode($inventory, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        if ($this->option('write')) {
            $directory = storage_path('app/refactor');
            File::ensureDirectoryExists($directory);
            $target = $directory.'/inventory.json';
            File::put($target, json_encode($inventory, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info("Saved to: {$target}");
        }

        return self::SUCCESS;
    }
}
