@extends('layouts.authenticated')

@section('title', $title ?? 'Dashboard Admin — AgenteFlow')

@push('head')
    @vite('resources/js/reporting/dashboard-charts.js')
@endpush

@section('content')
    @if($metrics->operation_count === 0)
        <x-ui.empty-state
            icon="&#x1F4CA;"
            title="Sin datos para este periodo"
            description="No hay operaciones registradas con los filtros seleccionados."
        />
    @else
        <div class="admin-dashboard">
            <div class="admin-page-header">
                <div>
                    <h1 class="admin-title">Centro de Control Nacional</h1>
                    <p class="admin-subtitle">Resumen de operaciones financieras diarias, flujo de caja y rendimiento de agentes.</p>
                </div>
                <div class="admin-page-actions">
                    <button class="btn btn--secondary btn--sm" onclick="window.print()">Exportar Reporte</button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn--primary btn--sm">Actualizar Datos</a>
                </div>
            </div>

            <x-screen.admin-filters
                :regions="$regions"
                :agents="$agents"
                :types="$types"
                :period="$period"
                :date="$date ?? now()->format('Y-m-d')"
            />

            <div class="admin-kpi-grid">
                <x-ui.metric-card
                    label="Monto Bruto Operado"
                    :value="$metrics->gross_amount ?? 'S/ 0.00'"
                    icon="&#x1F3E6;"
                />
                <x-ui.metric-card
                    label="Total Entradas"
                    :value="$metrics->cash_in ?? 'S/ 0.00'"
                    icon="&#x2198;"
                    :sub="''"
                    variant="accent-green"
                />
                <x-ui.metric-card
                    label="Total Salidas"
                    :value="$metrics->cash_out ?? 'S/ 0.00'"
                    icon="&#x2197;"
                    :sub="''"
                    variant="accent-red"
                />
                <x-ui.metric-card
                    label="Movimiento Neto"
                    :value="$metrics->net_movement ?? 'S/ 0.00'"
                    icon="&#x21C4;"
                />
            </div>

            <div class="admin-secondary-metrics">
                <div class="admin-secondary-metric">
                    <span class="admin-secondary-value">{{ $metrics->operation_count ?? 0 }}</span>
                    <span class="admin-secondary-label">Total Ops</span>
                </div>
            </div>

            <div class="admin-charts-grid">
                <x-ui.chart-container title="Evolucion de Operaciones" height="300px">
                    <canvas id="evolutionChart"></canvas>
                </x-ui.chart-container>

                <x-ui.chart-container title="Distribucion por Tipo" height="280px">
                    <canvas id="typeDoughnutChart"></canvas>
                </x-ui.chart-container>
            </div>
        </div>
    @endif

    @push('scripts')
    <script>
    (function() {
        var hasData = {{ $metrics->operation_count > 0 ? 'true' : 'false' }};
        if (!hasData) return;

        var typeDist = @json($typeDistribution);
        var timeEvo = @json($timeEvolution);
        var attempts = 0;

        var interval = setInterval(function() {
            attempts++;
            if (typeof Chart !== 'undefined' && document.getElementById('evolutionChart')) {
                clearInterval(interval);

                var evo = document.getElementById('evolutionChart');
                if (evo && timeEvo.labels && timeEvo.labels.length > 0) {
                    new Chart(evo.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: timeEvo.labels,
                            datasets: [{ label: 'Volumen', data: timeEvo.data || [], borderColor: '#0b1c30', backgroundColor: '#d0e1fb66', borderWidth: 2, pointBackgroundColor: '#0b1c30', pointRadius: 3, pointHoverRadius: 5, fill: true, tension: 0.4 }]
                        },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, border: { display: false }, ticks: { maxTicksLimit: 6 } }, x: { grid: { display: false }, border: { display: false } } } }
                    });
                }

                var donut = document.getElementById('typeDoughnutChart');
                if (donut && typeDist && typeDist.length > 0) {
                    new Chart(donut.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: typeDist.map(function(d) { return d.name || d.type || ''; }),
                            datasets: [{ data: typeDist.map(function(d) { return d.count || d.percentage || 0; }), backgroundColor: ['#0b1c30', '#505f76', '#bec6e0', '#eae7e9', '#4edea3'], borderWidth: 0, hoverOffset: 4 }]
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
@endsection
