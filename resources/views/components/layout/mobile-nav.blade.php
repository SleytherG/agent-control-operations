@props(['role' => 'operator'])

<nav class="mobile-nav" id="mobile-nav" role="navigation" aria-label="Navegación móvil">
    <div class="mobile-nav-overlay" id="mobile-nav-overlay"></div>
    <div class="mobile-nav-panel">
        <div class="mobile-nav-header">
            <span class="mobile-nav-title">Menú</span>
            <button class="mobile-nav-close" id="mobile-nav-close" aria-label="Cerrar menú">&times;</button>
        </div>
        <div class="mobile-nav-links">
            @if($role === 'operator')
                <a href="{{ route('dashboard.operator') }}" class="mobile-nav-link {{ request()->routeIs('dashboard.operator') ? 'mobile-nav-link--active' : '' }}">Dashboard</a>
                <a href="{{ route('operations.create') }}" class="mobile-nav-link {{ request()->routeIs('operations.create') ? 'mobile-nav-link--active' : '' }}">Registrar Operación</a>
                <a href="{{ route('operations.index') }}" class="mobile-nav-link {{ request()->routeIs('operations.index') ? 'mobile-nav-link--active' : '' }}">Historial</a>
                <a href="{{ route('my-agents.index') }}" class="mobile-nav-link {{ request()->routeIs('my-agents.index') ? 'mobile-nav-link--active' : '' }}">Mis Agentes</a>
            @else
                <a href="{{ route('admin.dashboard') }}" class="mobile-nav-link {{ request()->routeIs('admin.dashboard') ? 'mobile-nav-link--active' : '' }}">Dashboard Admin</a>
                <a href="{{ route('admin.stores.index') }}" class="mobile-nav-link {{ request()->routeIs('admin.stores.*') ? 'mobile-nav-link--active' : '' }}">Tiendas</a>
                <a href="{{ route('admin.users.index') }}" class="mobile-nav-link {{ request()->routeIs('admin.users.*') ? 'mobile-nav-link--active' : '' }}">Usuarios</a>
                <a href="{{ route('dashboard.operator') }}" class="mobile-nav-link {{ request()->routeIs('dashboard.operator') ? 'mobile-nav-link--active' : '' }}">Dashboard</a>
                <a href="{{ route('operations.create') }}" class="mobile-nav-link {{ request()->routeIs('operations.create') ? 'mobile-nav-link--active' : '' }}">Registrar Operación</a>
                <a href="{{ route('operations.index') }}" class="mobile-nav-link {{ request()->routeIs('operations.index') ? 'mobile-nav-link--active' : '' }}">Historial</a>
                <a href="{{ route('daily-closures.index') }}" class="mobile-nav-link {{ request()->routeIs('daily-closures.*') ? 'mobile-nav-link--active' : '' }}">Cierre Diario</a>
                <a href="{{ route('admin.banks.index') }}" class="mobile-nav-link {{ request()->routeIs('admin.banks.*') ? 'mobile-nav-link--active' : '' }}">Bancos</a>
                <a href="{{ route('admin.bank-agents.index') }}" class="mobile-nav-link {{ request()->routeIs('admin.bank-agents.*') ? 'mobile-nav-link--active' : '' }}">Agentes</a>
                <a href="{{ route('admin.operation-types.index') }}" class="mobile-nav-link {{ request()->routeIs('admin.operation-types.*') ? 'mobile-nav-link--active' : '' }}">Tipos de Op.</a>
                <a href="{{ route('sessions.index') }}" class="mobile-nav-link {{ request()->routeIs('sessions.index') ? 'mobile-nav-link--active' : '' }}">Sesiones</a>
            @endif
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="mobile-nav-link" style="width:100%;border:none;background:none;cursor:pointer;text-align:left;">
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </div>
</nav>
