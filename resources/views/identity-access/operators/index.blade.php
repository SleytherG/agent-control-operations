@extends('layouts.authenticated')

@section('title', 'Operadores — Control de Operaciones')

@section('content')
    <h1>Operadores</h1>

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

    <a href="{{ route('admin.users.create') }}">Nuevo Operador</a>

    <form method="GET" action="{{ route('admin.users.index') }}">
        <select name="status">
            <option value="">Todos los estados</option>
            <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>Activo</option>
            <option value="INACTIVE" {{ request('status') === 'INACTIVE' ? 'selected' : '' }}>Inactivo</option>
        </select>
        <button type="submit">Filtrar</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Email</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($operators as $operator)
                <tr>
                    <td>{{ $operator->username_normalized }}</td>
                    <td>{{ $operator->email_normalized }}</td>
                    <td>{{ $operator->status->value }}</td>
                    <td>
                        <a href="{{ route('admin.users.edit', $operator) }}">Editar</a>
                        <a href="{{ route('admin.users.assignments.index', $operator) }}">Asignaciones</a>
                        @if($operator->status->value === 'ACTIVE')
                            <form action="{{ route('admin.users.deactivate-operator', $operator) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Desactivar este operador?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Desactivar</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No se encontraron operadores.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $operators->links() }}
@endsection
