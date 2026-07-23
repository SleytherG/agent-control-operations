@extends('layouts.authenticated')

@section('head')
    @vite('resources/js/reporting/dashboard-charts.js')
@endsection

@section('content')
<div class="operator-dashboard">
    <div class="page-header">
        <h2 class="admin-title" style="margin-bottom:var(--space-xs);">Buen dia, {{ explode(' ', $user['name'])[0] ?? 'Operador' }}.</h2>
        <p class="admin-subtitle">Resumen operativo para <strong>{{ $user['store'] ?? 'Tienda Centro' }}</strong> al corte actual.</p>
    </div>

    <x-screen.operator-metrics :metrics="$metrics" />

    <div class="operator-charts-grid">
        <x-ui.chart-container title="Volumen Operativo por Hora" height="300px">
            <canvas id="opsByHourChart"></canvas>
        </x-ui.chart-container>

        <x-ui.chart-container title="Distribucion por Tipo" height="280px">
            <canvas id="opsByTypeChart"></canvas>
            <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;margin-top:24px;">
                <span style="font-size:var(--font-size-label);color:var(--color-on-surface-variant);">TOTAL</span>
                <span style="font-size:var(--font-size-headline-sm);font-weight:var(--font-weight-bold);color:var(--color-on-surface);">{{ $metrics['operation_count'] ?? '0' }}</span>
            </div>
            <div class="chart-legend">
                @foreach($distribution as $item)
                <div class="chart-legend-item">
                    <span>{{ $item['type'] }}</span>
                    <span class="chart-legend-value">{{ $item['percentage'] }}% ({{ $item['count'] }})</span>
                </div>
                @endforeach
            </div>
        </x-ui.chart-container>
    </div>

    <div class="card operator-recent-table">
        <div class="card-header">
            <h3 class="card-title">Ultimas Operaciones</h3>
            <a href="/demo/operator/history" style="font-size:var(--font-size-label);font-weight:var(--font-weight-bold);color:var(--color-primary);">VER TODAS</a>
        </div>
        <x-ui.data-table
            :headers="[
                ['label' => 'Hora'],
                ['label' => 'Banco / Agente'],
                ['label' => 'Tipo'],
                ['label' => 'Monto', 'align' => 'right'],
                ['label' => 'Estado', 'align' => 'center'],
            ]"
            :rows="collect($recentOperations)->map(function($op) {
                return [
                    ['value' => $op['time'], 'class' => 'data-mono'],
                    ['value' => '<strong>' . $op['bank'] . '</strong> ' . $op['agent']],
                    ['value' => $op['type']],
                    ['value' => $op['amount'], 'align' => 'right'],
                    ['value' => '<x-ui.badge variant=\'active\'>Activo</x-ui.badge>', 'align' => 'center'],
                ];
            })->toArray()"
        />
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var chartModule = import.meta.url.replace('app.js', 'reporting/dashboard-charts.js');
    import('/resources/js/reporting/dashboard-charts.js?' + Date.now()).then(function(mod) {
        mod.initCharts('operator', @json(['evolution' => $evolution, 'distribution' => $distribution]));
    }).catch(function() {
        // Chart.js loaded via vite, init inline
        if (typeof Chart !== 'undefined') {
            var data = @json(['evolution' => $evolution, 'distribution' => $distribution]);
            window._chartData = data;
        }
    });
});
</script>
<script>
(function() {
    var data = @json(['evolution' => $evolution, 'distribution' => $distribution]);
    var attempts = 0;
    var interval = setInterval(function() {
        attempts++;
        if (typeof Chart !== 'undefined' && document.getElementById('opsByHourChart')) {
            clearInterval(interval);
            var ctx = document.getElementById('opsByHourChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.evolution.labels,
                    datasets: [
                        { label: 'Entradas', data: data.evolution.entradas, backgroundColor: '#4edea3', borderRadius: 4, barPercentage: 0.6 },
                        { label: 'Salidas', data: data.evolution.salidas, backgroundColor: '#ba1a1a', borderRadius: 4, barPercentage: 0.6 }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { stacked: true, grid: { display: false } }, y: { stacked: true, border: { display: false }, beginAtZero: true } } }
            });

            var dc = document.getElementById('opsByTypeChart');
            if (dc) {
                new Chart(dc.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: data.distribution.map(function(d) { return d.type; }),
                        datasets: [{ data: data.distribution.map(function(d) { return d.count; }), backgroundColor: ['#4edea3', '#ba1a1a', '#bec6e0'], borderWidth: 0, hoverOffset: 4 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { display: false } } }
                });
            }
        }
        if (attempts > 50) clearInterval(interval);
    }, 100);
})();
</script>
@endpush
