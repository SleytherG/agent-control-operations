@extends('layouts.authenticated')

@section('title', 'Detalle de Operación — Control de Operaciones')

@section('content')
<div class="operation-detail">
    <div style="margin-bottom:var(--space-lg);">
        <h2 class="admin-title" style="margin-bottom:var(--space-xs);">Detalle de Operación #{{ $operation->id }}</h2>
        <p class="admin-subtitle">Información completa de la transacción registrada.</p>
    </div>

    @if(session('status'))
        <div class="alert alert-success" role="alert" style="margin-bottom:var(--space-md);">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" role="alert" style="margin-bottom:var(--space-md);">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @php
        $statusBadge = $operation->status === 'ACTIVE'
            ? '<span class="badge badge--active">Activa</span>'
            : '<span class="badge badge--annulled">Anulada</span>';

        $agentName = ($operation->agent->code ?? '—') . ' — ' . ($operation->agent->name ?? '—') . ' (' . ($operation->agent->city ?? '—') . ')';
        $typeName = $operation->operationType?->name ?? '—';
        $amount = ($operation->currency ?? 'PEN') . ' ' . number_format((float) $operation->amount, 2);
        $cashDelta = number_format((float) ($operation->cash_delta ?? 0), 2);
        $digitalDelta = number_format((float) ($operation->digital_delta ?? 0), 2);
        $effectiveAt = $operation->effective_at?->format('Y-m-d H:i') ?? '—';
        $recordedAt = $operation->recorded_at?->format('Y-m-d H:i') ?? '—';
        $registeredBy = $operation->user?->username_normalized ?? '—';
        $notes = $operation->notes ?? $operation->observation ?? '—';
        $idempotencyKey = '<code>' . $operation->idempotency_key . '</code>';

        $rows = [
            [['value' => 'ID', 'class' => ''], ['value' => '#' . $operation->id, 'class' => 'data-mono']],
            [['value' => 'Código', 'class' => ''], ['value' => $operation->internal_code ?? '—', 'class' => 'data-mono']],
            [['value' => 'Estado', 'class' => ''], ['value' => $statusBadge, 'class' => '']],
            [['value' => 'Agente', 'class' => ''], ['value' => $agentName, 'class' => '']],
            [['value' => 'Tipo', 'class' => ''], ['value' => $typeName, 'class' => '']],
            [['value' => 'Cliente', 'class' => ''], ['value' => $operation->customer_name ?? '—', 'class' => '']],
            [['value' => 'Monto', 'class' => ''], ['value' => $amount, 'class' => '', 'align' => 'right']],
            [['value' => 'Efectivo Δ', 'class' => ''], ['value' => $cashDelta, 'class' => '', 'align' => 'right']],
            [['value' => 'Digital Δ', 'class' => ''], ['value' => $digitalDelta, 'class' => '', 'align' => 'right']],
            [['value' => 'Fecha Efectiva', 'class' => ''], ['value' => $effectiveAt, 'class' => 'data-mono']],
            [['value' => 'Fecha de Registro', 'class' => ''], ['value' => $recordedAt, 'class' => 'data-mono']],
            [['value' => 'Registrado por', 'class' => ''], ['value' => $registeredBy, 'class' => '']],
            [['value' => 'Notas', 'class' => ''], ['value' => $notes, 'class' => '']],
            [['value' => 'Clave de Idempotencia', 'class' => ''], ['value' => $idempotencyKey, 'class' => 'data-mono']],
        ];

        $annulRows = [];
        if ($operation->isAnnulled()) {
            $annulledBy = $operation->voidedBy?->username_normalized ?? $operation->annulledBy?->username_normalized ?? '—';
            $annulledAt = ($operation->voided_at ?? $operation->annulled_at)?->format('Y-m-d H:i') ?? '—';
            $annullReason = $operation->void_reason ?? $operation->annulment_reason ?? '—';
            $annulRows = [
                [['value' => 'Anulado por', 'class' => ''], ['value' => $annulledBy, 'class' => '']],
                [['value' => 'Fecha de Anulación', 'class' => ''], ['value' => $annulledAt, 'class' => 'data-mono']],
                [['value' => 'Motivo de Anulación', 'class' => ''], ['value' => $annullReason, 'class' => '']],
            ];
        }
    @endphp

    <div class="card" style="margin-bottom:var(--space-lg);">
        <x-ui.data-table :headers="[['label' => 'Campo'], ['label' => 'Valor']]" :rows="$rows" />
    </div>

    @if($operation->isAnnulled())
    <div class="card" style="margin-bottom:var(--space-lg);">
        <div class="card-header"><h3 class="card-title">Información de Anulación</h3></div>
        <x-ui.data-table :headers="[['label' => 'Campo'], ['label' => 'Valor']]" :rows="$annulRows" />
    </div>
    @endif

    <div style="display:flex;gap:var(--space-sm);align-items:center;">
        <a href="{{ route('operations.index') }}" class="btn btn--secondary btn--sm">Volver al Historial</a>

        @if($operation->isActive())
            @can('annul', $operation)
                <button type="button" class="btn btn--warning btn--sm" onclick="if(confirm('¿Anular esta operación?')) { document.getElementById('annul-form-{{ $operation->id }}').submit(); }">
                    Anular Operación
                </button>
                <form id="annul-form-{{ $operation->id }}" action="{{ route('operations.annul', $operation) }}" method="POST" style="display:none;">
                    @csrf
                    <input type="hidden" name="reason" value="Anulación desde vista de detalle">
                </form>
            @endcan
        @endif
    </div>

    @if($operation->isActive())
        @can('annul', $operation)
            <div class="card" style="margin-top:var(--space-lg);">
                <div class="card-header"><h3 class="card-title">Anular Operación</h3></div>
                <form method="POST" action="{{ route('operations.annul', $operation) }}" style="padding:var(--space-md);">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="reason">Motivo de Anulación</label>
                        <textarea name="reason" id="reason" class="form-input" rows="3" required maxlength="500" placeholder="Explique el motivo de la anulación"></textarea>
                    </div>
                    <div style="margin-top:var(--space-md);">
                        <x-ui.button variant="danger" type="submit">Confirmar Anulación</x-ui.button>
                    </div>
                </form>
            </div>
        @endcan
    @endif
</div>
@endsection
