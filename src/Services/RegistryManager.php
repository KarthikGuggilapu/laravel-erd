<?php

namespace YourVendor\LaravelErd\Services;

use Illuminate\Support\Facades\File;

class RegistryManager
{
    protected string $path;

    public function __construct()
    {
        $this->path = config('erd.storage.path');
    }

    public function initialize(): void
    {
        File::ensureDirectoryExists($this->path);

        $this->initializeFile('metadata.json', [
            'version' => 1,
            'package_version' => '0.1.0',
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
        $path = $this->filePath($file);

        if (!File::exists($path)) {
            return [];
        }

        $contents = File::get($path);

        return json_decode($contents, true) ?? [];
    }

    public function put(string $file, array $data): void
    {
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
        return File::exists($this->filePath('metadata.json'));
    }

    protected function initializeFile(string $file, array $data): void
    {
        if (!File::exists($this->filePath($file))) {
            $this->put($file, $data);
        }
    }

    protected function filePath(string $file): string
    {
        return $this->path . DIRECTORY_SEPARATOR . $file;
    }
}