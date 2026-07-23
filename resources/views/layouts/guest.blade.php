<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'AgenteFlow' }}</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>&#x1F3E6;</text></svg>">
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    @stack('head')
</head>
<body class="guest-layout">
    <main class="guest-content">
        {{ $slot }}
    </main>
    @stack('scripts')
</body>
</html>
