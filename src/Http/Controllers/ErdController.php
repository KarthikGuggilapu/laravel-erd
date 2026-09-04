<?php

namespace YourVendor\LaravelErd\Http\Controllers;

use Illuminate\Routing\Controller;
use YourVendor\LaravelErd\Services\RegistryManager;

class ErdController extends Controller
{
    public function index(RegistryManager $registry)
    {
        return view('erd::erd', [
            'metadata' => $registry->get('metadata.json'),
            'migrations' => $registry->get('migrations.json'),
            'models' => $registry->get('models.json'),
            'relations' => $registry->get('relations.json'),
            'history' => $registry->get('history.json'),
            'layout' => $registry->get('layout.json'),
        ]);
    }
}