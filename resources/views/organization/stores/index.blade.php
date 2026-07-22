@extends('layouts.authenticated')

@section('title', 'Tiendas — Control de Operaciones')

@section('content')
    <h1>Tiendas</h1>

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

    <a href="{{ route('admin.stores.create') }}">Nueva Tienda</a>

    <form method="GET" action="{{ route('admin.stores.index') }}">
        <select name="district_id">
            <option value="">Todos los distritos</option>
            @foreach($districts as $district)
                <option value="{{ $district->id }}" {{ request('district_id') == $district->id ? 'selected' : '' }}>
                    {{ $district->name }}
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
                <th>Nombre</th>
                <th>Distrito</th>
                <th>Provincia</th>
                <th>Región</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stores as $store)
                <tr>
                    <td>{{ $store->code }}</td>
                    <td>{{ $store->name }}</td>
                    <td>{{ $store->district?->name }}</td>
                    <td>{{ $store->district?->province?->name }}</td>
                    <td>{{ $store->district?->province?->region?->name }}</td>
                    <td>{{ $store->is_active ? 'Activo' : 'Inactivo' }}</td>
                    <td>
                        <a href="{{ route('admin.stores.show', $store) }}">Ver</a>
                        <a href="{{ route('admin.stores.update', $store) }}" onclick="event.preventDefault(); document.getElementById('edit-store-{{ $store->id }}').submit();">Editar</a>
                        <form id="edit-store-{{ $store->id }}" action="{{ route('admin.stores.update', $store) }}" method="POST" style="display:none;">
                            @csrf
                            @method('PATCH')
                        </form>
                        @if($store->is_active)
                            <form action="{{ route('admin.stores.deactivate', $store) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Desactivar esta tienda?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Desactivar</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">No se encontraron tiendas.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $stores->links() }}
@endsection
