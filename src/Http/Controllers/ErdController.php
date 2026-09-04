<?php

namespace YourVendor\LaravelErd\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use YourVendor\LaravelErd\Services\MigrationScanner;
use YourVendor\LaravelErd\Services\ModelScanner;
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
        ModelScanner $modelScanner
    ) {
        if (!config('erd.enabled')) {
            return response()->json([
                'success' => false,
                'message' => 'Laravel ERD is disabled.'
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

        return response()->json([
            'success' => true,
            'message' => 'Schema analyzed successfully.',
            'migrations' => count($migrations),
            'models' => count($models),
        ]);
    }
}