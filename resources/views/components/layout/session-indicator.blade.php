@php
    $expiresAt = $sessionExpiresAt ?? now()->addMinutes(5);
    $remainingSeconds = max(0, (int) $expiresAt->diffInSeconds(now()));
    $minutes = (int) floor($remainingSeconds / 60);
    $seconds = $remainingSeconds % 60;
@endphp

<div class="session-indicator" role="timer" aria-label="Tiempo restante de sesión" aria-live="polite"
     data-expires-at="{{ $expiresAt->timestamp }}"
     data-renew-url="{{ route('auth.refresh') }}"
     data-logout-url="{{ route('logout') }}">
    <span class="session-indicator-icon" aria-hidden="true">&#x23F1;</span>
    <span class="session-indicator-time" id="session-timer">{{ sprintf('%02d:%02d', $minutes, $seconds) }}</span>
</div>

<script>
(function () {
    var container = document.querySelector('.session-indicator');
    if (!container) return;

    var timer = document.getElementById('session-timer');
    if (!timer) return;

    var expiresAt = parseInt(container.getAttribute('data-expires-at'), 10);
    var renewUrl = container.getAttribute('data-renew-url');
    var renewToken = '{{ csrf_token() }}';

    function getRemaining() {
        return Math.max(0, expiresAt - Math.floor(Date.now() / 1000));
    }

    function tick() {
        var r = getRemaining();
        var m = Math.floor(r / 60);
        var s = r % 60;
        timer.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');

        if (r <= 300) timer.classList.add('session-indicator--warning');
        if (r <= 60)  timer.classList.add('session-indicator--danger');
        if (r <= 0) {
            window.location.href = '{{ route('login') }}';
            return;
        }
        setTimeout(tick, 1000);
    }

    tick();
})();
</script>
