@extends('layouts.authenticated')

@section('title', 'Agentes Bancarios — Control de Operaciones')

@section('content')
    <h1>Agentes Bancarios</h1>

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

    <a href="{{ route('admin.bank-agents.create') }}">Nuevo Agente</a>

    <form method="GET" action="{{ route('admin.bank-agents.index') }}">
        <select name="store_id">
            <option value="">Todas las tiendas</option>
            @foreach($stores as $store)
                <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                    {{ $store->name }}
                </option>
            @endforeach
        </select>
        <select name="bank_id">
            <option value="">Todos los bancos</option>
            @foreach($banks as $bank)
                <option value="{{ $bank->id }}" {{ request('bank_id') == $bank->id ? 'selected' : '' }}>
                    {{ $bank->name }}
                </option>
            @endforeach
        </select>
        <select name="is_active">
            <option value="">Todos los estados</option>
            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Activo</option>
            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactivo</option>
        </select>
        <button type="submit">Filtrar</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Terminal</th>
                <th>Tienda</th>
                <th>Banco</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($agents as $agent)
                <tr>
                    <td>{{ $agent->code }}</td>
                    <td>{{ $agent->terminal_code }}</td>
                    <td>{{ $agent->store?->name }}</td>
                    <td>{{ $agent->bank?->name }}</td>
                    <td>{{ $agent->is_active ? 'Activo' : 'Inactivo' }}</td>
                    <td>
                        <a href="{{ route('admin.bank-agents.update', $agent) }}" onclick="event.preventDefault(); document.getElementById('edit-agent-{{ $agent->id }}').submit();">Editar</a>
                        <form id="edit-agent-{{ $agent->id }}" action="{{ route('admin.bank-agents.update', $agent) }}" method="POST" style="display:none;">
                            @csrf
                            @method('PATCH')
                        </form>
                        @if($agent->is_active)
                            <form action="{{ route('admin.bank-agents.deactivate', $agent) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Desactivar este agente? Se terminarán todas las asignaciones activas.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Desactivar</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No se encontraron agentes.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $agents->links() }}
@endsection
