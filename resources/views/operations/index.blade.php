@extends('layouts.authenticated')

@section('title', $title ?? 'Historial de Operaciones — AgenteFlow')

@section('content')
<div class="operator-history">
    <div>
        <h2 class="admin-title" style="margin-bottom:var(--space-xs);">Mis operaciones</h2>
        <p class="admin-subtitle">Historial detallado de transacciones y movimientos financieros.</p>
    </div>

    @if(session('status'))
        <div class="alert alert-success" role="alert" style="margin: var(--space-md) 0;">{{ session('status') }}</div>
    @endif

    @php
        $agentOptions = [];
        foreach ($agents as $agent) {
            $agentOptions[$agent->id] = ($agent->code ?? '') . ' — ' . ($agent->name ?? 'Sin nombre');
        }

        $typeOptions = [];
        foreach ($types as $type) {
            $typeOptions[$type->id] = $type->name;
        }
    @endphp

    <form method="GET" action="{{ route('operations.index') }}" class="filter-bar filter-bar--standalone filter-bar--responsive">
        <x-ui.input
            label="Código"
            name="code"
            value="{{ request('code') }}"
            placeholder="Código de operación"
        />
        <x-ui.input
            label="Cliente"
            name="customer_name"
            value="{{ request('customer_name') }}"
            placeholder="Nombre del cliente"
        />
        <x-ui.input
            label="Monto"
            name="amount"
            type="number"
            step="0.01"
            value="{{ request('amount') }}"
            placeholder="Monto exacto"
        />
        <x-ui.select
            label="Agente"
            name="agent_id"
            :options="$agentOptions"
            :selected="request('agent_id')"
            placeholder="Todos los agentes"
        />
        <x-ui.select
            label="Tipo"
            name="operation_type_id"
            :options="$typeOptions"
            :selected="request('operation_type_id')"
            placeholder="Todos los tipos"
        />
        <x-ui.select
            label="Estado"
            name="status"
            :options="['ACTIVE' => 'Activas', 'ANNULLED' => 'Anuladas']"
            :selected="request('status')"
            placeholder="Todos los estados"
        />
        <x-ui.input
            label="Desde"
            name="date_from"
            type="date"
            value="{{ request('date_from') }}"
        />
        <x-ui.input
            label="Hasta"
            name="date_to"
            type="date"
            value="{{ request('date_to') }}"
        />
        <div class="filter-bar-actions" style="justify-content: flex-end;">
            <a href="{{ route('operations.index') }}" class="btn btn--secondary">Limpiar</a>
            <x-ui.button variant="secondary" type="submit">Filtrar</x-ui.button>
        </div>
    </form>

    <div class="history-summary-grid">
        <x-ui.metric-card label="Total Operaciones" :value="$summary['total_ops']" icon="&#x1F4CB;" />
        <x-ui.metric-card label="Monto Bruto" :value="$summary['total_amount']" icon="&#x1F4B0;" />
        <x-ui.metric-card label="Total Entradas" :value="$summary['total_cash_in']" icon="&#x2198;" variant="accent-green" />
        <x-ui.metric-card label="Total Salidas" :value="$summary['total_cash_out']" icon="&#x2197;" variant="accent-red" />
        <x-ui.metric-card label="Movimiento Neto" :value="$summary['net_movement']" icon="&#x1F4BC;" variant="dark" />
    </div>

    <div class="card">
        @if($operations->isEmpty())
            <x-ui.empty-state icon="&#x1F4ED;" title="Sin operaciones" description="No se encontraron operaciones con los filtros seleccionados." />
        @else
            <div class="table-responsive"><table class="data-table">
                <thead><tr>
                    <th class="table-th-center">Código</th>
                    <th>Fecha/Hora</th>
                    <th>Agente</th>
                    <th>Tipo</th>
                    <th>Cliente</th>
                    <th class="table-th-right">Monto</th>
                    <th class="table-th-center">Estado</th>
                </tr></thead>
                <tbody>@foreach($operations as $op)<tr>
                    <td class="data-mono table-td-center">{{ $op->internal_code ?? '—' }}</td>
                    <td class="data-mono">{{ $op->effective_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td>{{ $op->agent->code ?? '—' }} — {{ $op->agent->name ?? '—' }}</td>
                    <td>{{ $op->operationType?->name ?? '—' }}</td>
                    <td>{{ $op->customer_name ?? '—' }}</td>
                    <td class="table-td-right">{{ $op->currency ?? 'PEN' }} {{ number_format((float) $op->amount, 2) }}</td>
                    <td class="table-td-center">
                        <a href="{{ route('operations.show', $op) }}">
                            <x-ui.badge :variant="$op->isActive() ? 'active' : 'annulled'">{{ $op->isActive() ? 'Activa' : 'Anulada' }}</x-ui.badge>
                        </a>
                    </td>
                </tr>@endforeach</tbody>
            </table></div>
            <x-ui.pagination
                :currentPage="$operations->currentPage()"
                :lastPage="$operations->lastPage()"
                :total="$operations->total()"
                :from="$operations->firstItem() ?? 0"
                :to="$operations->lastItem() ?? 0"
            />
        @endif
    </div>
</div>
@endsection
