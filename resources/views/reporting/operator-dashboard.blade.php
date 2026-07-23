@extends('layouts.authenticated')

@section('title', $title ?? 'Dashboard — AgenteFlow')

@section('head')
    @vite('resources/js/reporting/dashboard-charts.js')
@endsection

@section('content')
    @if($metrics->operation_count === 0)
        <x-ui.empty-state
            icon="&#x1F4CA;"
            title="Sin operaciones en este periodo"
            description="Registre operaciones para ver sus metricas y graficos aqui."
        />
    @else
        <div class="operator-dashboard">
            <div class="page-header">
                <h2 class="admin-title" style="margin-bottom:var(--space-xs);">Buen dia, {{ $user->name ?? 'Operador' }}.</h2>
                <p class="admin-subtitle">Resumen operativo para <strong>{{ $user->store ?? 'Tienda' }}</strong> al corte actual.</p>
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
                        <span style="font-size:var(--font-size-headline-sm);font-weight:var(--font-weight-bold);color:var(--color-on-surface);">{{ $metrics->operation_count ?? '0' }}</span>
                    </div>
                </x-ui.chart-container>
            </div>

            @if(isset($recentOperations) && count($recentOperations) > 0)
            <div class="card operator-recent-table">
                <div class="card-header">
                    <h3 class="card-title">Ultimas Operaciones</h3>
                    <a href="{{ route('operations.index') }}" style="font-size:var(--font-size-label);font-weight:var(--font-weight-bold);color:var(--color-primary);">VER TODAS</a>
                </div>
                <x-ui.data-table
                    :headers="[
                        ['label' => 'Hora'],
                        ['label' => 'Agente'],
                        ['label' => 'Tipo'],
                        ['label' => 'Monto', 'align' => 'right'],
                        ['label' => 'Estado', 'align' => 'center'],
                    ]"
                    :rows="$recentOperations"
                />
            </div>
            @endif
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
            if (typeof Chart !== 'undefined' && document.getElementById('opsByHourChart')) {
                clearInterval(interval);

                var ctx = document.getElementById('opsByHourChart');
                if (ctx) {
                    new Chart(ctx.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: timeEvo.labels || [],
                            datasets: [
                                { label: 'Entradas', data: timeEvo.entradas || [], backgroundColor: '#4edea3', borderRadius: 4, barPercentage: 0.6 },
                                { label: 'Salidas', data: timeEvo.salidas || [], backgroundColor: '#ba1a1a', borderRadius: 4, barPercentage: 0.6 }
                            ]
                        },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { stacked: true, grid: { display: false } }, y: { stacked: true, border: { display: false }, beginAtZero: true } } }
                    });
                }

                var dc = document.getElementById('opsByTypeChart');
                if (dc && typeDist.length > 0) {
                    new Chart(dc.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: typeDist.map(function(d) { return d.type || d.name || ''; }),
                            datasets: [{ data: typeDist.map(function(d) { return d.count || 0; }), backgroundColor: ['#4edea3', '#ba1a1a', '#bec6e0', '#505f76', '#eae7e9'], borderWidth: 0, hoverOffset: 4 }]
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
