@extends('layouts.authenticated')

@section('content')
<div class="operator-history">
    <div>
        <h2 class="admin-title" style="margin-bottom:var(--space-xs);">Mis operaciones</h2>
        <p class="admin-subtitle">Historial detallado de transacciones y movimientos financieros.</p>
    </div>

    <x-screen.operation-filters />

    <div class="history-summary-grid">
        <x-ui.metric-card label="Total Operaciones" :value="$summary['total_ops']" icon="&#x1F4CB;" />
        <x-ui.metric-card label="Monto Bruto" :value="$summary['total_amount']" icon="&#x1F4B0;" />
        <x-ui.metric-card label="Total Entradas" :value="$summary['total_cash_in']" icon="&#x2198;" variant="accent-green" />
        <x-ui.metric-card label="Total Salidas" :value="$summary['total_cash_out']" icon="&#x2197;" variant="accent-red" />
        <x-ui.metric-card label="Movimiento Neto" :value="$summary['net_movement']" icon="&#x1F4BC;" variant="dark" />
    </div>

    <div class="card">
        <x-ui.data-table
            :headers="[
                ['label' => 'Fecha/Hora'],
                ['label' => 'Banco'],
                ['label' => 'Agente'],
                ['label' => 'Tipo'],
                ['label' => 'Monto', 'align' => 'right'],
                ['label' => 'Referencia'],
                ['label' => 'Estado', 'align' => 'center'],
            ]"
            :rows="collect($operations)->map(function($op) {
                $badgeVariant = $op['status'] === 'active' ? 'active' : 'annulled';
                return [
                    ['value' => $op['date'], 'class' => 'data-mono'],
                    ['value' => $op['bank']],
                    ['value' => $op['agent']],
                    ['value' => $op['type']],
                    ['value' => $op['amount'], 'align' => 'right'],
                    ['value' => $op['reference'], 'class' => 'data-mono'],
                    ['value' => '<x-ui.badge variant=\\\'' . $badgeVariant . '\\\'>' . ucfirst($op['status']) . '</x-ui.badge>', 'align' => 'center'],
                    'annulled' => $op['annulled'],
                ];
            })->toArray()"
        />
        <x-ui.pagination
            :currentPage="1"
            :lastPage="3"
            :total="count($operations)"
            :from="1"
            :to="min(count($operations), 10)"
        />
    </div>
</div>
@endsection
