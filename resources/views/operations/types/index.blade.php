@extends('layouts.authenticated')

@section('title', 'Tipos de Operacion — Control de Operaciones')

@section('content')
    <h2 class="admin-title" style="margin-bottom:var(--space-xs);">Tipos de Operacion</h2>
    <p class="admin-subtitle">Clasificacion de transacciones segun su naturaleza y flujo de caja.</p>

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
        <a href="{{ route('admin.operation-types.create') }}" class="btn btn--primary">Nuevo Tipo</a>

        <form method="GET" action="{{ route('admin.operation-types.index') }}" style="display: flex; gap: var(--space-sm); align-items: flex-end; flex-wrap: wrap;">
            <x-ui.select
                label="Banco"
                name="bank_id"
                :options="$banks->pluck('name', 'id')->toArray()"
                :selected="request('bank_id')"
                placeholder="Todos los bancos"
            />
            <x-ui.select
                label="Estado"
                name="is_active"
                :options="['1' => 'Activo', '0' => 'Inactivo']"
                :selected="request('is_active')"
                placeholder="Todos los estados"
            />
            <x-ui.button variant="secondary" type="submit">Filtrar</x-ui.button>
            <a href="{{ route('admin.operation-types.index', ['general' => 1]) }}" class="btn btn--secondary">Solo Generales</a>
        </form>
    </div>

    <div class="card">
        <x-ui.data-table
            :headers="[
                ['label' => 'Nombre'],
                ['label' => 'Descripcion'],
                ['label' => 'Direccion'],
                ['label' => 'Banco'],
                ['label' => 'Estado', 'align' => 'center'],
                ['label' => 'Acciones', 'align' => 'center'],
            ]"
            :rows="$types->map(function($type) {
                \$actions = \"<a href='\" . route('admin.operation-types.edit', \$type) . \"' class='btn btn--primary'>Editar</a>\";
                if (\$type->is_active) {
                    \$actions .= \"<form action='\" . route('admin.operation-types.destroy', \$type) . \"' method='POST' style='display:inline;' onsubmit=\\\"return confirm('Desactivar este tipo?');\\\">\";
                    \$actions .= \"<input type='hidden' name='_token' value='\" . csrf_token() . \"'>\";
                    \$actions .= \"<input type='hidden' name='_method' value='DELETE'>\";
                    \$actions .= \"<button type='submit' class='btn btn--danger'>Desactivar</button></form>\";
                }
                return [
                    ['value' => \$type->name],
                    ['value' => \$type->description ?? '—'],
                    ['value' => \$type->cash_direction],
                    ['value' => \$type->bank?->name ?? 'General'],
                    ['value' => \$type->is_active ? \"<x-ui.badge variant='active'>Activo</x-ui.badge>\" : \"<x-ui.badge variant='inactive'>Inactivo</x-ui.badge>\", 'align' => 'center'],
                    ['value' => \$actions, 'align' => 'center'],
                ];
            })->toArray()"
            emptyMessage="No se encontraron tipos de operacion."
        />
        <x-ui.pagination
            :currentPage="$types->currentPage()"
            :lastPage="$types->lastPage()"
            :total="$types->total()"
            :from="$types->firstItem() ?? 0"
            :to="$types->lastItem() ?? 0"
        />
    </div>
@endsection
