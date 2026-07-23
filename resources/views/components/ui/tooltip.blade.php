@props(['text' => ''])

<span class="tooltip-wrapper">
    {{ $slot }}
    <span class="tooltip-text" role="tooltip">{{ $text }}</span>
</span>

<style>
.tooltip-wrapper { position: relative; display: inline-flex; }
.tooltip-text {
  position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%);
  padding: 4px 8px; font-size: 11px; font-weight: var(--font-weight-medium);
  color: var(--color-on-primary); background: var(--color-primary);
  border-radius: var(--radius-df); white-space: nowrap; margin-bottom: 4px;
  opacity: 0; pointer-events: none; transition: opacity 0.15s ease;
}
.tooltip-wrapper:hover .tooltip-text,
.tooltip-wrapper:focus-within .tooltip-text { opacity: 1; }
</style>
