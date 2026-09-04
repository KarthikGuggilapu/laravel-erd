<?php

namespace YourVendor\LaravelErd\Services;

class RelationScanner
{
    public function scan(array $migrations): array
    {
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
                    $relation = $this->normalizeRelation(
                        $tableName,
                        $foreignKey
                    );

                    if (!$relation) {
                        continue;
                    }

                    $relations[] = $relation;
                }
            }
        }

        return $this->uniqueRelations($relations);
    }

    protected function extractForeignKeys(array $table): array
    {
        foreach ([
            'foreign_keys',
            'foreignKeys',
            'foreign',
            'constraints',
        ] as $key) {
            if (
                isset($table[$key]) &&
                is_array($table[$key])
            ) {
                return $table[$key];
            }
        }

        $foreignKeys = [];

        foreach ($table['columns'] ?? [] as $column) {
            if (!is_array($column)) {
                continue;
            }

            if (
                isset($column['foreign']) &&
                is_array($column['foreign'])
            ) {
                $foreignKeys[] = array_merge(
                    $column['foreign'],
                    [
                        'column' => $column['name'] ?? null,
                    ]
                );

                continue;
            }

            if (
                isset($column['references']) &&
                is_array($column['references'])
            ) {
                $foreignKeys[] = [
                    'column' => $column['name'] ?? null,
                    'references' => $column['references'],
                ];
            }
        }

        return $foreignKeys;
    }

    protected function normalizeRelation(
        string $fromTable,
        array $foreignKey
    ): ?array {
        $fromColumn =
            $foreignKey['column'] ??
            $foreignKey['from_column'] ??
            $foreignKey['local_column'] ??
            $foreignKey['local'] ??
            null;

        $toTable =
            $foreignKey['table'] ??
            $foreignKey['to_table'] ??
            $foreignKey['referenced_table'] ??
            $foreignKey['references_table'] ??
            null;

        $toColumn =
            $foreignKey['references'] ??
            $foreignKey['to_column'] ??
            $foreignKey['referenced_column'] ??
            $foreignKey['references_column'] ??
            'id';

        if (is_array($toColumn)) {
            $toTable =
                $toTable ??
                $toColumn['table'] ??
                $toColumn['to_table'] ??
                $toColumn['referenced_table'] ??
                null;

            $toColumn =
                $toColumn['column'] ??
                $toColumn['to_column'] ??
                $toColumn['referenced_column'] ??
                'id';
        }

        if (!$toTable) {
            $references =
                $foreignKey['references'] ?? null;

            if (is_string($references)) {
                $toColumn = $references;
            }

            $toTable =
                $foreignKey['on'] ??
                $foreignKey['on_table'] ??
                null;
        }

        if (
            !$fromColumn ||
            !$toTable ||
            !$toColumn
        ) {
            return null;
        }

        return [
            'from_table' => $fromTable,
            'from_column' => $fromColumn,
            'to_table' => $toTable,
            'to_column' => $toColumn,
            'type' => 'belongsTo',
        ];
    }

    protected function uniqueRelations(
        array $relations
    ): array {
        $unique = [];

        foreach ($relations as $relation) {
            $key = implode('|', [
                $relation['from_table'],
                $relation['from_column'],
                $relation['to_table'],
                $relation['to_column'],
            ]);

            $unique[$key] = $relation;
        }

        return array_values($unique);
    }
}