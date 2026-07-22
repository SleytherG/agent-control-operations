@extends('layouts.authenticated')

@section('title', 'Cierres Diarios — Control de Operaciones')

@section('content')
<h1>Cierres Diarios</h1>

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

<a href="{{ route('daily-closures.create') }}">Generar Cierre</a>

<form method="GET" action="{{ route('daily-closures.index') }}">
    <select name="bank_agent_id">
        <option value="">Todos los agentes</option>
        @foreach($agents as $agent)
            <option value="{{ $agent->id }}" {{ request('bank_agent_id') == $agent->id ? 'selected' : '' }}>
                {{ $agent->code }} — {{ $agent->bank->name ?? 'Sin banco' }}
            </option>
        @endforeach
    </select>

    <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="Desde">
    <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="Hasta">

    <select name="status">
        <option value="">Todos los estados</option>
        <option value="ACTIVO" {{ request('status') === 'ACTIVO' ? 'selected' : '' }}>Activo</option>
        <option value="CONFIRMADO" {{ request('status') === 'CONFIRMADO' ? 'selected' : '' }}>Confirmado</option>
        <option value="REABIERTO" {{ request('status') === 'REABIERTO' ? 'selected' : '' }}>Reabierto</option>
    </select>

    <button type="submit">Filtrar</button>
</form>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Agente</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th>Operaciones</th>
            <th>Monto Bruto</th>
            <th>Neto</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($closures as $closure)
            <tr>
                <td>{{ $closure->id }}</td>
                <td>{{ $closure->bankAgent?->code }} — {{ $closure->bankAgent?->store?->name }}</td>
                <td>{{ $closure->business_date?->format('Y-m-d') }}</td>
                <td>{{ $closure->status }}</td>
                <td>{{ $closure->operation_count }}</td>
                <td>{{ number_format($closure->gross_amount, 2) }}</td>
                <td>{{ number_format($closure->net_movement, 2) }}</td>
                <td>
                    <a href="{{ route('daily-closures.show', $closure) }}">Ver</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="8">No se encontraron cierres.</td></tr>
        @endforelse
    </tbody>
</table>

{{ $closures->links() }}
@endsection
