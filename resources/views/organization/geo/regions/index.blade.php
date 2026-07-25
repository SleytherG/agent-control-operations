@extends('layouts.authenticated')

@section('title', 'Regiones — Control de Operaciones')

@section('content')
    <h2 class="admin-title" style="margin-bottom:var(--space-xs);">Regiones</h2>
    <p class="admin-subtitle">Divisiones geograficas principales del pais.</p>

    @if(session('status'))
        <div class="alert alert-success" role="alert" style="margin: var(--space-md) 0;">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" role="alert" style="margin: var(--space-md) 0;">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="card" style="max-width: 500px; margin-bottom: var(--space-lg);">
        <h3 style="margin-bottom: var(--space-sm);">Nueva Region</h3>
        <form method="POST" action="{{ route('admin.regions.store') }}" style="display: flex; gap: var(--space-sm); align-items: flex-end;">
            @csrf
            <x-ui.input
                label="Nombre"
                name="name"
                value="{{ old('name') }}"
                required="true"
                placeholder="Nombre de la region"
            />
            <x-ui.button variant="primary" type="submit">Crear</x-ui.button>
        </form>
    </div>

    <div class="card">
        <div class="table-responsive"><table class="data-table">
            <thead><tr><th>Nombre</th><th class="table-th-center">Provincias</th><th class="table-th-center">Estado</th><th class="table-th-center">Acciones</th></tr></thead>
            <tbody>
                @forelse($regions as $region)
                    <tr>
                        <td>{{ $region->name }}</td>
                        <td class="table-td-center">{{ $region->provinces_count }}</td>
                        <td class="table-td-center">
                            <x-ui.badge :variant="$region->is_active ? 'active' : 'inactive'">{{ $region->is_active ? 'Activo' : 'Inactivo' }}</x-ui.badge>
                        </td>
                        <td class="table-td-center">
                            <a href="{{ route('admin.regions.show', $region) }}" class="btn btn--secondary">Ver Provincias</a>
                            @if($region->is_active)
                                <form action="{{ route('admin.regions.deactivate', $region) }}" method="POST" style="display:inline;" data-confirm="¿Desactivar esta región?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn--danger">Desactivar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="table-empty"><div class="table-empty-icon" aria-hidden="true">&#x1F30D;</div>No se encontraron regiones.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <x-ui.pagination
            :currentPage="$regions->currentPage()"
            :lastPage="$regions->lastPage()"
            :total="$regions->total()"
            :from="$regions->firstItem() ?? 0"
            :to="$regions->lastItem() ?? 0"
        />
    </div>
@endsection
