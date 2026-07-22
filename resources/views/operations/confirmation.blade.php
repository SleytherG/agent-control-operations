@extends('layouts.authenticated')

@section('title', 'Operación Confirmada — Control de Operaciones')

@section('content')
    <h1>Operación Registrada</h1>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if(isset($operation))
        <table>
            <tr>
                <th>Agente</th>
                <td>{{ $operation->bankAgent?->bank?->name }} — {{ $operation->bankAgent?->store?->name }} ({{ $operation->bankAgent?->code }})</td>
            </tr>
            <tr>
                <th>Tipo</th>
                <td>{{ $operation->operationType?->name }}</td>
            </tr>
            <tr>
                <th>Monto</th>
                <td>{{ $operation->currency }} {{ number_format($operation->amount, 2) }}</td>
            </tr>
            <tr>
                <th>Estado</th>
                <td>{{ $operation->status }}</td>
            </tr>
            <tr>
                <th>Fecha Efectiva</th>
                <td>{{ $operation->effective_at?->format('Y-m-d H:i') }}</td>
            </tr>
        </table>
    @endif

    <p>
        <a href="{{ route('operations.index') }}">Ver Historial</a>
        <a href="{{ route('operations.create') }}">Registrar Otra</a>
    </p>
@endsection
