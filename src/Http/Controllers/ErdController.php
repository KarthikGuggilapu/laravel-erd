<?php

namespace YourVendor\LaravelErd\Http\Controllers;

use Illuminate\Routing\Controller;
use YourVendor\LaravelErd\Services\MigrationScanner;
use YourVendor\LaravelErd\Services\ModelScanner;
use YourVendor\LaravelErd\Services\RelationScanner;
use YourVendor\LaravelErd\Services\RegistryManager;

class ErdController extends Controller
{
    public function index(RegistryManager $registry)
    {
        return view('erd::index', [
            'metadata' => $registry->get('metadata.json'),
            'migrations' => $registry->get('migrations.json'),
            'models' => $registry->get('models.json'),
            'relations' => $registry->get('relations.json'),
            'history' => $registry->get('history.json'),
            'layout' => $registry->get('layout.json'),
        ]);
    }

    public function refresh(
        RegistryManager $registry,
        MigrationScanner $migrationScanner,
        ModelScanner $modelScanner,
        RelationScanner $relationScanner
    ) {
        if (!config('erd.enabled')) {
            return response()->json([
                'success' => false,
                'message' => 'Laravel ERD is disabled.',
            ], 403);
        }

        $registry->initialize();

        $migrations = $migrationScanner->scan();

        $registry->put('migrations.json', [
            'version' => 1,
            'updated_at' => now()->toIso8601String(),
            'migrations' => $migrations,
        ]);

        $models = $modelScanner->scan();

        $registry->put('models.json', [
            'version' => 1,
            'updated_at' => now()->toIso8601String(),
            'models' => $models,
        ]);

        $relations = $relationScanner->scan(
            $migrations,
            $models
        );

        $registry->put('relations.json', [
            'version' => 1,
            'updated_at' => now()->toIso8601String(),
            'relations' => $relations,
        ]);

        $history = $this->buildHistory(
            $migrations,
            $models,
            $relations
        );

        $registry->put('history.json', [
            'version' => 1,
            'updated_at' => now()->toIso8601String(),
            'tables' => $history,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Schema analyzed successfully.',
            'migrations' => count($migrations),
            'models' => count($models),
            'relations' => count($relations),
        ]);
    }

    protected function buildHistory(
        array $migrations,
        array $models,
        array $relations
    ): array {
        $history = [];

        foreach ($migrations as $migration) {
            foreach ($migration['tables'] ?? [] as $table) {
                $tableName = $table['name'] ?? null;

                if (!$tableName) {
                    continue;
                }

                $key = strtolower($tableName);

                if (!isset($history[$key])) {
                    $history[$key] = [
                        'table' => $tableName,
                        'migrations' => [],
                        'models' => [],
                        'relations' => [],
                    ];
                }

                $history[$key]['migrations'][] = [
                    'file' => $migration['file']
                        ?? $migration['id']
                        ?? null,

                    'operation' => $table['operation']
                        ?? 'table',

                    'columns' => $table['columns']
                        ?? [],
                ];
            }
        }

        foreach ($models as $model) {
            $tableName = $model['table'] ?? null;

            if (!$tableName) {
                continue;
            }

            $key = strtolower($tableName);

            if (!isset($history[$key])) {
                $history[$key] = [
                    'table' => $tableName,
                    'migrations' => [],
                    'models' => [],
                    'relations' => [],
                ];
            }

            $history[$key]['models'][] = [
                'name' => $model['name'] ?? null,
                'class' => $model['class'] ?? null,
                'file' => $model['file'] ?? null,
                'relations' => $model['relations'] ?? [],
            ];
        }

        foreach ($relations as $relation) {
            $fromTable = $relation['from_table'] ?? null;
            $toTable = $relation['to_table'] ?? null;

            if ($fromTable) {
                $fromKey = strtolower($fromTable);

                if (!isset($history[$fromKey])) {
                    $history[$fromKey] = [
                        'table' => $fromTable,
                        'migrations' => [],
                        'models' => [],
                        'relations' => [],
                    ];
                }

                $history[$fromKey]['relations'][] = $relation;
            }

            if (
                $toTable &&
                strtolower($toTable) !== strtolower($fromTable ?? '')
            ) {
                $toKey = strtolower($toTable);

                if (!isset($history[$toKey])) {
                    $history[$toKey] = [
                        'table' => $toTable,
                        'migrations' => [],
                        'models' => [],
                        'relations' => [],
                    ];
                }

                $history[$toKey]['relations'][] = $relation;
            }
        }

        foreach ($history as &$tableHistory) {
            $tableHistory['migrations'] =
                $this->uniqueHistoryItems(
                    $tableHistory['migrations']
                );

            $tableHistory['models'] =
                $this->uniqueHistoryItems(
                    $tableHistory['models']
                );

            $tableHistory['relations'] =
                $this->uniqueHistoryItems(
                    $tableHistory['relations']
                );
        }

        unset($tableHistory);

        return array_values($history);
    }

    protected function uniqueHistoryItems(
        array $items
    ): array {
        $unique = [];

        foreach ($items as $item) {
            $key = md5(
                json_encode(
                    $item,
                    JSON_UNESCAPED_SLASHES
                )
            );

            $unique[$key] = $item;
        }

        return array_values($unique);
    }
}