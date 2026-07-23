@extends('layouts.authenticated')

@section('head')
    @vite('resources/js/reporting/dashboard-charts.js')
@endsection

@section('content')
<div class="admin-dashboard">
    <div class="admin-page-header">
        <div>
            <h1 class="admin-title">Centro de Control Nacional</h1>
            <p class="admin-subtitle">Resumen de operaciones financieras diarias, flujo de caja y rendimiento de agentes.</p>
        </div>
        <div class="admin-page-actions">
            <button class="btn btn--secondary btn--sm">Exportar Reporte</button>
            <button class="btn btn--primary btn--sm">Actualizar Datos</button>
        </div>
    </div>

    <x-screen.admin-filters
        :banks="['BCP', 'Interbank', 'BBVA', 'Scotiabank']"
        :stores="['Plaza Central', 'Mall Sur', 'Av. Arequipa', 'Trujillo Norte', 'San Isidro']"
        :statuses="['Completado', 'Pendiente', 'Anulado']"
    />

    <div class="admin-kpi-grid">
        <x-ui.metric-card label="Monto Bruto Operado" :value="$metrics['gross_amount']" icon="&#x1F3E6;" :trend="'up'" :trendLabel="$metrics['gross_amount_trend']" />
        <x-ui.metric-card label="Total Entradas" :value="$metrics['cash_in']" icon="&#x2198;" :sub="$metrics['cash_in_ops']" variant="accent-green" />
        <x-ui.metric-card label="Total Salidas" :value="$metrics['cash_out']" icon="&#x2197;" :sub="$metrics['cash_out_ops']" variant="accent-red" />
        <x-ui.metric-card label="Movimiento Neto" :value="$metrics['net_movement']" icon="&#x21C4;" />
    </div>

    <div class="admin-secondary-metrics">
        <div class="admin-secondary-metric">
            <span class="admin-secondary-value">{{ $metrics['total_ops'] }}</span>
            <span class="admin-secondary-label">Total Ops</span>
        </div>
        <div class="admin-secondary-metric">
            <span class="admin-secondary-value">{{ $metrics['active_workers'] }}</span>
            <span class="admin-secondary-label">Trabajadores Activos</span>
        </div>
        <div class="admin-secondary-metric">
            <span class="admin-secondary-value">{{ $metrics['active_stores'] }}</span>
            <span class="admin-secondary-label">Tiendas Activas</span>
        </div>
        <div class="admin-secondary-metric">
            <span class="admin-secondary-value">{{ $metrics['active_agents'] }}</span>
            <span class="admin-secondary-label">Agentes Activos</span>
        </div>
        <div class="admin-secondary-metric">
            <span class="admin-secondary-value" style="color:var(--color-error);">{{ $metrics['voided_ops'] }}</span>
            <span class="admin-secondary-label">Ops Anuladas</span>
        </div>
    </div>

    <div class="admin-charts-grid">
        <x-ui.chart-container title="Evolucion de Operaciones" height="300px">
            <canvas id="evolutionChart"></canvas>
        </x-ui.chart-container>

        <x-ui.chart-container title="Distribucion por Socio" height="280px">
            <canvas id="bankDoughnutChart"></canvas>
            <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;margin-top:24px;">
                <span style="font-size:var(--font-size-headline-md);font-weight:var(--font-weight-bold);color:var(--color-on-surface);">4</span>
                <span style="font-size:var(--font-size-label);color:var(--color-on-surface-variant);">Socios</span>
            </div>
        </x-ui.chart-container>
    </div>

    <div class="admin-bottom-grid">
        <x-ui.chart-container title="Comparacion de Flujo" height="280px">
            <canvas id="flowBarChart"></canvas>
        </x-ui.chart-container>

        <x-screen.operator-comparison :stores="$topStores" :workers="$topWorkers" />
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var data = @json(['evolution' => $evolution, 'bankDistribution' => $bankDistribution, 'flowByRegion' => $flowByRegion]);
    var attempts = 0;
    var interval = setInterval(function() {
        attempts++;
        if (typeof Chart !== 'undefined' && document.getElementById('evolutionChart')) {
            clearInterval(interval);
            var evo = document.getElementById('evolutionChart');
            if (evo) {
                new Chart(evo.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: data.evolution.labels,
                        datasets: [{ label: 'Volumen', data: data.evolution.data, borderColor: '#0b1c30', backgroundColor: '#d0e1fb66', borderWidth: 2, pointBackgroundColor: '#0b1c30', pointRadius: 3, pointHoverRadius: 5, fill: true, tension: 0.4 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, border: { display: false }, ticks: { maxTicksLimit: 6 } }, x: { grid: { display: false }, border: { display: false } } } }
                });
            }
            var donut = document.getElementById('bankDoughnutChart');
            if (donut) {
                new Chart(donut.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: data.bankDistribution.map(function(d) { return d.bank; }),
                        datasets: [{ data: data.bankDistribution.map(function(d) { return d.percentage; }), backgroundColor: ['#0b1c30', '#505f76', '#bec6e0', '#eae7e9'], borderWidth: 0, hoverOffset: 4 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { display: false } } }
                });
            }
            var barC = document.getElementById('flowBarChart');
            if (barC) {
                new Chart(barC.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: data.flowByRegion.labels,
                        datasets: [
                            { label: 'Entradas', data: data.flowByRegion.cash_in, backgroundColor: '#4edea3', borderRadius: 2, barPercentage: 0.6 },
                            { label: 'Salidas', data: data.flowByRegion.cash_out, backgroundColor: '#eae7e9', borderRadius: 2, barPercentage: 0.6 }
                        ]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 8 } } }, scales: { y: { beginAtZero: true, border: { display: false } }, x: { grid: { display: false }, border: { display: false } } } }
                });
            }
        }
        if (attempts > 50) clearInterval(interval);
    }, 100);
})();
</script>
@endpush
