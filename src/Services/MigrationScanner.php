<?php

namespace YourVendor\LaravelErd\Services;

use Illuminate\Support\Facades\File;

class MigrationScanner
{
    public function scan(): array
    {
        $path = database_path('migrations');

        if (!File::isDirectory($path)) {
            return [];
        }

        $files = File::glob($path . DIRECTORY_SEPARATOR . '*.php');

        $migrations = [];

        foreach ($files as $file) {
            $migration = $this->parseFile($file);

            if ($migration) {
                $migrations[] = $migration;
            }
        }

        return $migrations;
    }

    protected function parseFile(string $file): ?array
    {
        $contents = File::get($file);
        $filename = basename($file);

        preg_match(
            '/(\d{4}_\d{2}_\d{2}_\d{6})_(.+)\.php$/',
            $filename,
            $matches
        );

        if (!$matches) {
            return null;
        }

        $id = $matches[1] . '_' . $matches[2];

        $tables = $this->extractTables($contents);

        return [
            'id' => $id,
            'file' => $filename,
            'path' => $file,
            'class' => $this->extractClass($contents),
            'tables' => $tables,
            'status' => 'unknown',
            'exists' => true,
            'scanned_at' => now()->toIso8601String(),
        ];
    }

    protected function extractClass(string $contents): ?string
    {
        if (preg_match(
            '/class\s+([A-Za-z0-9_]+)/',
            $contents,
            $matches
        )) {
            return $matches[1];
        }

        return null;
    }

    protected function extractTables(string $contents): array
    {
        $tables = [];

        preg_match_all(
            '/Schema::(create|table|rename|dropIfExists|drop)\s*\(\s*[\'"]([^\'"]+)[\'"]/m',
            $contents,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $operation = $match[1];
            $table = $match[2];

            if (in_array($operation, ['drop', 'dropIfExists'], true)) {
                $tables[] = [
                    'name' => $table,
                    'operation' => $operation,
                    'columns' => [],
                    'indexes' => [],
                    'foreign_keys' => [],
                ];

                continue;
            }

            $tables[] = [
                'name' => $table,
                'operation' => $operation,
                'columns' => $this->extractColumns($contents, $table),
                'indexes' => $this->extractIndexes($contents),
                'foreign_keys' => $this->extractForeignKeys($contents),
            ];
        }

        return $tables;
    }

