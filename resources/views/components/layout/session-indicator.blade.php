<div class="session-indicator" role="timer" aria-label="Tiempo restante de sesión" aria-live="polite">
    <span class="session-indicator-icon" aria-hidden="true">&#x23F1;</span>
    <span class="session-indicator-time" id="session-timer">59:59</span>
</div>

<script>
    (function() {
        var timer = document.getElementById('session-timer');
        if (!timer) return;
        var total = 3599;
        function tick() {
            var m = Math.floor(total / 60);
            var s = total % 60;
            timer.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            if (total <= 300) timer.classList.add('session-indicator--warning');
            if (total <= 60) timer.classList.add('session-indicator--danger');
            if (total <= 0) return;
            total--;
            setTimeout(tick, 1000);
        }
        tick();
    })();
</script>
