@extends('layouts.authenticated')

@section('title', $province->name . ' — Distritos — Control de Operaciones')

@section('content')
    <h1>{{ $province->name }} — Distritos</h1>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <a href="{{ route('admin.regions.provinces.index', $province->region) }}">Volver a Provincias</a>

    <form method="POST" action="{{ route('admin.provinces.districts.store', $province) }}">
        @csrf
        <div>
            <label for="name">Nuevo Distrito</label>
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
            @forelse($districts as $district)
                <tr>
                    <td>{{ $district->name }}</td>
                    <td>{{ $district->is_active ? 'Activo' : 'Inactivo' }}</td>
                    <td>
                        @if($district->is_active)
                            <form action="{{ route('admin.districts.deactivate', $district) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Desactivar este distrito?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Desactivar</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="3">No se encontraron distritos.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $districts->links() }}
@endsection