    protected function extractColumns(
        string $contents,
        string $table
    ): array {
        $columns = [];

        $patterns = [
            'id' => '/\$table->id\s*\(\s*[\'"]?([^\'"\)]*)[\'"]?\s*\)/',
            'bigIncrements' => '/\$table->bigIncrements\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            'increments' => '/\$table->increments\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            'uuid' => '/\$table->uuid\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            'string' => '/\$table->string\s*\(\s*[\'"]([^\'"]+)[\'"](?:\s*,\s*(\d+))?\s*\)/',
            'text' => '/\$table->text\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            'mediumText' => '/\$table->mediumText\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            'longText' => '/\$table->longText\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            'integer' => '/\$table->integer\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            'bigInteger' => '/\$table->bigInteger\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            'smallInteger' => '/\$table->smallInteger\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            'tinyInteger' => '/\$table->tinyInteger\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            'boolean' => '/\$table->boolean\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            'decimal' => '/\$table->decimal\s*\(\s*[\'"]([^\'"]+)[\'"](?:\s*,\s*(\d+))?(?:\s*,\s*(\d+))?\s*\)/',
            'float' => '/\$table->float\s*\(\s*[\'"]([^\'"]+)[\'"](?:\s*,\s*(\d+))?(?:\s*,\s*(\d+))?\s*\)/',
            'double' => '/\$table->double\s*\(\s*[\'"]([^\'"]+)[\'"](?:\s*,\s*(\d+))?(?:\s*,\s*(\d+))?\s*\)/',
            'date' => '/\$table->date\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            'dateTime' => '/\$table->dateTime\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            'timestamp' => '/\$table->timestamp\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            'time' => '/\$table->time\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            'json' => '/\$table->json\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            'jsonb' => '/\$table->jsonb\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            'enum' => '/\$table->enum\s*\(\s*[\'"]([^\'"]+)[\'"]/',
            'foreignId' => '/\$table->foreignId\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            'foreignUuid' => '/\$table->foreignUuid\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
        ];

        foreach ($patterns as $type => $pattern) {
            preg_match_all(
                $pattern,
                $contents,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $match) {
                $name = trim($match[1] ?? '');

                if ($type === 'id' && $name === '') {
                    $name = 'id';
                }

                if ($name === '') {
                    continue;
                }

                $column = [
                    'name' => $name,
                    'type' => $type,
                    'nullable' => str_contains(
                        $this->columnDefinition($contents, $name),
                        '->nullable()'
                    ),
                    'unique' => str_contains(
                        $this->columnDefinition($contents, $name),
                        '->unique()'
                    ),
                    'unsigned' => str_contains(
                        $this->columnDefinition($contents, $name),
                        '->unsigned()'
                    ),
                    'primary' => $type === 'id'
                        || $type === 'increments'
                        || $type === 'bigIncrements',
                ];

                if (isset($match[2]) && $match[2] !== '') {
                    $column['length'] = (int) $match[2];
                }

                if ($type === 'decimal' || $type === 'float' || $type === 'double') {
                    if (isset($match[2]) && $match[2] !== '') {
                        $column['precision'] = (int) $match[2];
                    }

                    if (isset($match[3]) && $match[3] !== '') {
                        $column['scale'] = (int) $match[3];
                    }
                }

                $columns[$name] = $column;
            }
        }

        if (preg_match(
            '/\$table->timestamps\s*\(\s*(\d+)?\s*\)/',
            $contents,
            $matches
        )) {
            $columns['created_at'] = [
                'name' => 'created_at',
                'type' => 'timestamp',
                'nullable' => true,
                'unique' => false,
                'unsigned' => false,
                'primary' => false,
            ];

            $columns['updated_at'] = [
                'name' => 'updated_at',
                'type' => 'timestamp',
                'nullable' => true,
                'unique' => false,
                'unsigned' => false,
                'primary' => false,
            ];
        }

        if (str_contains($contents, '$table->softDeletes')) {
            $columns['deleted_at'] = [
                'name' => 'deleted_at',
                'type' => 'timestamp',
                'nullable' => true,
                'unique' => false,
                'unsigned' => false,
                'primary' => false,
            ];
        }

        return array_values($columns);
    }

    protected function columnDefinition(
        string $contents,
        string $column
    ): string {
        $quoted = preg_quote($column, '/');

        if (preg_match(
            '/\$table->\w+\s*\(\s*[\'"]' . $quoted . '[\'"][^;]*;/',
            $contents,
            $matches
        )) {
            return $matches[0];
        }

        return '';
    }

    protected function extractIndexes(string $contents): array
    {
        $indexes = [];

        preg_match_all(
            '/\$table->(index|unique|primary|fullText|spatialIndex)\s*\(\s*(.*?)\s*\)/',
            $contents,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $columns = trim($match[2]);

            $columns = trim(
                $columns,
                "[]'\" "
            );

            $columns = array_map(
                fn ($column) => trim(
                    $column,
                    "'\" "
                ),
                preg_split('/\s*,\s*/', $columns)
            );

            $indexes[] = [
                'type' => $match[1],
                'columns' => array_values(
                    array_filter($columns)
                ),
            ];
        }

        return $indexes;
    }

    protected function extractForeignKeys(string $contents): array
    {
        $foreignKeys = [];

        preg_match_all(
            '/foreignId\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\).*?->constrained\s*\(\s*(?:[\'"]([^\'"]+)[\'"])?\s*\)/s',
            $contents,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $column = $match[1];

            $referencedTable = $match[2] ?? null;

            if (!$referencedTable) {
                $referencedTable = str_ends_with($column, '_id')
                    ? str_replace('_id', '', $column)
                    : $column;
            }

            $foreignKeys[] = [
                'column' => $column,
                'referenced_table' => $referencedTable,
                'referenced_column' => 'id',
                'on_delete' => null,
                'on_update' => null,
            ];
        }

        preg_match_all(
            '/foreign\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)\s*->references\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)\s*->on\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            $contents,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $foreignKeys[] = [
                'column' => $match[1],
                'referenced_table' => $match[3],
                'referenced_column' => $match[2],
                'on_delete' => null,
                'on_update' => null,
            ];
        }

        return $foreignKeys;
    }
}