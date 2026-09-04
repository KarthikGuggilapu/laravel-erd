<?php

namespace YourVendor\LaravelErd\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;

class RegistryManager
{
    protected string $path;

    protected array $files = [
        'metadata.json',
        'migrations.json',
        'models.json',
        'relations.json',
        'history.json',
        'layout.json',
    ];

    public function __construct()
    {
        $this->path = config('erd.storage.path');
    }

    public function initialize(): void
    {
        File::ensureDirectoryExists($this->path);

        $this->initializeFile('metadata.json', [
            'version' => 1,
            'package_version' => $this->packageVersion(),
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ]);

        $this->initializeFile('migrations.json', [
            'version' => 1,
            'migrations' => [],
        ]);

        $this->initializeFile('models.json', [
            'version' => 1,
            'models' => [],
        ]);

        $this->initializeFile('relations.json', [
            'version' => 1,
            'relations' => [],
        ]);

        $this->initializeFile('history.json', [
            'version' => 1,
            'events' => [],
        ]);

        $this->initializeFile('layout.json', [
            'version' => 1,
            'tables' => [],
        ]);
    }

    public function get(string $file): array
    {
        if (!in_array($file, $this->files, true)) {
            throw new RuntimeException(
                "Invalid ERD registry file: {$file}"
            );
        }

        $path = $this->filePath($file);

        if (!File::exists($path)) {
            return [];
        }

        $contents = File::get($path);

        return json_decode($contents, true) ?? [];
    }

    public function put(string $file, array $data): void
    {
        if (!in_array($file, $this->files, true)) {
            throw new RuntimeException(
                "Invalid ERD registry file: {$file}"
            );
        }

        File::ensureDirectoryExists($this->path);

        $path = $this->filePath($file);
        $temporary = $path . '.tmp';

        File::put(
            $temporary,
            json_encode(
                $data,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );

        if (File::exists($path)) {
            File::delete($path);
        }

        File::move($temporary, $path);
    }

    public function update(string $file, callable $callback): array
    {
        $data = $this->get($file);

        $data = $callback($data);

        $this->put($file, $data);

        return $data;
    }

    public function exists(): bool
    {
        return File::exists(
            $this->filePath('metadata.json')
        );
    }

    public function isOwnedDirectory(): bool
    {
        if (!File::isDirectory($this->path)) {
            return false;
        }

        foreach ($this->files as $file) {
            if (File::exists($this->filePath($file))) {
                continue;
            }
        }

        return $this->hasExpectedRegistryFiles();
    }

    protected function hasExpectedRegistryFiles(): bool
    {
        foreach ($this->files as $file) {
            if (File::exists($this->filePath($file))) {
                return true;
            }
        }

        return false;
    }

    protected function initializeFile(
        string $file,
        array $data
    ): void {
        if (!File::exists($this->filePath($file))) {
            $this->put($file, $data);
        }
    }

    protected function filePath(string $file): string
    {
        return $this->path . DIRECTORY_SEPARATOR . $file;
    }

    protected function packageVersion(): string
    {
        $composer = dirname(__DIR__, 2) . '/composer.json';

        if (!File::exists($composer)) {
            return '0.0.2';
        }

        $data = json_decode(
            File::get($composer),
            true
        );

        return $data['version'] ?? '0.0.2';
    }
}