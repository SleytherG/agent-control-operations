@extends('layouts.authenticated')

@section('title', 'Comparativa de Operadores — Control de Operaciones')

@section('content')
    <h1 class="admin-title" style="margin-bottom:var(--space-xs);">Comparativa de Operadores</h1>
    <p class="admin-subtitle" style="margin-bottom:var(--space-md);">Compare volumen, entradas, salidas y neto por operador con filtros consistentes.</p>

    <div class="filter-panel" style="margin-bottom: var(--space-lg);">
        <form method="GET" action="{{ route('admin.dashboard.operators') }}" class="filter-form filter-form--grid">
            <div class="form-group">
                <label class="form-label" for="period">Periodo</label>
                <select name="period" id="period" class="form-input form-select">
                <option value="day" {{ $period === 'day' ? 'selected' : '' }}>Día</option>
                <option value="week" {{ $period === 'week' ? 'selected' : '' }}>Semana</option>
                <option value="month" {{ $period === 'month' ? 'selected' : '' }}>Mes</option>
                <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>Trimestre</option>
                <option value="semester" {{ $period === 'semester' ? 'selected' : '' }}>Semestre</option>
                <option value="year" {{ $period === 'year' ? 'selected' : '' }}>Año</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="date">Fecha</label>
                <input type="date" name="date" id="date" value="{{ $date }}" class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label" for="operator_ids">Operadores</label>
                <select name="operator_ids[]" id="operator_ids" multiple size="5" class="form-input form-select form-select--multiple">
                    @foreach($allOperators as $op)
                        <option value="{{ $op->id }}" {{ in_array($op->id, $selectedOperatorIds) ? 'selected' : '' }}>
                            {{ $op->username_normalized }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-form-actions">
                <a href="{{ route('admin.dashboard.operators') }}" class="btn btn--secondary btn--sm">Limpiar</a>
                <button type="submit" class="btn btn--primary btn--sm">Aplicar</button>
            </div>
        </form>
    </div>

    @if(empty($operators))
        @include('reporting.components.empty-state', ['context' => 'comparison'])
    @else
        <div class="dashboard-charts">
            <div class="chart-container">
                <h2>Top operadores por monto bruto operado</h2>
                <canvas id="comparisonBarChart"></canvas>
            </div>
        </div>

        <div class="comparison-table-container">
            <h2>Ranking de operadores</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Operador</th>
                        <th>Cantidad</th>
                        <th>Monto bruto operado</th>
                        <th>Entradas</th>
                        <th>Salidas</th>
                        <th>Neto</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rank = ($page - 1) * $perPage + 1; @endphp
                    @foreach($operators as $operator)
                        <tr>
                            <td>{{ $rank++ }}</td>
                            <td>{{ $operator->username_normalized }}</td>
                            <td>{{ number_format($operator->operation_count, 0) }}</td>
                            <td>S/ {{ number_format((float) $operator->gross_amount, 2) }}</td>
                            <td>S/ {{ number_format((float) $operator->cash_in, 2) }}</td>
                            <td>S/ {{ number_format((float) $operator->cash_out, 2) }}</td>
                            <td>S/ {{ number_format((float) (($operator->cash_in ?? 0) - ($operator->cash_out ?? 0)), 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($total > $perPage)
                <nav class="pagination">
                    @for($i = 1; $i <= ceil($total / $perPage); $i++)
                        <a href="{{ route('admin.dashboard.operators', array_merge(request()->except('page'), ['page' => $i])) }}"
                           class="{{ $page == $i ? 'active' : '' }}">{{ $i }}</a>
                    @endfor
                </nav>
            @endif
        </div>
    @endif

    @push('scripts')
        @if(!empty($operators))
            @vite(['resources/js/reporting/dashboard-charts.js'])
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    initOperatorComparison({!! json_encode($operators, JSON_UNESCAPED_UNICODE) !!});
                });
            </script>
        @endif
    @endpush
@endsection
