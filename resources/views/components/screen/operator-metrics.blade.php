@props(['metrics' => []])

<div class="operator-kpi-grid">
    <x-ui.metric-card
        label="Ops. del Dia"
        :value="$metrics['operation_count'] ?? '0'"
        icon="&#x1F4CB;"
        trend="up"
        :trendLabel="$metrics['operation_count_trend'] ?? ''"
    />
    <x-ui.metric-card
        label="Monto Bruto"
        :value="$metrics['gross_amount'] ?? 'S/ 0.00'"
        icon="&#x1F4B0;"
        trend="up"
        :trendLabel="$metrics['gross_amount_trend'] ?? ''"
    />
    <x-ui.metric-card
        label="Entradas"
        :value="$metrics['cash_in'] ?? 'S/ 0.00'"
        icon="&#x2198;"
        :sub="($metrics['cash_in_ops'] ?? 0) . ' operaciones'"
        variant="accent-green"
    />
    <x-ui.metric-card
        label="Salidas"
        :value="$metrics['cash_out'] ?? 'S/ 0.00'"
        icon="&#x2197;"
        :sub="($metrics['cash_out_ops'] ?? 0) . ' operaciones'"
        variant="accent-red"
    />
    <x-ui.metric-card
        label="Movimiento Neto"
        :value="$metrics['net_movement'] ?? 'S/ 0.00'"
        icon="&#x1F4BC;"
        :sub="$metrics['net_label'] ?? ''"
        variant="dark"
    />
</div>
