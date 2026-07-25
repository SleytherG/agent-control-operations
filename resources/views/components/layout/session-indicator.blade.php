@props(['sessionExpiresAt'])

@php
    $expiresAt = $sessionExpiresAt;
    $remainingSeconds = max(0, $expiresAt->timestamp - now()->timestamp);
    $minutes = (int) floor($remainingSeconds / 60);
    $seconds = $remainingSeconds % 60;
@endphp

<div class="session-indicator" role="timer" aria-label="Tiempo restante de sesión" aria-live="polite"
     data-expires-at="{{ $expiresAt->timestamp }}">
    <span class="session-indicator-icon" aria-hidden="true">&#x23F1;</span>
    <span class="session-indicator-time" id="session-timer">{{ sprintf('%02d:%02d', $minutes, $seconds) }}</span>
</div>
