@extends('layouts.authenticated')

@section('title', 'Agentes Bancarios — Control de Operaciones')

@section('content')
    <h2 class="admin-title" style="margin-bottom:var(--space-xs);">Agentes Bancarios</h2>
    <p class="admin-subtitle">Terminales y puntos de atencion bancaria asignados.</p>

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

    <div class="page-toolbar">
        <a href="{{ route('admin.bank-agents.create') }}" class="btn btn--primary">Nuevo Agente</a>

        <form method="GET" action="{{ route('admin.bank-agents.index') }}" class="filter-bar">
            <x-ui.select
                label="Tienda"
                name="store_id"
                :options="$stores->pluck('name', 'id')->toArray()"
                :selected="request('store_id')"
                placeholder="Todas las tiendas"
            />
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
            <div class="filter-bar-actions">
                <x-ui.button variant="secondary" type="submit">Filtrar</x-ui.button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-responsive"><table class="data-table">
            <thead><tr><th>Código</th><th>Terminal</th><th>Tienda</th><th>Banco</th><th class="table-th-center">Estado</th><th class="table-th-center">Acciones</th></tr></thead>
            <tbody>@forelse($agents as $agent)<tr>
                <td>{{ $agent->code }}</td><td>{{ $agent->terminal_code }}</td><td>{{ $agent->store?->name ?? '—' }}</td><td>{{ $agent->bank?->name ?? '—' }}</td>
                <td class="table-td-center"><x-ui.badge :variant="$agent->is_active ? 'active' : 'inactive'">{{ $agent->is_active ? 'Activo' : 'Inactivo' }}</x-ui.badge></td>
                <td class="table-td-center">
                    <a href="#" class="btn btn--primary" onclick="event.preventDefault(); document.getElementById('edit-agent-{{ $agent->id }}').submit();">Editar</a>
                    <form id="edit-agent-{{ $agent->id }}" action="{{ route('admin.bank-agents.update', $agent) }}" method="POST" style="display:none">@csrf @method('PATCH')</form>
                    @if($agent->is_active)<form action="{{ route('admin.bank-agents.deactivate', $agent) }}" method="POST" style="display:inline" data-confirm="¿Desactivar este agente? Se terminarán todas las asignaciones activas.">@csrf @method('DELETE')<button type="submit" class="btn btn--danger">Desactivar</button></form>@endif
                </td>
            </tr>@empty<tr><td colspan="6" class="table-empty"><div class="table-empty-icon" aria-hidden="true">&#x1F3E7;</div>No se encontraron agentes.</td></tr>@endforelse</tbody>
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
