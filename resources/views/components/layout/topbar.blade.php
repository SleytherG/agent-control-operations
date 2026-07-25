@props(['user' => null, 'role' => 'operator', 'sessionExpiresAt' => null])

<header class="topbar" role="banner">
    <button class="topbar-hamburger" id="hamburger-btn" aria-label="Abrir menú" aria-expanded="false">
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
    </button>

    <div class="topbar-left">
        <h2 class="topbar-title">Control de operaciones</h2>
    </div>

    <div class="topbar-right">
        @if($sessionExpiresAt)
            <x-layout.session-indicator :session-expires-at="$sessionExpiresAt" />
        @endif

        <div class="topbar-user">
            @if($role === 'admin')
                <span class="badge badge--info">Administrador</span>
            @else
                <span class="badge badge--blue">Operador</span>
            @endif
            <span class="topbar-user-name">{{ $user?->username_normalized ?? $user?->email_normalized ?? 'Usuario' }}</span>
        </div>
    </div>
</header>
