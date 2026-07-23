@extends('layouts.authenticated')

@section('title', 'Anular Operación — Control de Operaciones')

@section('content')
<div class="operation-annul">
    <div style="margin-bottom:var(--space-lg);">
        <h2 class="admin-title" style="margin-bottom:var(--space-xs);">Anular Operación #{{ $operation->id }}</h2>
        <p class="admin-subtitle">Confirme los datos antes de proceder con la anulación.</p>
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
        <div class="card-header"><h3 class="card-title">Resumen de la Operación</h3></div>
        <x-ui.data-table
            :headers="[
                ['label' => 'Campo'],
                ['label' => 'Valor'],
            ]"
            :rows="[
                [['value' => 'Monto'], ['value' => ($operation->currency ?? 'PEN') . ' ' . number_format((float) $operation->amount, 2), 'align' => 'right']],
                [['value' => 'Tipo'], ['value' => $operation->operationType?->name ?? '—']],
                [['value' => 'Fecha Efectiva'], ['value' => $operation->effective_at?->format('Y-m-d H:i') ?? '—', 'class' => 'data-mono']],
                [['value' => 'Registrado por'], ['value' => $operation->user?->username_normalized ?? '—']],
            ]"
        />
    </div>

    @if(!$operation->isActive())
        <div class="alert alert-warning" role="alert" style="margin-bottom:var(--space-lg);">Esta operación ya se encuentra anulada.</div>
    @else
        <div class="card">
            <div class="card-header"><h3 class="card-title">Motivo de Anulación</h3></div>
            <form method="POST" action="{{ route('operations.annul', $operation) }}" style="padding:var(--space-md);">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="reason">Motivo de Anulación (*)</label>
                    <textarea name="reason" id="reason" class="form-input" rows="3" required maxlength="500" placeholder="Explique el motivo de la anulación">{{ old('reason') }}</textarea>
                </div>
                <div style="display:flex;gap:var(--space-sm);margin-top:var(--space-md);">
                    <a href="{{ route('operations.show', $operation) }}" class="btn btn--secondary btn--sm">Cancelar</a>
                    <x-ui.button variant="danger" type="submit">Confirmar Anulación</x-ui.button>
                </div>
            </form>
        </div>
    @endif
</div>
@endsection
