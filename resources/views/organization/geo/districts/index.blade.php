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
        <x-ui.data-table
            :headers="[
                ['label' => 'Nombre'],
                ['label' => 'Estado', 'align' => 'center'],
                ['label' => 'Acciones', 'align' => 'center'],
            ]"
            :rows="$districts->map(function($district) {
                \$actions = '';
                if (\$district->is_active) {
                    \$actions .= \"<form action='\" . route('admin.districts.deactivate', \$district) . \"' method='POST' style='display:inline;' onsubmit=\\\"return confirm('Desactivar este distrito?');\\\">\";
                    \$actions .= \"<input type='hidden' name='_token' value='\" . csrf_token() . \"'>\";
                    \$actions .= \"<input type='hidden' name='_method' value='DELETE'>\";
                    \$actions .= \"<button type='submit' class='btn btn--danger'>Desactivar</button></form>\";
                }
                return [
                    ['value' => \$district->name],
                    ['value' => \$district->is_active ? \"<x-ui.badge variant='active'>Activo</x-ui.badge>\" : \"<x-ui.badge variant='inactive'>Inactivo</x-ui.badge>\", 'align' => 'center'],
                    ['value' => \$actions, 'align' => 'center'],
                ];
            })->toArray()"
            emptyMessage="No se encontraron distritos."
        />
        <x-ui.pagination
            :currentPage="$districts->currentPage()"
            :lastPage="$districts->lastPage()"
            :total="$districts->total()"
            :from="$districts->firstItem() ?? 0"
            :to="$districts->lastItem() ?? 0"
        />
    </div>
@endsection
