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
                if (!$tableName) continue;

                foreach ($this->extractForeignKeys($table) as $foreignKey) {
                    $relation = $this->normalizeRelation($tableName, $foreignKey);
                    if ($relation) $relations[] = $relation;
                }
            }
        }

        return $this->uniqueRelations($relations);
    }

    protected function extractForeignKeys(array $table): array
    {
        $foreignKeys = [];

        foreach (['foreign_keys', 'foreignKeys', 'foreign', 'constraints'] as $key) {
            if (!isset($table[$key]) || !is_array($table[$key])) continue;
            foreach ($table[$key] as $foreignKey) {
                if (is_array($foreignKey)) $foreignKeys[] = $foreignKey;
            }
        }

        foreach ($table['columns'] ?? [] as $column) {
            if (!is_array($column)) continue;
            $columnName = $column['name'] ?? $column['column'] ?? null;

            foreach (['foreign', 'references', 'constrained'] as $key) {
                if (!isset($column[$key])) continue;
                $value = $column[$key];

                if (is_array($value)) {
                    $foreignKeys[] = array_merge($value, ['column' => $columnName]);
                } elseif (is_string($value)) {
                    $foreignKeys[] = [
                        'column' => $columnName,
                        $key => $value,
                        'on' => $column['on'] ?? $column['table'] ?? null,
                    ];
                }
            }

            $onTable = $column['on'] ?? $column['on_table'] ?? $column['constrained_table'] ?? null;
            if ($columnName && $onTable) {
                $foreignKeys[] = [
                    'column' => $columnName,
                    'table' => $onTable,
                    'references' => $column['references'] ?? $column['referenced_column'] ?? 'id',
                ];
            }
        }

        return $foreignKeys;
    }

    protected function normalizeRelation(string $fromTable, array $foreignKey): ?array
    {
        $fromColumn = $this->firstValue($foreignKey, ['column', 'from_column', 'local_column', 'local']);
        $toTable = $this->firstValue($foreignKey, ['table', 'to_table', 'referenced_table', 'references_table', 'on', 'on_table', 'constrained_table']);
        $toColumn = $this->firstValue($foreignKey, ['to_column', 'referenced_column', 'references_column']);

        $references = $foreignKey['references'] ?? null;
        if (is_array($references)) {
            $toTable = $toTable ?? $references['table'] ?? $references['to_table'] ?? $references['referenced_table'] ?? null;
            $toColumn = $toColumn ?? $references['column'] ?? $references['to_column'] ?? $references['referenced_column'] ?? null;
        } elseif (is_string($references) && $references !== '') {
            $toColumn = $toColumn ?? $references;
        }

        $constrained = $foreignKey['constrained'] ?? null;
        if (is_array($constrained)) {
            $toTable = $toTable ?? $constrained['table'] ?? $constrained['on'] ?? null;
            $toColumn = $toColumn ?? $constrained['column'] ?? $constrained['references'] ?? null;
        } elseif (is_string($constrained) && $constrained !== '') {
            $toTable = $toTable ?? $constrained;
        }

        $toColumn = $toColumn ?: 'id';

        if (!$fromColumn || !$toTable || !$toColumn) return null;

        return [
            'from_table' => $fromTable,
            'from_column' => $fromColumn,
            'to_table' => $toTable,
            'to_column' => $toColumn,
            'type' => 'belongsTo',
        ];
    }

    protected function firstValue(array $data, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (isset($data[$key]) && $data[$key] !== '') return $data[$key];
        }
        return null;
    }

    protected function uniqueRelations(array $relations): array
    {
        $unique = [];
        foreach ($relations as $relation) {
            $key = implode('|', [$relation['from_table'], $relation['from_column'], $relation['to_table'], $relation['to_column']]);
            $unique[$key] = $relation;
        }
        return array_values($unique);
    }
}
