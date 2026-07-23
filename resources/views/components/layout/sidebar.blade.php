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
        <a href="/demo/operator/dashboard" class="sidebar-link {{ request()->is('demo/operator/dashboard') ? 'sidebar-link--active' : '' }}">
            <span class="sidebar-icon" aria-hidden="true">&#x25A3;</span>
            <span class="sidebar-label">Dashboard</span>
        </a>
        <a href="/demo/operator/register" class="sidebar-link {{ request()->is('demo/operator/register') ? 'sidebar-link--active' : '' }}">
            <span class="sidebar-icon" aria-hidden="true">&#x2795;</span>
            <span class="sidebar-label">Registrar Operación</span>
        </a>
        <a href="/demo/operator/history" class="sidebar-link {{ request()->is('demo/operator/history') ? 'sidebar-link--active' : '' }}">
            <span class="sidebar-icon" aria-hidden="true">&#x23F0;</span>
            <span class="sidebar-label">Historial</span>
        </a>
    </nav>
    @else
    <nav class="sidebar-nav">
        <span class="sidebar-section">Principal</span>
        <a href="/demo/admin/dashboard" class="sidebar-link {{ request()->is('demo/admin/dashboard') ? 'sidebar-link--active' : '' }}">
            <span class="sidebar-icon" aria-hidden="true">&#x25A3;</span>
            <span class="sidebar-label">Dashboard Admin</span>
        </a>
        <a href="#" class="sidebar-link">
            <span class="sidebar-icon" aria-hidden="true">&#x1F3E2;</span>
            <span class="sidebar-label">Estructura</span>
        </a>
        <a href="#" class="sidebar-link">
            <span class="sidebar-icon" aria-hidden="true">&#x1F465;</span>
            <span class="sidebar-label">Usuarios</span>
        </a>
        <span class="sidebar-section">Operaciones</span>
        <a href="/demo/operator/dashboard" class="sidebar-link">
            <span class="sidebar-icon" aria-hidden="true">&#x25A3;</span>
            <span class="sidebar-label">Dashboard</span>
        </a>
        <a href="/demo/operator/register" class="sidebar-link">
            <span class="sidebar-icon" aria-hidden="true">&#x2795;</span>
            <span class="sidebar-label">Registrar Operación</span>
        </a>
        <a href="/demo/operator/history" class="sidebar-link">
            <span class="sidebar-icon" aria-hidden="true">&#x23F0;</span>
            <span class="sidebar-label">Historial</span>
        </a>
        <a href="/demo/daily-closing/1" class="sidebar-link {{ request()->is('demo/daily-closing/*') ? 'sidebar-link--active' : '' }}">
            <span class="sidebar-icon" aria-hidden="true">&#x1F4C5;</span>
            <span class="sidebar-label">Cierre Diario</span>
        </a>
    </nav>
    @endif

    <div class="sidebar-footer">
        <a href="#" class="sidebar-link">
            <span class="sidebar-icon" aria-hidden="true">&#x1F6AA;</span>
            <span class="sidebar-label">Cerrar Sesión</span>
        </a>
    </div>
</aside>
