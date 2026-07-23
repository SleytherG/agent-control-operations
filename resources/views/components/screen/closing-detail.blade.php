@props(['byType' => [], 'byWorker' => [], 'statusBreakdown' => [], 'participants' => []])

<div class="closing-main-grid">
    <div>
        <div class="card closing-breakdown-table">
            <div class="card-header">
                <h3 class="card-title">Desglose por Tipo de Operacion</h3>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th scope="col">Tipo</th>
                            <th scope="col" class="table-th-right">Volumen</th>
                            <th scope="col" class="table-th-right">Entradas</th>
                            <th scope="col" class="table-th-right">Salidas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($byType as $row)
                        <tr>
                            <td>{{ $row['type'] }}</td>
                            <td class="table-td-right">{{ $row['count'] }}</td>
                            <td class="table-td-right table-td-amount-positive">{{ $row['entradas'] }}</td>
                            <td class="table-td-right table-td-amount-negative">{{ $row['salidas'] }}</td>
                        </tr>
                        @endforeach
                        <tr class="closing-totals-row">
                            <td>TOTALES</td>
                            <td class="table-td-right">{{ collect($byType)->sum('count') }}</td>
                            <td class="table-td-right">-</td>
                            <td class="table-td-right">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" style="margin-top:var(--space-lg);">
            <div class="card-header">
                <h3 class="card-title">Actividad por Trabajador</h3>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th scope="col">Trabajador</th>
                            <th scope="col" class="table-th-right">Ops Mencionadas</th>
                            <th scope="col" class="table-th-right">Monto Procesado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($byWorker as $worker)
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:var(--space-sm);">
                                    <span class="admin-worker-avatar">{{ $worker['initials'] }}</span>
                                    {{ $worker['name'] }}
                                </div>
                            </td>
                            <td class="table-td-right">{{ $worker['ops'] }}</td>
                            <td class="table-td-right">{{ $worker['amount'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Estado de Operaciones</h3>
            </div>
            <div class="card-body">
                @foreach($statusBreakdown as $status)
                <div class="closing-status-item {{ isset($status['error']) && $status['error'] ? 'closing-status-item--error' : '' }}">
                    <div style="display:flex;align-items:center;gap:var(--space-sm);">
                        <span class="badge-dot badge-dot--{{ $status['color'] }}" aria-hidden="true"></span>
                        <span style="font-size:var(--font-size-body-md);">{{ $status['label'] }}</span>
                    </div>
                    <span style="font-family:var(--font-family-mono);font-weight:var(--font-weight-medium);">{{ $status['count'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        @if(!empty($participants))
        <div class="card" style="margin-top:var(--space-lg);">
            <div class="card-header">
                <h3 class="card-title">Participantes Activos</h3>
            </div>
            <div class="card-body">
                @foreach($participants as $participant)
                <div class="closing-participant">
                    <div class="closing-participant-avatar">{{ strtoupper(substr($participant['name'], 0, 1) . substr($participant['name'], strpos($participant['name'], ' ') + 1, 1)) }}</div>
                    <div>
                        <div class="closing-participant-name">{{ $participant['name'] }}</div>
                        <div class="closing-participant-role">{{ $participant['role'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
