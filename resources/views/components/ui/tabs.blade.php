@props(['tabs' => [], 'active' => null])

<div class="tabs" role="tablist" aria-label="Pestañas">
    <div class="tabs-nav">
        @foreach($tabs as $key => $label)
            <button
                class="tabs-tab {{ $key === $active ? 'tabs-tab--active' : '' }}"
                role="tab"
                aria-selected="{{ $key === $active ? 'true' : 'false' }}"
                data-tab="{{ $key }}"
                id="tab-{{ $key }}"
            >{{ $label }}</button>
        @endforeach
    </div>
    <div class="tabs-content">
        {{ $slot }}
    </div>
</div>

<style>
.tabs-nav {
  display: flex;
  border-bottom: var(--border-thin);
  gap: 0;
}
.tabs-tab {
  padding: 10px 16px;
  font-size: var(--font-size-body-sm);
  font-weight: var(--font-weight-medium);
  color: var(--color-on-surface-variant);
  border-bottom: 2px solid transparent;
  transition: color 0.15s ease, border-color 0.15s ease;
}
.tabs-tab:hover { color: var(--color-primary); }
.tabs-tab--active { color: var(--color-primary); border-bottom-color: var(--color-primary); font-weight: var(--font-weight-bold); }
.tabs-content { padding: var(--space-md) 0; }
.tabs-panel[hidden] { display: none; }
</style>
