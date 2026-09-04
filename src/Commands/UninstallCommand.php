<?php

namespace YourVendor\LaravelErd\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use YourVendor\LaravelErd\Services\RegistryManager;

class UninstallCommand extends Command
{
    protected $signature = 'erd:uninstall
        {--force : Remove ERD registry data without confirmation}';

    protected $description = 'Remove Laravel ERD generated registry data';

    public function handle(RegistryManager $registry): int
    {
        if (!$this->option('force') && !$this->confirm(
            'This will permanently delete the Laravel ERD registry data. Continue?'
        )) {
            $this->info('Uninstallation cancelled.');

            return self::SUCCESS;
        }

        $path = config('erd.storage.path');

        if (!File::exists($path)) {
            $this->info('Laravel ERD registry data does not exist.');

            return self::SUCCESS;
        }

        if (!$registry->isOwnedDirectory()) {
            $this->error(
                'The ERD storage directory could not be verified as package-owned.'
            );

            return self::FAILURE;
        }

        File::deleteDirectory($path);

        $this->info('Laravel ERD registry data removed successfully.');

        return self::SUCCESS;
    }
}