@extends('layouts.authenticated')

@section('title', 'Agentes — Control de Operaciones')

@section('content')
    <div class="admin-page-header">
        <div>
            <h1 class="admin-title">Agentes</h1>
            <p class="admin-subtitle">Puntos físicos de operación registrados en el sistema.</p>
        </div>
        <a href="{{ route('admin.agents.create') }}" class="btn btn--primary">Nuevo Agente</a>
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

    <form method="GET" action="{{ route('admin.agents.index') }}" class="filter-bar filter-bar--standalone">
        <x-ui.input
            label="Código"
            name="code"
            value="{{ request('code') }}"
            placeholder="Filtrar por código"
        />
        <x-ui.input
            label="Nombre"
            name="name"
            value="{{ request('name') }}"
            placeholder="Filtrar por nombre"
        />
        <x-ui.input
            label="Ciudad"
            name="city"
            value="{{ request('city') }}"
            placeholder="Filtrar por ciudad"
        />
        <x-ui.select
            label="Estado"
            name="is_active"
            :options="['1' => 'Activo', '0' => 'Inactivo']"
            :selected="request('is_active')"
            placeholder="Todos los estados"
        />
        <div class="filter-bar-actions">
            <a href="{{ route('admin.agents.index') }}" class="btn btn--secondary">Limpiar</a>
            <x-ui.button variant="secondary" type="submit">Filtrar</x-ui.button>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive"><table class="data-table">
            <thead><tr><th>Código</th><th>Nombre</th><th>Ciudad</th><th class="table-th-center">Estado</th><th class="table-th-center">Acciones</th></tr></thead>
            <tbody>@forelse($agents as $agent)<tr>
                <td>{{ $agent->code }}</td><td>{{ $agent->name }}</td><td>{{ $agent->city }}</td>
                <td class="table-td-center"><x-ui.badge :variant="$agent->is_active ? 'active' : 'inactive'">{{ $agent->is_active ? 'Activo' : 'Inactivo' }}</x-ui.badge></td>
                <td class="table-td-center">
                    <a href="{{ route('admin.agents.edit', $agent) }}" class="btn btn--primary">Editar</a>
                    @if($agent->is_active)<form action="{{ route('admin.agents.deactivate', $agent) }}" method="POST" style="display:inline" data-confirm="¿Desactivar este agente? Se terminarán todas las asignaciones activas.">@csrf @method('DELETE')<button type="submit" class="btn btn--danger">Desactivar</button></form>@endif
                </td>
            </tr>@empty<tr><td colspan="5" class="table-empty"><div class="table-empty-icon" aria-hidden="true">&#x1F3E6;</div>No se encontraron agentes.</td></tr>@endforelse</tbody>
        </table></div>
        <x-ui.pagination
            :currentPage="$agents->currentPage()"
            :lastPage="$agents->lastPage()"
            :total="$agents->total()"
            :from="$agents->firstItem() ?? 0"
            :to="$agents->lastItem() ?? 0"
        />
    </div>
@endsection
