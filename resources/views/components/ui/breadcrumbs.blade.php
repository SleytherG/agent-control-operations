@props(['items' => []])

<nav class="breadcrumbs" aria-label="Breadcrumb">
    <ol class="breadcrumbs-list">
        @foreach($items as $index => $item)
            <li class="breadcrumbs-item {{ $loop->last ? 'breadcrumbs-item--current' : '' }}">
                @if(!$loop->last)
                    <a href="{{ $item['url'] ?? '#' }}" class="breadcrumbs-link">{{ $item['label'] }}</a>
                @else
                    <span aria-current="page">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

<style>
.breadcrumbs-list {
  display: flex;
  align-items: center;
  gap: var(--space-xs);
  font-size: var(--font-size-body-sm);
  color: var(--color-on-surface-variant);
  flex-wrap: wrap;
}
.breadcrumbs-item::after { content: "/"; margin-left: var(--space-xs); color: var(--color-outline); }
.breadcrumbs-item:last-child::after { content: ""; }
.breadcrumbs-item--current { color: var(--color-on-surface); font-weight: var(--font-weight-medium); }
.breadcrumbs-link { color: var(--color-on-surface-variant); text-decoration: none; }
.breadcrumbs-link:hover { color: var(--color-primary); }
</style>
