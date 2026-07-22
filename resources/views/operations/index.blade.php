@extends('layouts.authenticated')

@section('title', 'Historial de Operaciones — Control de Operaciones')

@section('content')
    <h1>Historial de Operaciones</h1>

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

    <a href="{{ route('operations.create') }}">Registrar Operación</a>

    <form method="GET" action="{{ route('operations.index') }}">
        <select name="bank_agent_id">
            <option value="">Todos los agentes</option>
            @foreach($agents as $agent)
                <option value="{{ $agent->id }}" {{ request('bank_agent_id') == $agent->id ? 'selected' : '' }}>
                    {{ $agent->code }} — {{ $agent->bank->name ?? 'Sin banco' }}
                </option>
            @endforeach
        </select>

        <select name="operation_type_id">
            <option value="">Todos los tipos</option>
            @foreach($types as $type)
                <option value="{{ $type->id }}" {{ request('operation_type_id') == $type->id ? 'selected' : '' }}>
                    {{ $type->name }}
                </option>
            @endforeach
        </select>

        <select name="status">
            <option value="">Todos los estados</option>
            <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>Activas</option>
            <option value="ANNULLED" {{ request('status') === 'ANNULLED' ? 'selected' : '' }}>Anuladas</option>
        </select>

        <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="Desde">
        <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="Hasta">

        <button type="submit">Filtrar</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Agente</th>
                <th>Tipo</th>
                <th>Monto</th>
                <th>Estado</th>
                <th>Fecha Efectiva</th>
                <th>Registrado por</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($operations as $operation)
                <tr>
                    <td>{{ $operation->id }}</td>
                    <td>{{ $operation->bankAgent?->code }} — {{ $operation->bankAgent?->store?->name }}</td>
                    <td>{{ $operation->operationType?->name }}</td>
                    <td>{{ $operation->currency }} {{ number_format($operation->amount, 2) }}</td>
                    <td>{{ $operation->status === 'ACTIVE' ? 'Activa' : 'Anulada' }}</td>
                    <td>{{ $operation->effective_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $operation->user?->username_normalized ?? '—' }}</td>
                    <td>
                        <a href="{{ route('operations.show', $operation) }}">Ver</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">No se encontraron operaciones.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $operations->links() }}
@endsection
