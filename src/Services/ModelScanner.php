<?php

namespace YourVendor\LaravelErd\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModelScanner
{
    public function scan(): array
    {
        $modelsPath = app_path('Models');

        if (!File::isDirectory($modelsPath)) {
            return [];
        }

        $models = [];

        foreach (File::allFiles($modelsPath) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = File::get($file->getPathname());

            $model = $this->parseModel(
                $content,
                $file->getPathname()
            );

            if ($model) {
                $models[] = $model;
            }
        }

        return $models;
    }

    protected function parseModel(
        string $content,
        string $path
    ): ?array {
        $namespace = $this->extractNamespace($content);

        $class = $this->extractClass($content);

        if (!$class) {
            return null;
        }

        $fillable = $this->extractArrayProperty(
            $content,
            'fillable'
        );

        $casts = $this->extractCasts($content);

        $traits = $this->extractTraits($content);

        $relations = $this->extractRelations($content);

        $table = $this->extractTable($content);

        if (!$table) {
            $table = Str::snake(
                Str::pluralStudly($class)
            );
        }

        $relativePath = Str::after(
            $path,
            base_path() . DIRECTORY_SEPARATOR
        );

        return [
            'name' => $class,
            'class' => $namespace
                ? $namespace . '\\' . $class
                : $class,
            'file' => str_replace(
                DIRECTORY_SEPARATOR,
                '/',
                $relativePath
            ),
            'table' => $table,
            'traits' => $traits,
            'fillable' => $fillable,
            'casts' => $casts,
            'relations' => $relations,
            'status' => 'active',
        ];
    }

    protected function extractNamespace(
        string $content
    ): ?string {
        if (preg_match(
            '/namespace\s+([^;]+);/',
            $content,
            $matches
        )) {
            return trim($matches[1]);
        }

        return null;
    }

    protected function extractClass(
        string $content
    ): ?string {
        if (preg_match(
            '/class\s+([A-Za-z_][A-Za-z0-9_]*)/',
            $content,
            $matches
        )) {
            return $matches[1];
        }

        return null;
    }

    protected function extractTable(
        string $content
    ): ?string {
        if (preg_match(
            '/protected\s+\$table\s*=\s*[\'"]([^\'"]+)[\'"]\s*;/',
            $content,
            $matches
        )) {
            return $matches[1];
        }

        return null;
    }

    protected function extractArrayProperty(
        string $content,
        string $property
    ): array {
        if (!preg_match(
            '/(?:protected|private|public)\s+\$' . preg_quote($property, '/') . '\s*=\s*\[(.*?)\];/s',
            $content,
            $matches
        )) {
            return [];
        }

        preg_match_all(
            '/[\'"]([^\'"]+)[\'"]/',
            $matches[1],
            $values
        );

        return array_values(
            array_unique($values[1] ?? [])
        );
    }

    protected function extractCasts(
        string $content
    ): array {
        if (!preg_match(
            '/(?:protected|private|public)\s+\$casts\s*=\s*\[(.*?)\];/s',
            $content,
            $matches
        )) {
            return [];
        }

        preg_match_all(
            '/[\'"]([^\'"]+)[\'"]\s*=>\s*[\'"]([^\'"]+)[\'"]/',
            $matches[1],
            $values,
            PREG_SET_ORDER
        );

        $casts = [];

        foreach ($values as $value) {
            $casts[$value[1]] = $value[2];
        }

        return $casts;
    }

    protected function extractTraits(
        string $content
    ): array {
        if (!preg_match(
            '/\buse\s+([^;]+);/',
            $content,
            $matches
        )) {
            return [];
        }

        $traits = [];

        foreach (
            preg_split('/\s*,\s*/', $matches[1])
            as $trait
        ) {
            $trait = trim($trait);

            if (!$trait) {
                continue;
            }

            $trait = preg_replace(
                '/\s+as\s+.*$/',
                '',
                $trait
            );

            $traits[] = trim($trait, '\\ ');
        }

        return array_values(
            array_unique($traits)
        );
    }

    protected function extractRelations(
        string $content
    ): array {
        preg_match_all(
            '/public\s+function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\([^)]*\)\s*(?::\s*[^{]+)?\s*\{(.*?)\}/s',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        $relationMethods = [
            'hasOne',
            'hasMany',
            'hasOneThrough',
            'hasManyThrough',
            'belongsTo',
            'belongsToMany',
            'morphOne',
            'morphMany',
            'morphTo',
            'morphToMany',
            'morphedByMany',
        ];

        $relations = [];

        foreach ($matches as $match) {
            $methodBody = $match[2];

            foreach ($relationMethods as $relationMethod) {
                if (str_contains(
                    $methodBody,
                    '$this->' . $relationMethod . '('
                )) {
                    $relations[] = $match[1];

                    break;
                }
            }
        }

        return array_values(
            array_unique($relations)
        );
    }
}