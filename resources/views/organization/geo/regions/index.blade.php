@extends('layouts.authenticated')

@section('title', 'Regiones — Control de Operaciones')

@section('content')
    <h1>Regiones</h1>

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

    <form method="POST" action="{{ route('admin.regions.store') }}">
        @csrf
        <div>
            <label for="name">Nueva Región</label>
            <input type="text" name="name" id="name" required maxlength="160">
            <button type="submit">Crear</button>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Provincias</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($regions as $region)
                <tr>
                    <td>{{ $region->name }}</td>
                    <td>{{ $region->provinces_count }}</td>
                    <td>{{ $region->is_active ? 'Activo' : 'Inactivo' }}</td>
                    <td>
                        <a href="{{ route('admin.regions.show', $region) }}">Ver Provincias</a>
                        @if($region->is_active)
                            <form action="{{ route('admin.regions.deactivate', $region) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Desactivar esta región?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Desactivar</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No se encontraron regiones.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $regions->links() }}
@endsection
