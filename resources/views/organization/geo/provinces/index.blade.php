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
        <x-ui.data-table
            :headers="[
                ['label' => 'Nombre'],
                ['label' => 'Estado', 'align' => 'center'],
                ['label' => 'Acciones', 'align' => 'center'],
            ]"
            :rows="$provinces->map(function($province) {
                \$actions = \"<a href='\" . route('admin.provinces.districts.index', \$province) . \"' class='btn btn--secondary'>Ver Distritos</a>\";
                if (\$province->is_active) {
                    \$actions .= \"<form action='\" . route('admin.provinces.deactivate', \$province) . \"' method='POST' style='display:inline;' onsubmit=\\\"return confirm('Desactivar esta provincia?');\\\">\";
                    \$actions .= \"<input type='hidden' name='_token' value='\" . csrf_token() . \"'>\";
                    \$actions .= \"<input type='hidden' name='_method' value='DELETE'>\";
                    \$actions .= \"<button type='submit' class='btn btn--danger'>Desactivar</button></form>\";
                }
                return [
                    ['value' => \$province->name],
                    ['value' => \$province->is_active ? \"<x-ui.badge variant='active'>Activo</x-ui.badge>\" : \"<x-ui.badge variant='inactive'>Inactivo</x-ui.badge>\", 'align' => 'center'],
                    ['value' => \$actions, 'align' => 'center'],
                ];
            })->toArray()"
            emptyMessage="No se encontraron provincias."
        />
        <x-ui.pagination
            :currentPage="$provinces->currentPage()"
            :lastPage="$provinces->lastPage()"
            :total="$provinces->total()"
            :from="$provinces->firstItem() ?? 0"
            :to="$provinces->lastItem() ?? 0"
        />
    </div>
@endsection
