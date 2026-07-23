@props(['metrics' => null])

@php
    $count = is_object($metrics) ? ($metrics->operation_count ?? 0) : ($metrics['operation_count'] ?? 0);
    $gross = is_object($metrics) ? ($metrics->gross_amount ?? 'S/ 0.00') : ($metrics['gross_amount'] ?? 'S/ 0.00');
    $cashIn = is_object($metrics) ? ($metrics->cash_in ?? 'S/ 0.00') : ($metrics['cash_in'] ?? 'S/ 0.00');
    $cashOut = is_object($metrics) ? ($metrics->cash_out ?? 'S/ 0.00') : ($metrics['cash_out'] ?? 'S/ 0.00');
    $net = is_object($metrics) ? ($metrics->net_movement ?? 'S/ 0.00') : ($metrics['net_movement'] ?? 'S/ 0.00');
    $cashInOps = is_object($metrics) ? ($metrics->cash_in_ops ?? 0) : ($metrics['cash_in_ops'] ?? 0);
    $cashOutOps = is_object($metrics) ? ($metrics->cash_out_ops ?? 0) : ($metrics['cash_out_ops'] ?? 0);
@endphp

<div class="operator-kpi-grid">
    <x-ui.metric-card
        label="Ops. del Dia"
        :value="$count"
        icon="&#x1F4CB;"
    />
    <x-ui.metric-card
        label="Monto Bruto"
        :value="$gross"
        icon="&#x1F4B0;"
    />
    <x-ui.metric-card
        label="Entradas"
        :value="$cashIn"
        icon="&#x2198;"
        :sub="($cashInOps ? $cashInOps . ' operaciones' : '')"
        variant="accent-green"
    />
    <x-ui.metric-card
        label="Salidas"
        :value="$cashOut"
        icon="&#x2197;"
        :sub="($cashOutOps ? $cashOutOps . ' operaciones' : '')"
        variant="accent-red"
    />
    <x-ui.metric-card
        label="Movimiento Neto"
        :value="$net"
        icon="&#x1F4BC;"
        variant="dark"
    />
</div>
