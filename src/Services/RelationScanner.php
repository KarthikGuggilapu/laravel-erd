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

                foreach (
                    $this->extractForeignKeys($table)
                    as $foreignKey
                ) {
                    $relation = $this->normalizeMigrationRelation(
                        $tableName,
                        $foreignKey
                    );

                    if ($relation) {
                        $relations[] = $relation;
                    }
                }
            }
        }

        foreach ($models as $model) {
            foreach ($model['relations'] ?? [] as $relation) {
                $normalized = $this->normalizeModelRelation(
                    $model,
                    $relation
                );

                if ($normalized) {
                    $relations[] = $normalized;
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
                if (
                    !array_key_exists(
                        $key,
                        $column
                    )
                ) {
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
        $fromColumn =
            $this->firstValue(
                $foreignKey,
                [
                    'column',
                    'from_column',
                    'local_column',
                ]
            );

        $toTable =
            $this->firstValue(
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

        $toColumn =
            $this->firstValue(
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
            $references =
                $foreignKey['references'];

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
            is_string(
                $foreignKey['references']
            )
        ) {
            $toColumn =
                $toColumn ??
                $foreignKey['references'];
        }

        if (!$toTable && $fromColumn) {
            $candidate =
                Str::beforeLast(
                    $fromColumn,
                    '_id'
                );

            if ($candidate !== $fromColumn) {
                $toTable =
                    Str::plural($candidate);
            }
        }

        if (!$fromColumn || !$toTable) {
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
                $this->normalizeName(
                    $toColumn ?: 'id'
                ),

            'type' =>
                'belongsTo',

            'sources' =>
                ['migration'],

            'database_constraint' =>
                true,

            'eloquent_relation' =>
                false,

            'status' =>
                'database_only',
        ];
    }

    protected function normalizeModelRelation(
        array $model,
        array $relation
    ): ?array {
        $fromTable =
            $model['table'] ??
            null;

        $type =
            strtolower(
                $relation['type'] ?? ''
            );

        $toTable =
            $relation['related_table'] ??
            $relation['table'] ??
            null;

        if (!$toTable && isset($relation['related'])) {
            $toTable =
                $this->classToTable(
                    $relation['related']
                );
        }

        if (!$fromTable || !$toTable) {
            return null;
        }

        $fromColumn =
            $relation['foreign_key'] ??
            null;

        $toColumn =
            $relation['owner_key'] ??
            'id';

        if (
            in_array(
                $type,
                [
                    'hasmany',
                    'hasone',
                    'hasmanythrough',
                    'hasonethrough',
                ],
                true
            )
        ) {
            $fromColumn =
                $toColumn;

            $toColumn =
                $relation['foreign_key'] ??
                Str::snake(
                    class_basename(
                        $model['class'] ??
                        $model['name'] ??
                        ''
                    )
                ) . '_id';
        }

        if (
            !$fromColumn &&
            in_array(
                $type,
                [
                    'belongsto',
                    'morphto',
                ],
                true
            )
        ) {
            $fromColumn =
                Str::snake(
                    $relation['name'] ?? ''
                ) . '_id';
        }

        if (!$fromColumn) {
            return null;
        }

        return [
            'from_table' =>
                $this->normalizeName(
                    $fromTable
                ),

            'from_column' =>
                $this->normalizeName(
                    $fromColumn
                ),

            'to_table' =>
                $this->normalizeName(
                    $toTable
                ),

            'to_column' =>
                $this->normalizeName(
                    $toColumn
                ),

            'type' =>
                $type ?: 'belongsTo',

            'sources' =>
                ['model'],

            'database_constraint' =>
                false,

            'eloquent_relation' =>
                true,

            'model' =>
                $model['class'] ??
                $model['name'] ??
                null,

            'method' =>
                $relation['name'] ??
                null,

            'status' =>
                'model_only',
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
                        $relation['from_table']
                    ),
                    $this->normalizeName(
                        $relation['from_column'] ?? ''
                    ),
                    $this->normalizeName(
                        $relation['to_table']
                    ),
                    $this->normalizeName(
                        $relation['to_column'] ?? 'id'
                    ),
                ]
            );

            if (!isset($merged[$key])) {
                $merged[$key] = $relation;

                continue;
            }

            $existing =
                $merged[$key];

            $sources = array_values(
                array_unique(
                    array_merge(
                        $existing['sources'] ?? [],
                        $relation['sources'] ?? []
                    )
                )
            );

            $existing['sources'] =
                $sources;

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
                in_array(
                    'migration',
                    $sources,
                    true
                ) &&
                in_array(
                    'model',
                    $sources,
                    true
                )
            ) {
                $existing['status'] =
                    'matched';
            }

            if (
                !empty(
                    $relation['model']
                )
            ) {
                $existing['model'] =
                    $relation['model'];
            }

            if (
                !empty(
                    $relation['method']
                )
            ) {
                $existing['method'] =
                    $relation['method'];
            }

            $merged[$key] =
                $existing;
        }

        return array_values(
            $merged
        );
    }

    protected function classToTable(
        string $class
    ): ?string {
        $class =
            trim(
                $class,
                '\\'
            );

        if (!$class) {
            return null;
        }

        return Str::snake(
            Str::plural(
                class_basename($class)
            )
        );
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