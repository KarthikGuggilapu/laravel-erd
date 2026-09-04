<?php

namespace YourVendor\LaravelErd\Commands;

use Illuminate\Console\Command;
use YourVendor\LaravelErd\Services\MigrationScanner;
use YourVendor\LaravelErd\Services\RegistryManager;

class InstallCommand extends Command
{
    protected $signature = 'erd:install';

    protected $description = 'Install and initialize Laravel ERD';

    public function handle(
        RegistryManager $registry,
        MigrationScanner $scanner
    ): int {
        if (!config('erd.enabled')) {
            $this->error('Laravel ERD is disabled.');

            return self::FAILURE;
        }

        $registry->initialize();

        $migrations = $scanner->scan();

        $registry->put('migrations.json', [
            'version' => 1,
            'updated_at' => now()->toIso8601String(),
            'migrations' => $migrations,
        ]);

        $this->info('Laravel ERD installed successfully.');

        $this->newLine();

        $this->line(
            'ERD URL: /' . config('erd.route.prefix')
        );

        $this->line(
            'Storage: ' . config('erd.storage.path')
        );

        $this->line(
            'Migrations discovered: ' . count($migrations)
        );

        return self::SUCCESS;
    }
}