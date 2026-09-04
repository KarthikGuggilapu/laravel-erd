<?php

namespace YourVendor\LaravelErd\Commands;

use Illuminate\Console\Command;
use YourVendor\LaravelErd\Services\RegistryManager;

class InstallCommand extends Command
{
    protected $signature = 'erd:install';

    protected $description = 'Install and initialize Laravel ERD';

    public function handle(RegistryManager $registry): int
    {
        if (!config('erd.enabled')) {
            $this->error('Laravel ERD is disabled.');

            return self::FAILURE;
        }

        $registry->initialize();

        $this->info('Laravel ERD installed successfully.');
        $this->newLine();
        $this->line('ERD URL: /' . config('erd.route.prefix'));
        $this->line('Storage: ' . config('erd.storage.path'));

        return self::SUCCESS;
    }
}