@extends('layouts.authenticated')

@section('title', 'Dashboard Administrativo — Control de Operaciones')

@section('content')
    <h1>Dashboard Administrativo</h1>

    @include('reporting.components.filters')

    @if($metrics->operation_count === 0)
        @include('reporting.components.empty-state', ['context' => 'admin'])
    @else
        <div class="dashboard-cards">
            <div class="card">
                <h3>Cantidad de operaciones</h3>
                <p class="metric-value">{{ number_format($metrics->operation_count, 0) }}</p>
            </div>
            <div class="card">
                <h3>Monto bruto operado</h3>
                <p class="metric-value">S/ {{ number_format((float) $metrics->gross_amount, 2) }}</p>
            </div>
            <div class="card">
                <h3>Entradas de efectivo</h3>
                <p class="metric-value">S/ {{ number_format((float) $metrics->cash_in, 2) }}</p>
            </div>
            <div class="card">
                <h3>Salidas de efectivo</h3>
                <p class="metric-value">S/ {{ number_format((float) $metrics->cash_out, 2) }}</p>
            </div>
            <div class="card">
                <h3>Movimiento neto</h3>
                <p class="metric-value">S/ {{ number_format((float) $metrics->net_movement, 2) }}</p>
            </div>
        </div>

        <div class="dashboard-charts">
            <div class="chart-container">
                <h2>Distribución por tipo de operación</h2>
                <canvas id="typeDistributionChart"></canvas>
            </div>
            <div class="chart-container">
                <h2>Evolución temporal</h2>
                <canvas id="timeEvolutionChart"></canvas>
            </div>
        </div>

        @include('reporting.components.operations-table')
    @endif

    @push('scripts')
        @if($metrics->operation_count > 0)
            @vite(['resources/js/reporting/dashboard-charts.js'])
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    initAdminDashboard({!! json_encode($typeDistribution, JSON_UNESCAPED_UNICODE) !!}, {!! json_encode($timeEvolution, JSON_UNESCAPED_UNICODE) !!});
                });
            </script>
        @endif
    @endpush
@endsection
