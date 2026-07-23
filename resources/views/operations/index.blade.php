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

    <x-screen.operation-filters :agents="$agents" :types="$types" />

    <div class="history-summary-grid">
        <x-ui.metric-card
            label="Total Operaciones"
            :value="$summary['total_ops']"
            icon="&#x1F4CB;"
        />
        <x-ui.metric-card
            label="Monto Bruto"
            :value="$summary['total_amount']"
            icon="&#x1F4B0;"
        />
        <x-ui.metric-card
            label="Total Entradas"
            :value="$summary['total_cash_in']"
            icon="&#x2198;"
            variant="accent-green"
        />
        <x-ui.metric-card
            label="Total Salidas"
            :value="$summary['total_cash_out']"
            icon="&#x2197;"
            variant="accent-red"
        />
        <x-ui.metric-card
            label="Movimiento Neto"
            :value="$summary['net_movement']"
            icon="&#x1F4BC;"
            variant="dark"
        />
    </div>

    <div class="card">
        @if($operations->isEmpty())
            <x-ui.empty-state
                icon="&#x1F4ED;"
                title="Sin operaciones"
                description="No se encontraron operaciones con los filtros seleccionados."
            />
        @else
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
                :rows="$operations->map(function($op) {
                    $isActive = $op->status === 'ACTIVE' || $op->isActive();
                    return [
                        ['value' => $op->effective_at?->format('Y-m-d H:i') ?? '—', 'class' => 'data-mono'],
                        ['value' => $op->bankAgent?->bank?->name ?? '—'],
                        ['value' => $op->bankAgent?->code ?? '—'],
                        ['value' => $op->operationType?->name ?? '—'],
                        ['value' => ($op->currency ?? 'PEN') . ' ' . number_format((float) $op->amount, 2), 'align' => 'right'],
                        ['value' => $op->reference ?: '—', 'class' => 'data-mono'],
                        ['value' => $isActive
                            ? \"<a href='\" . route('operations.show', $op) . \"'><x-ui.badge variant='active'>Activa</x-ui.badge></a>\"
                            : \"<a href='\" . route('operations.show', $op) . \"'><x-ui.badge variant='annulled'>Anulada</x-ui.badge></a>\",
                            'align' => 'center'],
                    ];
                })->toArray()"
            />
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
