<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'AgenteFlow' }}</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>&#x1F3E6;</text></svg>">
    @if(isset($sessionExpiresAt))
        <meta name="session-expires-at" content="{{ $sessionExpiresAt->toIso8601String() }}">
    @endif
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    @stack('head')
</head>
<body class="authenticated-layout">
    <div class="app-shell">
        <x-layout.sidebar :role="$role ?? 'operator'" />
        <div class="app-main">
            <x-layout.topbar
                :user="$user ?? null"
                :role="$role ?? 'operator'"
                :session-expires-at="$sessionExpiresAt ?? null"
            />
            <x-layout.mobile-nav :role="$role ?? 'operator'" />
            <main class="app-content">
                @yield('content')
            </main>
        </div>
    </div>

    @if(isset($sessionExpiresAt))
    <x-screen.expiry-modal-content />
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
    @endif

    <div id="global-confirm-modal" class="modal-overlay" style="display:none;" role="dialog" aria-modal="true" aria-label="Confirmación">
        <div class="modal">
            <div class="modal-body" style="text-align:center;">
                <p id="global-confirm-message" style="margin-bottom:var(--space-md);font-size:var(--font-size-body-md);"></p>
                <div style="display:flex;gap:var(--space-sm);justify-content:center;">
                    <button class="btn btn--danger" id="global-confirm-yes">Confirmar</button>
                    <button class="btn btn--secondary" id="global-confirm-no">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
