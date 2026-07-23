@extends('layouts.authenticated')

@section('title', $title ?? 'AgenteFlow - Inicio')

@section('content')
<div class="welcome-page">
    <div class="welcome-card">
        <div class="welcome-icon" aria-hidden="true">&#x1F3E6;</div>
        <h1 class="welcome-title">Bienvenido, {{ $user->name ?? 'Operador' }}</h1>
        <p class="welcome-subtitle">Has iniciado sesión correctamente.</p>
        <p class="welcome-subtitle" style="font-size:var(--font-size-label);color:var(--color-on-surface-variant);">
            Tiempo restante de sesión: <span class="session-indicator-time" id="session-timer" style="font-family:var(--font-family-mono);font-weight:var(--font-weight-medium);"></span>
        </p>

        <div class="welcome-actions">
            <a href="{{ route('dashboard.operator') }}" class="btn btn--primary">Ir al Dashboard</a>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var timer = document.getElementById('session-timer');
    if (!timer) return;

    var indicator = document.querySelector('.session-indicator');
    if (!indicator) {
        timer.textContent = '--:--';
        return;
    }

    var expiresAt = parseInt(indicator.getAttribute('data-expires-at'), 10);
    if (!expiresAt) {
        timer.textContent = '--:--';
        return;
    }

    function tick() {
        var r = Math.max(0, expiresAt - Math.floor(Date.now() / 1000));
        var m = Math.floor(r / 60);
        var s = r % 60;
        timer.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        if (r <= 0) {
            window.location.href = '{{ route('login') }}';
            return;
        }
        setTimeout(tick, 1000);
    }
    tick();
})();
</script>
@endpush
@endsection
