@extends('layouts.authenticated')

@section('title', $province->name . ' — Distritos — Control de Operaciones')

@section('content')
    <h2 class="admin-title" style="margin-bottom:var(--space-xs);">{{ $province->name }} — Distritos</h2>

    @if(session('status'))
        <div class="alert alert-success" role="alert" style="margin: var(--space-md) 0;">{{ session('status') }}</div>
    @endif

    <div style="margin-bottom: var(--space-md);">
        <a href="{{ route('admin.regions.provinces.index', $province->region) }}" class="btn btn--secondary">Volver a Provincias</a>
    </div>

    <div class="card" style="max-width: 500px; margin-bottom: var(--space-lg);">
        <h3 style="margin-bottom: var(--space-sm);">Nuevo Distrito</h3>
        <form method="POST" action="{{ route('admin.provinces.districts.store', $province) }}" style="display: flex; gap: var(--space-sm); align-items: flex-end;">
            @csrf
            <x-ui.input
                label="Nombre"
                name="name"
                value="{{ old('name') }}"
                required="true"
                placeholder="Nombre del distrito"
            />
            <x-ui.button variant="primary" type="submit">Crear</x-ui.button>
        </form>
    </div>

    <div class="card">
        <div class="table-responsive"><table class="data-table">
            <thead><tr><th>Nombre</th><th class="table-th-center">Estado</th><th class="table-th-center">Acciones</th></tr></thead>
            <tbody>
                @forelse($districts as $district)
                    <tr>
                        <td>{{ $district->name }}</td>
                        <td class="table-td-center">
                            <x-ui.badge :variant="$district->is_active ? 'active' : 'inactive'">{{ $district->is_active ? 'Activo' : 'Inactivo' }}</x-ui.badge>
                        </td>
                        <td class="table-td-center">
                            @if($district->is_active)
                                <form action="{{ route('admin.districts.deactivate', $district) }}" method="POST" style="display:inline;" data-confirm="¿Desactivar este distrito?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn--danger">Desactivar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="table-empty"><div class="table-empty-icon" aria-hidden="true">&#x1F30D;</div>No se encontraron distritos.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <x-ui.pagination
            :currentPage="$districts->currentPage()"
            :lastPage="$districts->lastPage()"
            :total="$districts->total()"
            :from="$districts->firstItem() ?? 0"
            :to="$districts->lastItem() ?? 0"
        />
    </div>
@endsection
