@extends('layouts.authenticated')

@section('title', 'Tiendas — Control de Operaciones')

@section('content')
    <h2 class="admin-title" style="margin-bottom:var(--space-xs);">Tiendas</h2>
    <p class="admin-subtitle">Gestion de puntos de venta registrados en el sistema.</p>

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

    <div style="margin-bottom: var(--space-md); display: flex; justify-content: space-between; align-items: flex-end; gap: var(--space-md); flex-wrap: wrap;">
        <a href="{{ route('admin.stores.create') }}" class="btn btn--primary">Nueva Tienda</a>

        <form method="GET" action="{{ route('admin.stores.index') }}" style="display: flex; gap: var(--space-sm); align-items: flex-end; flex-wrap: wrap;">
            <x-ui.select
                label="Distrito"
                name="district_id"
                :options="$districts->pluck('name', 'id')->toArray()"
                :selected="request('district_id')"
                placeholder="Todos los distritos"
            />
            <x-ui.select
                label="Estado"
                name="is_active"
                :options="['1' => 'Activo', '0' => 'Inactivo']"
                :selected="request('is_active')"
                placeholder="Todos los estados"
            />
            <x-ui.button variant="secondary" type="submit">Filtrar</x-ui.button>
        </form>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Código</th><th>Nombre</th><th>Distrito</th><th>Provincia</th><th>Región</th><th class="table-th-center">Estado</th><th class="table-th-center">Acciones</th></tr></thead>
                <tbody>
                    @forelse($stores as $store)
                        <tr>
                            <td>{{ $store->code }}</td><td>{{ $store->name }}</td><td>{{ $store->district?->name ?? '—' }}</td><td>{{ $store->district?->province?->name ?? '—' }}</td><td>{{ $store->district?->province?->region?->name ?? '—' }}</td>
                            <td class="table-td-center"><x-ui.badge :variant="$store->is_active ? 'active' : 'inactive'">{{ $store->is_active ? 'Activo' : 'Inactivo' }}</x-ui.badge></td>
                            <td class="table-td-center">
                                <a href="{{ route('admin.stores.show', $store) }}" class="btn btn--secondary">Ver</a>
                                <a href="#" class="btn btn--primary" onclick="event.preventDefault(); document.getElementById('edit-store-{{ $store->id }}').submit();">Editar</a>
                                <form id="edit-store-{{ $store->id }}" action="{{ route('admin.stores.update', $store) }}" method="POST" style="display:none">@csrf @method('PATCH')</form>
                                @if($store->is_active)
                                    <form action="{{ route('admin.stores.deactivate', $store) }}" method="POST" style="display:inline" data-confirm="¿Desactivar esta tienda?">@csrf @method('DELETE')<button type="submit" class="btn btn--danger">Desactivar</button></form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="table-empty"><div class="table-empty-icon" aria-hidden="true">&#x1F3EA;</div>No se encontraron tiendas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-ui.pagination
            :currentPage="$stores->currentPage()"
            :lastPage="$stores->lastPage()"
            :total="$stores->total()"
            :from="$stores->firstItem() ?? 0"
            :to="$stores->lastItem() ?? 0"
        />
    </div>
@endsection
