@extends('layouts.authenticated')

@section('title', 'Tipos de Operación — Control de Operaciones')

@section('content')
    <h1>Tipos de Operación</h1>

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

    <a href="{{ route('admin.operation-types.create') }}">Nuevo Tipo</a>

    <form method="GET" action="{{ route('admin.operation-types.index') }}">
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
        <a href="{{ route('admin.operation-types.index', ['general' => 1]) }}">Solo Generales</a>
    </form>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Dirección</th>
                <th>Banco</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($types as $type)
                <tr>
                    <td>{{ $type->name }}</td>
                    <td>{{ $type->description ?? '—' }}</td>
                    <td>{{ $type->cash_direction }}</td>
                    <td>{{ $type->bank?->name ?? 'General' }}</td>
                    <td>{{ $type->is_active ? 'Activo' : 'Inactivo' }}</td>
                    <td>
                        <a href="{{ route('admin.operation-types.edit', $type) }}">Editar</a>
                        @if($type->is_active)
                            <form action="{{ route('admin.operation-types.destroy', $type) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Desactivar este tipo?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Desactivar</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No se encontraron tipos de operación.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $types->links() }}
@endsection
