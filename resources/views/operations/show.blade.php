@extends('layouts.authenticated')

@section('title', 'Detalle de Operación — Control de Operaciones')

@section('content')
    <h1>Detalle de Operación #{{ $operation->id }}</h1>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <table>
        <tr>
            <th>ID</th>
            <td>{{ $operation->id }}</td>
        </tr>
        <tr>
            <th>Estado</th>
            <td>{{ $operation->status === 'ACTIVE' ? 'Activa' : 'Anulada' }}</td>
        </tr>
        <tr>
            <th>Agente</th>
            <td>{{ $operation->bankAgent?->bank?->name }} — {{ $operation->bankAgent?->store?->name }} ({{ $operation->bankAgent?->code }})</td>
        </tr>
        <tr>
            <th>Tipo</th>
            <td>{{ $operation->operationType?->name }} ({{ $operation->operationType?->cash_direction }})</td>
        </tr>
        <tr>
            <th>Monto</th>
            <td>{{ $operation->currency }} {{ number_format($operation->amount, 2) }}</td>
        </tr>
        <tr>
            <th>Fecha Efectiva</th>
            <td>{{ $operation->effective_at?->format('Y-m-d H:i') }}</td>
        </tr>
        <tr>
            <th>Fecha de Registro</th>
            <td>{{ $operation->recorded_at?->format('Y-m-d H:i') }}</td>
        </tr>
        <tr>
            <th>Registrado por</th>
            <td>{{ $operation->user?->username_normalized ?? '—' }}</td>
        </tr>
        <tr>
            <th>Referencia</th>
            <td>{{ $operation->reference ?? '—' }}</td>
        </tr>
        <tr>
            <th>Observación</th>
            <td>{{ $operation->observation ?? '—' }}</td>
        </tr>
        <tr>
            <th>Clave de Idempotencia</th>
            <td><code>{{ $operation->idempotency_key }}</code></td>
        </tr>
        @if($operation->isAnnulled())
            <tr>
                <th>Anulado por</th>
                <td>{{ $operation->annulledBy?->username_normalized ?? '—' }}</td>
            </tr>
            <tr>
                <th>Fecha de Anulación</th>
                <td>{{ $operation->annulled_at?->format('Y-m-d H:i') }}</td>
            </tr>
            <tr>
                <th>Motivo de Anulación</th>
                <td>{{ $operation->annulment_reason }}</td>
            </tr>
        @endif
    </table>

    <p>
        <a href="{{ route('operations.index') }}">Volver al Historial</a>

        @if($operation->isActive())
            @can('annul', $operation)
                <a href="{{ route('operations.annul', $operation) }}" onclick="event.preventDefault(); if(confirm('¿Anular esta operación?')) { document.getElementById('annul-form-{{ $operation->id }}').submit(); }">
                    Anular Operación
                </a>
                <form id="annul-form-{{ $operation->id }}" action="{{ route('operations.annul', $operation) }}" method="POST" style="display:none;">
                    @csrf
                    <input type="hidden" name="reason" value="Anulación desde vista de detalle">
                </form>
            @endcan
        @endif
    </p>

    @if($operation->isActive())
        @can('annul', $operation)
            <hr>
            <h2>Anular Operación</h2>
            <form method="POST" action="{{ route('operations.annul', $operation) }}">
                @csrf
                <div>
                    <label for="reason">Motivo de Anulación</label>
                    <textarea name="reason" id="reason" required maxlength="500" placeholder="Explique el motivo de la anulación"></textarea>
                </div>
                <button type="submit">Confirmar Anulación</button>
            </form>
        @endcan
    @endif
@endsection
