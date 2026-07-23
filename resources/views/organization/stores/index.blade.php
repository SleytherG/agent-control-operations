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
        <x-ui.data-table
            :headers="[
                ['label' => 'Codigo'],
                ['label' => 'Nombre'],
                ['label' => 'Distrito'],
                ['label' => 'Provincia'],
                ['label' => 'Region'],
                ['label' => 'Estado', 'align' => 'center'],
                ['label' => 'Acciones', 'align' => 'center'],
            ]"
            :rows="$stores->map(function($store) {
                $actions = \"<a href='\" . route('admin.stores.show', $store) . \"' class='btn btn--secondary'>Ver</a>
                    <a href='#' onclick=\\\"event.preventDefault(); document.getElementById('edit-store-{$store->id}').submit();\\\" class='btn btn--primary'>Editar</a>
                    <form id='edit-store-{$store->id}' action='\" . route('admin.stores.update', $store) . \"' method='POST' style='display:none;'>
                        <input type='hidden' name='_token' value='\" . csrf_token() . \"'>
                        <input type='hidden' name='_method' value='PATCH'>
                    </form>\";
                if (\$store->is_active) {
                    \$actions .= \"<form action='\" . route('admin.stores.deactivate', $store) . \"' method='POST' style='display:inline;' onsubmit=\\\"return confirm('Desactivar esta tienda?');\\\">\";
                    \$actions .= \"<input type='hidden' name='_token' value='\" . csrf_token() . \"'>\";
                    \$actions .= \"<input type='hidden' name='_method' value='DELETE'>\";
                    \$actions .= \"<button type='submit' class='btn btn--danger'>Desactivar</button></form>\";
                }
                return [
                    ['value' => \$store->code],
                    ['value' => \$store->name],
                    ['value' => \$store->district?->name ?? '—'],
                    ['value' => \$store->district?->province?->name ?? '—'],
                    ['value' => \$store->district?->province?->region?->name ?? '—'],
                    ['value' => \$store->is_active ? \"<x-ui.badge variant='active'>Activo</x-ui.badge>\" : \"<x-ui.badge variant='inactive'>Inactivo</x-ui.badge>\", 'align' => 'center'],
                    ['value' => \$actions, 'align' => 'center'],
                ];
            })->toArray()"
            emptyMessage="No se encontraron tiendas."
        />
        <x-ui.pagination
            :currentPage="$stores->currentPage()"
            :lastPage="$stores->lastPage()"
            :total="$stores->total()"
            :from="$stores->firstItem() ?? 0"
            :to="$stores->lastItem() ?? 0"
        />
    </div>
@endsection
