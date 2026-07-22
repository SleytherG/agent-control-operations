@extends('layouts.authenticated')

@section('title', $region->name . ' — Provincias — Control de Operaciones')

@section('content')
    <h1>{{ $region->name }} — Provincias</h1>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <a href="{{ route('admin.regions.index') }}">Volver a Regiones</a>

    <form method="POST" action="{{ route('admin.regions.provinces.store', $region) }}">
        @csrf
        <div>
            <label for="name">Nueva Provincia</label>
            <input type="text" name="name" id="name" required maxlength="160">
            <button type="submit">Crear</button>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($provinces as $province)
                <tr>
                    <td>{{ $province->name }}</td>
                    <td>{{ $province->is_active ? 'Activo' : 'Inactivo' }}</td>
                    <td>
                        <a href="{{ route('admin.provinces.districts.index', $province) }}">Ver Distritos</a>
                        @if($province->is_active)
                            <form action="{{ route('admin.provinces.deactivate', $province) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Desactivar esta provincia?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Desactivar</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="3">No se encontraron provincias.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $provinces->links() }}
@endsection
