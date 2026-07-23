@props(['label' => '', 'position' => 'bottom-left'])

<div class="dropdown" data-dropdown>
    <button class="dropdown-trigger" aria-haspopup="true" aria-expanded="false">
        {{ $label }}
        <span aria-hidden="true" class="dropdown-arrow">&#x25BE;</span>
    </button>
    <div class="dropdown-menu" role="menu" hidden>
        {{ $slot }}
    </div>
</div>

<style>
.dropdown { position: relative; display: inline-block; }
.dropdown-trigger {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 8px 12px; font-size: var(--font-size-body-md);
  color: var(--color-on-surface); border: var(--border-thin);
  border-radius: var(--radius-df); background: var(--color-surface-container-lowest);
}
.dropdown-trigger:hover { background: var(--color-surface-container-low); }
.dropdown-arrow { font-size: 12px; }
.dropdown-menu {
  position: absolute; top: 100%; left: 0; z-index: 50;
  background: var(--color-surface-container-lowest); border: var(--border-thin);
  border-radius: var(--radius-lg); box-shadow: var(--shadow-dropdown);
  min-width: 180px; padding: 4px; margin-top: 4px;
}
.dropdown-menu[hidden] { display: none; }
.dropdown-item {
  display: block; padding: 8px 12px; font-size: var(--font-size-body-md);
  color: var(--color-on-surface); border-radius: var(--radius-df); text-decoration: none;
}
.dropdown-item:hover { background: var(--color-surface-container-low); }
</style>
