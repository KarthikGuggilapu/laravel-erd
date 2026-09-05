<?php

namespace YourVendor\LaravelErd\Services;

use Illuminate\Support\Str;

class RelationScanner
{
    public function scan(
        array $migrations,
        array $models = []
    ): array {
        $relations = [];

        foreach ($migrations as $migration) {
            foreach ($migration['tables'] ?? [] as $table) {
                $tableName = $table['name'] ?? null;

                if (!$tableName) {
                    continue;
                }

                foreach ($this->extractForeignKeys($table) as $foreignKey) {
                    $relation = $this->normalizeMigrationRelation(
                        $tableName,
                        $foreignKey
                    );

                    if ($relation) {
                        $relation['source'] = 'migration';
                        $relation['source_file'] =
                            $migration['file'] ??
                            $migration['id'] ??
                            null;

                        $relations[] = $relation;
                    }
                }
            }
        }

        foreach ($models as $model) {
            $modelTable = $model['table'] ?? null;

            if (!$modelTable) {
                continue;
            }

            foreach ($model['relations'] ?? [] as $modelRelation) {
                $relation = $this->normalizeModelRelation(
                    $model,
                    $modelRelation,
                    $models
                );

                if ($relation) {
                    $relations[] = $relation;
                }
            }
        }

        return $this->mergeRelations($relations);
    }

    protected function extractForeignKeys(array $table): array
    {
        $foreignKeys = [];

        foreach (
            [
                'foreign_keys',
                'foreignKeys',
                'foreign',
                'constraints',
            ] as $key
        ) {
            if (
                !isset($table[$key]) ||
                !is_array($table[$key])
            ) {
                continue;
            }

            foreach ($table[$key] as $foreignKey) {
                if (is_array($foreignKey)) {
                    $foreignKeys[] = $foreignKey;
                }
            }
        }

        foreach ($table['columns'] ?? [] as $column) {
            if (!is_array($column)) {
                continue;
            }

            $columnName =
                $column['name'] ??
                $column['column'] ??
                null;

            if (!$columnName) {
                continue;
            }

            $foreignTable =
                $column['on'] ??
                $column['on_table'] ??
                $column['constrained_table'] ??
                $column['foreign_table'] ??
                null;

            $foreignColumn =
                $column['references'] ??
                $column['referenced_column'] ??
                $column['foreign_column'] ??
                'id';

            if ($foreignTable) {
                $foreignKeys[] = [
                    'column' => $columnName,
                    'table' => $foreignTable,
                    'references' => $foreignColumn,
                ];
            }

            foreach (
                [
                    'foreign',
                    'references',
                    'constrained',
                ] as $key
            ) {
                if (!array_key_exists($key, $column)) {
                    continue;
                }

                $value = $column[$key];

                if (is_array($value)) {
                    $foreignKeys[] = array_merge(
                        $value,
                        [
                            'column' => $columnName,
                        ]
                    );
                }
            }
        }

        return $foreignKeys;
    }

    protected function normalizeMigrationRelation(
        string $fromTable,
        array $foreignKey
    ): ?array {
        $fromColumn = $this->firstValue(
            $foreignKey,
            [
                'column',
                'from_column',
                'local_column',
            ]
        );

        $toTable = $this->firstValue(
            $foreignKey,
            [
                'table',
                'to_table',
                'referenced_table',
                'references_table',
                'on',
                'on_table',
                'constrained_table',
            ]
        );

        $toColumn = $this->firstValue(
            $foreignKey,
            [
                'to_column',
                'referenced_column',
                'references_column',
            ]
        );

        if (
            isset($foreignKey['references']) &&
            is_array($foreignKey['references'])
        ) {
            $references = $foreignKey['references'];

            $toTable =
                $toTable ??
                $references['table'] ??
                $references['to_table'] ??
                $references['referenced_table'] ??
                null;

            $toColumn =
                $toColumn ??
                $references['column'] ??
                $references['to_column'] ??
                $references['referenced_column'] ??
                null;
        } elseif (
            isset($foreignKey['references']) &&
            is_string($foreignKey['references'])
        ) {
            $toColumn =
                $toColumn ??
                $foreignKey['references'];
        }

        $constrained = $foreignKey['constrained'] ?? null;

        if (is_array($constrained)) {
            $toTable =
                $toTable ??
                $constrained['table'] ??
                $constrained['on'] ??
                null;

            $toColumn =
                $toColumn ??
                $constrained['column'] ??
                $constrained['references'] ??
                null;
        } elseif (
            is_string($constrained) &&
            $constrained !== ''
        ) {
            $toTable =
                $toTable ??
                $constrained;
        }

        if (!$toTable && $fromColumn) {
            $candidate = Str::beforeLast(
                $fromColumn,
                '_id'
            );

            if ($candidate !== $fromColumn) {
                $toTable = Str::plural($candidate);
            }
        }

        if (!$fromColumn || !$toTable) {
            return null;
        }

        return [
            'from_table' => $this->normalizeName($fromTable),
            'from_column' => $this->normalizeName($fromColumn),
            'to_table' => $this->normalizeName($toTable),
            'to_column' => $this->normalizeName(
                $toColumn ?: 'id'
            ),
            'type' => 'belongsTo',
            'source' => 'migration',
            'source_file' => null,
            'model' => null,
            'model_file' => null,
            'relation' => null,
            'database_constraint' => true,
            'eloquent_relation' => false,
            'status' => 'database_only',
        ];
    }

