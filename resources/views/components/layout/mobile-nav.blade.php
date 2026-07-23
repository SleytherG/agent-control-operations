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
                <a href="/demo/operator/dashboard" class="mobile-nav-link {{ request()->is('demo/operator/dashboard') ? 'mobile-nav-link--active' : '' }}">Dashboard</a>
                <a href="/demo/operator/register" class="mobile-nav-link {{ request()->is('demo/operator/register') ? 'mobile-nav-link--active' : '' }}">Registrar Operación</a>
                <a href="/demo/operator/history" class="mobile-nav-link {{ request()->is('demo/operator/history') ? 'mobile-nav-link--active' : '' }}">Historial</a>
            @else
                <a href="/demo/admin/dashboard" class="mobile-nav-link {{ request()->is('demo/admin/dashboard') ? 'mobile-nav-link--active' : '' }}">Dashboard Admin</a>
                <a href="#" class="mobile-nav-link">Estructura</a>
                <a href="#" class="mobile-nav-link">Usuarios</a>
                <a href="/demo/operator/dashboard" class="mobile-nav-link">Dashboard</a>
                <a href="/demo/operator/register" class="mobile-nav-link">Registrar Operación</a>
                <a href="/demo/operator/history" class="mobile-nav-link">Historial</a>
                <a href="/demo/daily-closing/1" class="mobile-nav-link {{ request()->is('demo/daily-closing/*') ? 'mobile-nav-link--active' : '' }}">Cierre Diario</a>
            @endif
            <a href="#" class="mobile-nav-link">Cerrar Sesión</a>
        </div>
    </div>
</nav>
