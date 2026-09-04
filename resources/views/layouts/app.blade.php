<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} ERD</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='14' fill='%23315ea8'/%3E%3Cpath d='M18 17h28v7H25v8h18v7H25v8h21v7H18z' fill='white'/%3E%3C/svg%3E">
    <link rel="stylesheet" href="{{ asset('vendor/erd/erd.css') }}">
    @stack('styles')
</head>
<body>
    @yield('content')

    <script>
        window.ERD = @json([
            'metadata' => $metadata ?? [],
            'migrations' => $migrations ?? [],
            'models' => $models ?? [],
            'relations' => $relations ?? [],
            'history' => $history ?? [],
            'layout' => $layout ?? [],
        ]);

        window.ERD_CONFIG = {
            refreshUrl: @json(route('erd.refresh')),
            indexUrl: @json(route('erd.index')),
            appName: @json(config('app.name')),
        };
    </script>

    <script type="module" src="{{ asset('vendor/erd/js/erd.js') }}"></script>
    @stack('scripts')
</body>
</html>
