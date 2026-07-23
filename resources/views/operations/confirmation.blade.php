@extends('layouts.authenticated')

@section('title', $title ?? 'Operacion Registrada — AgenteFlow')

@section('content')
<div class="confirmation-page" style="max-width: 600px; margin: 0 auto;">
    <div class="card" style="text-align: center; padding: var(--space-xl);">
        <div style="font-size: 48px; margin-bottom: var(--space-md);" aria-hidden="true">&#x2705;</div>
        <h2 class="admin-title" style="margin-bottom: var(--space-xs);">Operacion Registrada</h2>

        @if(session('status'))
            <div class="alert alert-success" role="alert" style="margin-bottom: var(--space-md);">
                {{ session('status') }}
            </div>
        @endif

        @if(isset($operation))
        <div class="table-responsive" style="margin: var(--space-lg) 0; text-align: left;">
            <table class="data-table">
                <tbody>
                    <tr>
                        <th style="width: 40%;">ID de Operacion</th>
                        <td style="font-family: var(--font-family-mono);">#{{ $operation->id }}</td>
                    </tr>
                    <tr>
                        <th>Agente</th>
                        <td>{{ $operation->bankAgent?->bank?->name ?? '—' }} — {{ $operation->bankAgent?->code ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Tipo</th>
                        <td>{{ $operation->operationType?->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Monto</th>
                        <td style="font-family: var(--font-family-mono);">{{ $operation->currency }} {{ number_format((float) $operation->amount, 2) }}</td>
                    </tr>
                    @if($operation->reference)
                    <tr>
                        <th>Referencia</th>
                        <td style="font-family: var(--font-family-mono);">{{ $operation->reference }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>Fecha Efectiva</th>
                        <td>{{ $operation->effective_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Estado</th>
                        <td><x-ui.badge variant="{{ $operation->isActive() ? 'active' : 'annulled' }}">{{ $operation->isActive() ? 'Activa' : 'Anulada' }}</x-ui.badge></td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif

        <div style="display: flex; gap: var(--space-sm); justify-content: center; margin-top: var(--space-lg);">
            <a href="{{ route('operations.create') }}" class="btn btn--secondary">Registrar Otra</a>
            <a href="{{ route('operations.index') }}" class="btn btn--primary">Ver Historial</a>
        </div>
    </div>
</div>
@endsection
