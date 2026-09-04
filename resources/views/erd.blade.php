<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Laravel ERD</title>

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            font-family: Inter, system-ui, sans-serif;
            background: #0b1020;
            color: #fff;
        }

        .erd-app {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .erd-header {
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            border-bottom: 1px solid rgba(255,255,255,.08);
            background: #11182b;
        }

        .erd-title {
            font-size: 18px;
            font-weight: 700;
        }

        .erd-status {
            font-size: 13px;
            color: #8ea0bd;
        }

        .erd-canvas {
            position: relative;
            flex: 1;
            overflow: hidden;
            background:
                radial-gradient(circle at 1px 1px, rgba(255,255,255,.08) 1px, transparent 0);
            background-size: 24px 24px;
        }

        .erd-empty {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #71809c;
        }

        .erd-empty strong {
            color: #fff;
            font-size: 18px;
            margin-bottom: 8px;
        }
    </style>
</head>

<body>

<div class="erd-app">

    <header class="erd-header">
        <div class="erd-title">
            Laravel ERD
        </div>

        <div class="erd-status">
            Registry v{{ $metadata['version'] ?? 1 }}
        </div>
    </header>

    <main class="erd-canvas">

        <div class="erd-empty">
            <strong>Laravel ERD</strong>

            <span>
                No tables have been registered yet.
            </span>
        </div>

    </main>

</div>

<script>
    window.ERD = @json([
        'metadata' => $metadata,
        'migrations' => $migrations,
        'models' => $models,
        'relations' => $relations,
        'history' => $history,
        'layout' => $layout,
    ]);
</script>

</body>
</html>