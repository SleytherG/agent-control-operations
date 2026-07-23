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

    <div class="card" style="margin-bottom:var(--space-lg);">
        <x-ui.data-table
            :headers="[
                ['label' => 'Campo'],
                ['label' => 'Valor'],
            ]"
            :rows="[
                [['value' => 'ID'], ['value' => '#' . $operation->id, 'class' => 'data-mono']],
                [['value' => 'Estado'], ['value' => $operation->status === 'ACTIVE'
                    ? \"<x-ui.badge variant='active'>Activa</x-ui.badge>\"
                    : \"<x-ui.badge variant='annulled'>Anulada</x-ui.badge>\"]],
                [['value' => 'Agente'], ['value' => ($operation->bankAgent?->bank?->name ?? '—') . ' — ' . ($operation->bankAgent?->store?->name ?? '—') . ' (' . ($operation->bankAgent?->code ?? '—') . ')']],
                [['value' => 'Tipo'], ['value' => ($operation->operationType?->name ?? '—') . ' (' . ($operation->operationType?->cash_direction ?? '—') . ')']],
                [['value' => 'Monto'], ['value' => ($operation->currency ?? 'PEN') . ' ' . number_format((float) $operation->amount, 2), 'align' => 'right']],
                [['value' => 'Fecha Efectiva'], ['value' => $operation->effective_at?->format('Y-m-d H:i') ?? '—', 'class' => 'data-mono']],
                [['value' => 'Fecha de Registro'], ['value' => $operation->recorded_at?->format('Y-m-d H:i') ?? '—', 'class' => 'data-mono']],
                [['value' => 'Registrado por'], ['value' => $operation->user?->username_normalized ?? '—']],
                [['value' => 'Referencia'], ['value' => $operation->reference ?? '—', 'class' => 'data-mono']],
                [['value' => 'Observación'], ['value' => $operation->observation ?? '—']],
                [['value' => 'Clave de Idempotencia'], ['value' => '<code>' . $operation->idempotency_key . '</code>', 'class' => 'data-mono']],
            ]"
        />
    </div>

    @if($operation->isAnnulled())
    <div class="card" style="margin-bottom:var(--space-lg);">
        <div class="card-header"><h3 class="card-title">Información de Anulación</h3></div>
        <x-ui.data-table
            :headers="[
                ['label' => 'Campo'],
                ['label' => 'Valor'],
            ]"
            :rows="[
                [['value' => 'Anulado por'], ['value' => $operation->annulledBy?->username_normalized ?? '—']],
                [['value' => 'Fecha de Anulación'], ['value' => $operation->annulled_at?->format('Y-m-d H:i') ?? '—', 'class' => 'data-mono']],
                [['value' => 'Motivo de Anulación'], ['value' => $operation->annulment_reason ?? '—']],
            ]"
        />
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
