@props(['role' => 'operator'])

<aside class="sidebar" id="sidebar" role="navigation" aria-label="Navegación principal">
    <div class="sidebar-brand">
        <div class="sidebar-logo" aria-hidden="true">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                <rect width="32" height="32" rx="8" fill="currentColor" fill-opacity="0.15"/>
                <path d="M16 6L6 12v8l10 6 10-6v-8L16 6z" stroke="currentColor" stroke-width="1.5" fill="none"/>
            </svg>
        </div>
        <div class="sidebar-brand-text">
            <span class="sidebar-app-name">AgenteFlow</span>
            <span class="sidebar-session">Sesión activa</span>
        </div>
    </div>

    @if($role === 'operator')
    <nav class="sidebar-nav">
        <a href="{{ route('dashboard.operator') }}" class="sidebar-link {{ request()->routeIs('dashboard.operator') ? 'sidebar-link--active' : '' }}">
            <span class="sidebar-icon" aria-hidden="true">&#x25A3;</span>
            <span class="sidebar-label">Dashboard</span>
        </a>
        <a href="{{ route('operations.create') }}" class="sidebar-link {{ request()->routeIs('operations.create') ? 'sidebar-link--active' : '' }}">
            <span class="sidebar-icon" aria-hidden="true">&#x2795;</span>
            <span class="sidebar-label">Registrar Operación</span>
        </a>
        <a href="{{ route('operations.index') }}" class="sidebar-link {{ request()->routeIs('operations.index') ? 'sidebar-link--active' : '' }}">
            <span class="sidebar-icon" aria-hidden="true">&#x23F0;</span>
            <span class="sidebar-label">Historial</span>
        </a>
        <a href="{{ route('my-agents.index') }}" class="sidebar-link {{ request()->routeIs('my-agents.index') ? 'sidebar-link--active' : '' }}">
            <span class="sidebar-icon" aria-hidden="true">&#x1F3E6;</span>
            <span class="sidebar-label">Mis Agentes</span>
        </a>
    </nav>
    @else
    <nav class="sidebar-nav">
        <span class="sidebar-section">Principal</span>
        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'sidebar-link--active' : '' }}">
            <span class="sidebar-icon" aria-hidden="true">&#x25A3;</span>
            <span class="sidebar-label">Dashboard Admin</span>
        </a>
        <a href="{{ route('admin.stores.index') }}" class="sidebar-link {{ request()->routeIs('admin.stores.*') ? 'sidebar-link--active' : '' }}">
            <span class="sidebar-icon" aria-hidden="true">&#x1F3E2;</span>
            <span class="sidebar-label">Tiendas</span>
        </a>
        <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'sidebar-link--active' : '' }}">
            <span class="sidebar-icon" aria-hidden="true">&#x1F465;</span>
            <span class="sidebar-label">Usuarios</span>
        </a>
        <span class="sidebar-section">Operaciones</span>
        <a href="{{ route('dashboard.operator') }}" class="sidebar-link {{ request()->routeIs('dashboard.operator') ? 'sidebar-link--active' : '' }}">
            <span class="sidebar-icon" aria-hidden="true">&#x25A3;</span>
            <span class="sidebar-label">Dashboard</span>
        </a>
        <a href="{{ route('operations.create') }}" class="sidebar-link {{ request()->routeIs('operations.create') ? 'sidebar-link--active' : '' }}">
            <span class="sidebar-icon" aria-hidden="true">&#x2795;</span>
            <span class="sidebar-label">Registrar Operación</span>
        </a>
        <a href="{{ route('operations.index') }}" class="sidebar-link {{ request()->routeIs('operations.index') ? 'sidebar-link--active' : '' }}">
            <span class="sidebar-icon" aria-hidden="true">&#x23F0;</span>
            <span class="sidebar-label">Historial</span>
        </a>
        <a href="{{ route('daily-closures.index') }}" class="sidebar-link {{ request()->routeIs('daily-closures.*') ? 'sidebar-link--active' : '' }}">
            <span class="sidebar-icon" aria-hidden="true">&#x1F4C5;</span>
            <span class="sidebar-label">Cierre Diario</span>
        </a>
        <span class="sidebar-section">Administración</span>
        <a href="{{ route('admin.banks.index') }}" class="sidebar-link {{ request()->routeIs('admin.banks.*') ? 'sidebar-link--active' : '' }}">
            <span class="sidebar-icon" aria-hidden="true">&#x1F3E6;</span>
            <span class="sidebar-label">Bancos</span>
        </a>
        <a href="{{ route('admin.bank-agents.index') }}" class="sidebar-link {{ request()->routeIs('admin.bank-agents.*') ? 'sidebar-link--active' : '' }}">
            <span class="sidebar-icon" aria-hidden="true">&#x1F3E6;</span>
            <span class="sidebar-label">Agentes</span>
        </a>
        <a href="{{ route('admin.operation-types.index') }}" class="sidebar-link {{ request()->routeIs('admin.operation-types.*') ? 'sidebar-link--active' : '' }}">
            <span class="sidebar-icon" aria-hidden="true">&#x1F4CB;</span>
            <span class="sidebar-label">Tipos de Op.</span>
        </a>
        <a href="{{ route('sessions.index') }}" class="sidebar-link {{ request()->routeIs('sessions.index') ? 'sidebar-link--active' : '' }}">
            <span class="sidebar-icon" aria-hidden="true">&#x1F511;</span>
            <span class="sidebar-label">Sesiones</span>
        </a>
    </nav>
    @endif

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}" style="display:inline;">
            @csrf
            <button type="submit" class="sidebar-link" style="width:100%;border:none;background:none;cursor:pointer;text-align:left;">
                <span class="sidebar-icon" aria-hidden="true">&#x1F6AA;</span>
                <span class="sidebar-label">Cerrar Sesión</span>
            </button>
        </form>
    </div>
</aside>
