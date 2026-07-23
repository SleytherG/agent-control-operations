@props(['stores' => [], 'workers' => []])

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Top 5 Tiendas</h3>
        <span style="font-size:var(--font-size-label);font-weight:var(--font-weight-bold);color:var(--color-on-surface-variant);">Por Volumen</span>
    </div>
    <div class="card-body">
        <div class="admin-ranking-list">
            @foreach($stores as $store)
            <div class="admin-ranking-item">
                <span class="admin-ranking-rank">{{ $store['rank'] }}</span>
                <div style="flex:1;">
                    <span class="admin-ranking-name">{{ $store['name'] }}</span>
                    <div class="admin-ranking-code">{{ $store['code'] }}</div>
                </div>
                <span class="admin-ranking-value">{{ $store['volume'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card" style="margin-top:var(--space-lg);">
    <div class="card-header">
        <h3 class="card-title">Top Cajeros</h3>
        <span style="font-size:var(--font-size-label);font-weight:var(--font-weight-bold);color:var(--color-on-surface-variant);">Por Cantidad Ops</span>
    </div>
    <div class="card-body">
        <div class="admin-ranking-list">
            @foreach($workers as $worker)
            <div class="admin-ranking-item">
                <div class="admin-worker-avatar">{{ $worker['initials'] }}</div>
                <div style="flex:1;margin-left:var(--space-sm);">
                    <span class="admin-ranking-name">{{ $worker['name'] }}</span>
                    <div class="admin-ranking-code">ID: {{ $worker['id'] }}</div>
                </div>
                <span class="admin-ranking-value">{{ $worker['ops'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>