    protected function normalizeModelRelation(
        array $model,
        array $relation,
        array $models
    ): ?array {
        $fromTable = $model['table'] ?? null;

        if (!$fromTable) {
            return null;
        }

        $relatedClass =
            $relation['related_class'] ??
            $relation['related'] ??
            null;

        if (!$relatedClass) {
            return null;
        }

        $relatedModel = null;

        foreach ($models as $candidate) {
            if (
                ($candidate['class'] ?? null) ===
                $relatedClass
            ) {
                $relatedModel = $candidate;
                break;
            }
        }

        $toTable =
            $relatedModel['table'] ??
            $relation['related_table'] ??
            $relation['table'] ??
            $this->classToTable($relatedClass);

        if (!$toTable) {
            return null;
        }

        $type = $relation['type'] ?? 'belongsTo';

        $foreignKey =
            $relation['foreign_key'] ??
            null;

        $localKey =
            $relation['local_key'] ??
            $relation['owner_key'] ??
            null;

        $method =
            $relation['name'] ??
            $relation['method'] ??
            null;

        if ($type === 'belongsTo') {
            $fromColumn =
                $foreignKey ??
                (
                    $method
                        ? $this->snakeCase($method) . '_id'
                        : null
                );

            $toColumn = $localKey ?? 'id';
        } elseif (
            in_array(
                $type,
                [
                    'hasOne',
                    'hasMany',
                ],
                true
            )
        ) {
            $fromColumn = $localKey ?? 'id';

            $toColumn =
                $foreignKey ??
                (
                    $model['name']
                        ? $this->snakeCase(
                            $model['name']
                        ) . '_id'
                        : null
                );
        } else {
            $fromColumn = $foreignKey ?? 'id';
            $toColumn = $localKey ?? 'id';
        }

        if (!$fromColumn || !$toColumn) {
            return null;
        }

        return [
            'from_table' =>
                $this->normalizeName($fromTable),

            'from_column' =>
                $this->normalizeName($fromColumn),

            'to_table' =>
                $this->normalizeName($toTable),

            'to_column' =>
                $this->normalizeName($toColumn),

            'type' => $type,

            'source' => 'model',

            'source_file' =>
                $model['file'] ?? null,

            'model' =>
                $model['name'] ??
                $model['class'] ??
                null,

            'model_file' =>
                $model['file'] ?? null,

            'relation' => $method,

            'database_constraint' => false,

            'eloquent_relation' => true,

            'status' => 'model_only',
        ];
    }

    protected function mergeRelations(
        array $relations
    ): array {
        $merged = [];

        foreach ($relations as $relation) {
            $key = implode(
                '|',
                [
                    $this->normalizeName(
                        $relation['from_table'] ?? ''
                    ),
                    $this->normalizeName(
                        $relation['from_column'] ?? ''
                    ),
                    $this->normalizeName(
                        $relation['to_table'] ?? ''
                    ),
                    $this->normalizeName(
                        $relation['to_column'] ?? ''
                    ),
                ]
            );

            if (!isset($merged[$key])) {
                $merged[$key] = $relation;
                continue;
            }

            $existing = $merged[$key];

            $sources = array_values(
                array_unique(
                    array_merge(
                        $existing['sources'] ?? [],
                        $relation['sources'] ?? [],
                        !empty($existing['source'])
                            ? [$existing['source']]
                            : [],
                        !empty($relation['source'])
                            ? [$relation['source']]
                            : []
                    )
                )
            );

            $existing['sources'] = $sources;

            $existing['database_constraint'] =
                (
                    $existing['database_constraint'] ??
                    false
                ) ||
                (
                    $relation['database_constraint'] ??
                    false
                );

            $existing['eloquent_relation'] =
                (
                    $existing['eloquent_relation'] ??
                    false
                ) ||
                (
                    $relation['eloquent_relation'] ??
                    false
                );

            if (
                in_array('migration', $sources, true) &&
                in_array('model', $sources, true)
            ) {
                $existing['status'] = 'matched';
            } elseif (
                in_array('migration', $sources, true)
            ) {
                $existing['status'] = 'database_only';
            } elseif (
                in_array('model', $sources, true)
            ) {
                $existing['status'] = 'model_only';
            }

            if (
                !empty($relation['source_file']) &&
                ($relation['source'] ?? null) === 'migration'
            ) {
                $existing['migration'] =
                    $relation['source_file'];
            }

            if (
                !empty($relation['model'])
            ) {
                $existing['model'] =
                    $relation['model'];
            }

            if (
                !empty($relation['model_file'])
            ) {
                $existing['model_file'] =
                    $relation['model_file'];
            }

            if (
                !empty($relation['relation'])
            ) {
                $existing['relation'] =
                    $relation['relation'];
            }

            $merged[$key] = $existing;
        }

        return array_values($merged);
    }

    protected function classToTable(
        string $class
    ): string {
        return Str::snake(
            Str::pluralStudly(
                class_basename($class)
            )
        );
    }

    protected function snakeCase(
        string $value
    ): string {
        return Str::snake($value);
    }

    protected function firstValue(
        array $data,
        array $keys
    ): mixed {
        foreach ($keys as $key) {
            if (
                isset($data[$key]) &&
                $data[$key] !== ''
            ) {
                return $data[$key];
            }
        }

        return null;
    }

    protected function normalizeName(
        mixed $value
    ): string {
        return strtolower(
            trim(
                str_replace(
                    '`',
                    '',
                    (string) $value
                )
            )
        );
    }
}