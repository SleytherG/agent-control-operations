@extends('layouts.authenticated')

@section('title', $region->name . ' — Provincias — Control de Operaciones')

@section('content')
    <h2 class="admin-title" style="margin-bottom:var(--space-xs);">{{ $region->name }} — Provincias</h2>

    @if(session('status'))
        <div class="alert alert-success" role="alert" style="margin: var(--space-md) 0;">{{ session('status') }}</div>
    @endif

    <div style="margin-bottom: var(--space-md);">
        <a href="{{ route('admin.regions.index') }}" class="btn btn--secondary">Volver a Regiones</a>
    </div>

    <div class="card" style="max-width: 500px; margin-bottom: var(--space-lg);">
        <h3 style="margin-bottom: var(--space-sm);">Nueva Provincia</h3>
        <form method="POST" action="{{ route('admin.regions.provinces.store', $region) }}" style="display: flex; gap: var(--space-sm); align-items: flex-end;">
            @csrf
            <x-ui.input
                label="Nombre"
                name="name"
                value="{{ old('name') }}"
                required="true"
                placeholder="Nombre de la provincia"
            />
            <x-ui.button variant="primary" type="submit">Crear</x-ui.button>
        </form>
    </div>

    <div class="card">
        <div class="table-responsive"><table class="data-table">
            <thead><tr><th>Nombre</th><th class="table-th-center">Estado</th><th class="table-th-center">Acciones</th></tr></thead>
            <tbody>
                @forelse($provinces as $province)
                    <tr>
                        <td>{{ $province->name }}</td>
                        <td class="table-td-center">
                            <x-ui.badge :variant="$province->is_active ? 'active' : 'inactive'">{{ $province->is_active ? 'Activo' : 'Inactivo' }}</x-ui.badge>
                        </td>
                        <td class="table-td-center">
                            <a href="{{ route('admin.provinces.districts.index', $province) }}" class="btn btn--secondary">Ver Distritos</a>
                            @if($province->is_active)
                                <form action="{{ route('admin.provinces.deactivate', $province) }}" method="POST" style="display:inline;" data-confirm="¿Desactivar esta provincia?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn--danger">Desactivar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="table-empty"><div class="table-empty-icon" aria-hidden="true">&#x1F30D;</div>No se encontraron provincias.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <x-ui.pagination
            :currentPage="$provinces->currentPage()"
            :lastPage="$provinces->lastPage()"
            :total="$provinces->total()"
            :from="$provinces->firstItem() ?? 0"
            :to="$provinces->lastItem() ?? 0"
        />
    </div>
@endsection
