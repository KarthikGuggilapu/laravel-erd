<?php

namespace YourVendor\LaravelErd\Commands;

use Illuminate\Console\Command;
use YourVendor\LaravelErd\Services\MigrationScanner;
use YourVendor\LaravelErd\Services\ModelScanner;
use YourVendor\LaravelErd\Services\RegistryManager;

class RefreshCommand extends Command
{
    protected $signature = 'erd:refresh';

    protected $description = 'Refresh Laravel ERD schema registry';

    public function handle(
        RegistryManager $registry,
        MigrationScanner $migrationScanner,
        ModelScanner $modelScanner
    ): int {
        if (!config('erd.enabled')) {
            $this->error('Laravel ERD is disabled.');

            return self::FAILURE;
        }

        $registry->initialize();

        $this->info('Scanning migrations...');

        $migrations = $migrationScanner->scan();

        $registry->put('migrations.json', [
            'version' => 1,
            'updated_at' => now()->toIso8601String(),
            'migrations' => $migrations,
        ]);

        $this->info(
            'Migrations discovered: ' . count($migrations)
        );

        $this->info('Scanning models...');

        $models = $modelScanner->scan();

        $registry->put('models.json', [
            'version' => 1,
            'updated_at' => now()->toIso8601String(),
            'models' => $models,
        ]);

        $this->info(
            'Models discovered: ' . count($models)
        );

        $this->info('Migration registry updated.');
        $this->info('Model registry updated.');

        return self::SUCCESS;
    }
}