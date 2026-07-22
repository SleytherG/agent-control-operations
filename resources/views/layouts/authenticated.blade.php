<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Control de Operaciones')</title>
    @vite(['resources/css/app.css'])
    @stack('styles')
</head>
<body>
    <header>
        <nav>
            <span>Control de Operaciones</span>
            <a href="{{ route('sessions.index') }}">Sesiones</a>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Cerrar sesión
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                @csrf
            </form>
        </nav>
    </header>
    <main>
        @yield('content')
    </main>
    @if(isset($expiresAt))
        <meta name="session-expires-at" content="{{ $expiresAt }}">
    @endif
    <div id="session-expiry-modal" class="modal" role="dialog" aria-modal="true" hidden>
        <div class="modal-content">
            <p>Tu sesión está por vencer.</p>
            <button type="button" id="continue-session">Continuar</button>
            <button type="button" id="end-session">Cerrar sesión</button>
        </div>
    </div>
    @vite(['resources/js/identity-access/session-timer.js'])
    @stack('scripts')
</body>
</html>
