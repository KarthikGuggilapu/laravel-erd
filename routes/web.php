<?php

use Illuminate\Support\Facades\Route;
use YourVendor\LaravelErd\Http\Controllers\ErdController;
use YourVendor\LaravelErd\Http\Middleware\EnsureErdEnabled;

Route::middleware([
    EnsureErdEnabled::class,
])
    ->prefix(config('erd.route.prefix'))
    ->group(function () {
        Route::get('/', [ErdController::class, 'index'])
            ->name('erd.index');

        Route::post('/refresh', [ErdController::class, 'refresh'])
            ->name('erd.refresh');
    });