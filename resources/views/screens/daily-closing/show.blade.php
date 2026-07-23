@extends('layouts.authenticated')

@php
    $statusQuery = $closing['status_query'] ?? 'active';
    $statusBadge = match($statusQuery) {
        'confirmed' => 'success',
        'reopened' => 'warning',
        default => 'blue',
    };
@endphp

@section('content')
<div class="daily-closing">
    <div class="closing-header">
        <div>
            <div class="closing-status-line">
                <span class="closing-status-dot closing-status-dot--{{ $statusBadge }}" aria-hidden="true"></span>
                <span class="closing-status-text closing-status-text--{{ $statusBadge }}">
                    @if($statusQuery === 'confirmed') Cierre Confirmado
                    @elseif($statusQuery === 'reopened') Cierre Reabierto
                    @else Cierre Pendiente
                    @endif
                </span>
</div>
@endsection

            <h2 class="closing-title">Cierre Diario - {{ $closing['agent'] }}</h2>
        </div>
        <div class="closing-actions">
            @if($statusQuery === 'active')
                <button class="btn btn--secondary btn--sm">Descargar Resumen</button>
                <button class="btn btn--secondary btn--sm">Revisar Operaciones</button>
                <button class="btn btn--success btn--sm">Confirmar Cierre</button>
            @elseif($statusQuery === 'confirmed')
                <button class="btn btn--secondary btn--sm">Descargar Resumen</button>
                <button class="btn btn--warning btn--sm">Reabrir Cierre</button>
            @elseif($statusQuery === 'reopened')
                <button class="btn btn--secondary btn--sm">Descargar Resumen</button>
                <button class="btn btn--success btn--sm">Confirmar Cierre</button>
            @endif
        </div>
    </div>

    <div class="closing-context-grid">
        <div class="closing-context-card">
            <span class="closing-context-label">Fecha de Operacion</span>
            <span class="closing-context-value">{{ $closing['date'] }}</span>
        </div>
        <div class="closing-context-card">
            <span class="closing-context-label">Tienda</span>
            <span class="closing-context-value">{{ $closing['store'] }}</span>
        </div>
        <div class="closing-context-card">
            <span class="closing-context-label">Banco Asignado</span>
            <span class="closing-context-value">{{ $closing['bank'] }}</span>
        </div>
        <div class="closing-context-card">
            <span class="closing-context-label">Agente Responsable</span>
            <span class="closing-context-value">{{ $closing['agent'] }}</span>
        </div>
    </div>

    <div class="closing-kpi-grid">
        <x-ui.metric-card
            label="Total Ops"
            :value="$closing['metrics']['total_ops']"
            icon="&#x1F4CB;"
        />
        <x-ui.metric-card
            label="Monto Bruto"
            :value="$closing['metrics']['gross_amount']"
            icon="&#x1F4B0;"
        />
        <x-ui.metric-card
            label="Total Entradas"
            :value="$closing['metrics']['total_entradas']"
            icon="&#x2198;"
            variant="accent-green"
        />
        <x-ui.metric-card
            label="Total Salidas"
            :value="$closing['metrics']['total_salidas']"
            icon="&#x2197;"
            variant="accent-red"
        />
        <x-ui.metric-card
            label="Movimiento Neto"
            :value="$closing['metrics']['net_movement']"
            icon="&#x1F4BC;"
            variant="dark"
        />
    </div>

    @if($statusQuery === 'active' && isset($closing['pending_confirm']) && $closing['pending_confirm'] > 0)
        <x-screen.closing-warning :count="$closing['pending_confirm']" />
    @endif

    @if(isset($closing['annulled_operations']) && count($closing['annulled_operations']) > 0)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Operaciones Anuladas</h3>
        </div>
        <x-ui.data-table
            :headers="[
                ['label' => 'Hora'],
                ['label' => 'Tipo'],
                ['label' => 'Monto', 'align' => 'right'],
                ['label' => 'Motivo'],
            ]"
            :rows="collect($closing['annulled_operations'])->map(function($op) {
                return [
                    ['value' => $op['time'], 'class' => 'data-mono'],
                    ['value' => $op['type']],
                    ['value' => $op['amount'], 'align' => 'right'],
                    ['value' => $op['reason']],
                    'annulled' => true,
                ];
            })->toArray()"
        />
    </div>
    @endif

    <x-screen.closing-detail
        :byType="$closing['by_type'] ?? []"
        :byWorker="$closing['by_worker'] ?? []"
        :statusBreakdown="$closing['status_breakdown'] ?? []"
        :participants="$closing['participants'] ?? []"
    />
</div>
