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
        <x-ui.data-table
            :headers="[
                ['label' => 'Nombre'],
                ['label' => 'Provincias', 'align' => 'center'],
                ['label' => 'Estado', 'align' => 'center'],
                ['label' => 'Acciones', 'align' => 'center'],
            ]"
            :rows="$regions->map(function($region) {
                \$actions = \"<a href='\" . route('admin.regions.show', \$region) . \"' class='btn btn--secondary'>Ver Provincias</a>\";
                if (\$region->is_active) {
                    \$actions .= \"<form action='\" . route('admin.regions.deactivate', \$region) . \"' method='POST' style='display:inline;' onsubmit=\\\"return confirm('Desactivar esta region?');\\\">\";
                    \$actions .= \"<input type='hidden' name='_token' value='\" . csrf_token() . \"'>\";
                    \$actions .= \"<input type='hidden' name='_method' value='DELETE'>\";
                    \$actions .= \"<button type='submit' class='btn btn--danger'>Desactivar</button></form>\";
                }
                return [
                    ['value' => \$region->name],
                    ['value' => \$region->provinces_count, 'align' => 'center'],
                    ['value' => \$region->is_active ? \"<x-ui.badge variant='active'>Activo</x-ui.badge>\" : \"<x-ui.badge variant='inactive'>Inactivo</x-ui.badge>\", 'align' => 'center'],
                    ['value' => \$actions, 'align' => 'center'],
                ];
            })->toArray()"
            emptyMessage="No se encontraron regiones."
        />
        <x-ui.pagination
            :currentPage="$regions->currentPage()"
            :lastPage="$regions->lastPage()"
            :total="$regions->total()"
            :from="$regions->firstItem() ?? 0"
            :to="$regions->lastItem() ?? 0"
        />
    </div>
@endsection
