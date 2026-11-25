<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>VYBETIFY — Your music. Your mood. Your vibe.</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script>
        window.VYBETIFY = {
            apiBase: @json(url('/api')),
            baseUrl: @json(url('/')),
            csrf: @json(csrf_token())
        };
    </script>
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
    <div class="app-shell">
        @include('partials.sidebar')
        <main class="main-panel">
            <div id="page-content" class="page-content">
                @yield('content')
            </div>
        </main>
    </div>

    @include('partials.player')
    <div id="toast-region" class="toast-region" aria-live="polite"></div>
</body>
</html>
