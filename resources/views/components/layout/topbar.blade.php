@props(['user' => null, 'role' => 'operator'])

<header class="topbar" role="banner">
    <button class="topbar-hamburger" id="hamburger-btn" aria-label="Abrir menú" aria-expanded="false">
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
    </button>

    <div class="topbar-left">
        <h2 class="topbar-title">Financial Operations</h2>
    </div>

    <div class="topbar-right">
        <div class="topbar-context">
            <span class="topbar-context-label">{{ $user->store ?? 'Tienda Centro' }}</span>
        </div>

        <button class="topbar-icon-btn" aria-label="Temporizador de sesión" title="Sesión activa">
            <span aria-hidden="true">&#x23F1;</span>
        </button>

        <button class="topbar-icon-btn topbar-notification" aria-label="Notificaciones">
            <span aria-hidden="true">&#x1F514;</span>
            <span class="notification-dot" aria-hidden="true"></span>
        </button>

        <div class="topbar-user">
            <div class="topbar-user-info">
                <span class="topbar-user-name">{{ $user->name ?? 'Carlos López' }}</span>
                <span class="topbar-user-role">
                    @if($role === 'admin')
                        <span class="badge badge--info">Administrador</span>
                    @else
                        <span class="badge badge--blue">Operador</span>
                    @endif
                </span>
            </div>
        </div>
    </div>
</header>
