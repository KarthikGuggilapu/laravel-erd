<?php

namespace YourVendor\LaravelErd\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureErdEnabled
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        if (!config('erd.enabled')) {
            abort(404);
        }

        return $next($request);
    }
}