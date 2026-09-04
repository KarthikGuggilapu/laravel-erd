@extends('erd::layouts.app')

@section('content')
    <div class="erd-app">
        @include('erd::components.navbar')

        <main class="erd-main" id="erdCanvas">
            @include('erd::components.canvas-controls')
            @include('erd::components.table-selector')
            @include('erd::components.canvas')
            @include('erd::components.stats')
        </main>

        @include('erd::components.toast')
    </div>
@endsection
