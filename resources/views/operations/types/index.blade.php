@extends('layouts.authenticated')

@section('title', 'Tipos de Operacion — Control de Operaciones')

@section('content')
    <div class="admin-page-header">
        <div>
            <h1 class="admin-title">Tipos de Operacion</h1>
            <p class="admin-subtitle">Clasificacion de transacciones segun su naturaleza y efecto monetario.</p>
        </div>
        <a href="{{ route('admin.operation-types.create') }}" class="btn btn--primary">Nuevo Tipo</a>
    </div>

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

    <form method="GET" action="{{ route('admin.operation-types.index') }}" class="filter-bar filter-bar--standalone">
        <x-ui.input
            label="Nombre"
            name="name"
            value="{{ request('name') }}"
            placeholder="Filtrar por nombre"
        />
        <x-ui.input
            label="Descripción"
            name="description"
            value="{{ request('description') }}"
            placeholder="Filtrar por descripción"
        />
        <x-ui.select
            label="Efectivo"
            name="cash_multiplier"
            :options="['1' => 'Entrada', '-1' => 'Salida', '0' => 'Neutro']"
            :selected="request('cash_multiplier')"
            placeholder="Todos"
        />
        <x-ui.select
            label="Digital"
            name="digital_multiplier"
            :options="['1' => 'Entrada', '-1' => 'Salida', '0' => 'Neutro']"
            :selected="request('digital_multiplier')"
            placeholder="Todos"
        />
        <x-ui.input
            label="Orden"
            name="sort_order"
            value="{{ request('sort_order') }}"
            placeholder="Filtrar por orden"
            type="number"
        />
        <x-ui.select
            label="Estado"
            name="is_active"
            :options="['1' => 'Activo', '0' => 'Inactivo']"
            :selected="request('is_active')"
            placeholder="Todos los estados"
        />
        <div class="filter-bar-actions">
            <a href="{{ route('admin.operation-types.index') }}" class="btn btn--secondary">Limpiar</a>
            <x-ui.button variant="secondary" type="submit">Filtrar</x-ui.button>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive"><table class="data-table">
            <thead><tr><th>Nombre</th><th>Descripción</th><th>Efectivo</th><th>Digital</th><th>Orden</th><th class="table-th-center">Estado</th><th class="table-th-center">Acciones</th></tr></thead>
            <tbody>@forelse($types as $type)<tr>
                <td>{{ $type->name }}</td><td>{{ $type->description ?? '—' }}</td><td>{{ $type->cash_multiplier === 1 ? 'Entrada' : ($type->cash_multiplier === -1 ? 'Salida' : 'Neutro') }}</td><td>{{ $type->digital_multiplier === 1 ? 'Entrada' : ($type->digital_multiplier === -1 ? 'Salida' : 'Neutro') }}</td><td>{{ $type->sort_order }}</td>
                <td class="table-td-center"><x-ui.badge :variant="$type->is_active ? 'active' : 'inactive'">{{ $type->is_active ? 'Activo' : 'Inactivo' }}</x-ui.badge></td>
                <td class="table-td-center"><a href="{{ route('admin.operation-types.edit', $type) }}" class="btn btn--primary">Editar</a>@if($type->is_active)<form action="{{ route('admin.operation-types.destroy', $type) }}" method="POST" style="display:inline" data-confirm="¿Desactivar este tipo?">@csrf @method('DELETE')<button type="submit" class="btn btn--danger">Desactivar</button></form>@endif</td>
            </tr>@empty<tr><td colspan="7" class="table-empty"><div class="table-empty-icon" aria-hidden="true">&#x1F4CB;</div>No se encontraron tipos de operación.</td></tr>@endforelse</tbody>
        </table></div>
        <x-ui.pagination
            :currentPage="$types->currentPage()"
            :lastPage="$types->lastPage()"
            :total="$types->total()"
            :from="$types->firstItem() ?? 0"
            :to="$types->lastItem() ?? 0"
        />
    </div>
@endsection
