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

    <div style="margin-bottom: var(--space-md); display: flex; justify-content: space-between; align-items: flex-end; gap: var(--space-md); flex-wrap: wrap;">
        <a href="{{ route('admin.bank-agents.create') }}" class="btn btn--primary">Nuevo Agente</a>

        <form method="GET" action="{{ route('admin.bank-agents.index') }}" style="display: flex; gap: var(--space-sm); align-items: flex-end; flex-wrap: wrap;">
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
            <x-ui.button variant="secondary" type="submit">Filtrar</x-ui.button>
        </form>
    </div>

    <div class="card">
        <x-ui.data-table
            :headers="[
                ['label' => 'Codigo'],
                ['label' => 'Terminal'],
                ['label' => 'Tienda'],
                ['label' => 'Banco'],
                ['label' => 'Estado', 'align' => 'center'],
                ['label' => 'Acciones', 'align' => 'center'],
            ]"
            :rows="$agents->map(function($agent) {
                \$actions = \"<a href='#' onclick=\\\"event.preventDefault(); document.getElementById('edit-agent-{$agent->id}').submit();\\\" class='btn btn--primary'>Editar</a>
                    <form id='edit-agent-{$agent->id}' action='\" . route('admin.bank-agents.update', $agent) . \"' method='POST' style='display:none;'>
                        <input type='hidden' name='_token' value='\" . csrf_token() . \"'>
                        <input type='hidden' name='_method' value='PATCH'>
                    </form>\";
                if (\$agent->is_active) {
                    \$actions .= \"<form action='\" . route('admin.bank-agents.deactivate', $agent) . \"' method='POST' style='display:inline;' onsubmit=\\\"return confirm('Desactivar este agente? Se terminaran todas las asignaciones activas.');\\\">\";
                    \$actions .= \"<input type='hidden' name='_token' value='\" . csrf_token() . \"'>\";
                    \$actions .= \"<input type='hidden' name='_method' value='DELETE'>\";
                    \$actions .= \"<button type='submit' class='btn btn--danger'>Desactivar</button></form>\";
                }
                return [
                    ['value' => \$agent->code],
                    ['value' => \$agent->terminal_code],
                    ['value' => \$agent->store?->name ?? '—'],
                    ['value' => \$agent->bank?->name ?? '—'],
                    ['value' => \$agent->is_active ? \"<x-ui.badge variant='active'>Activo</x-ui.badge>\" : \"<x-ui.badge variant='inactive'>Inactivo</x-ui.badge>\", 'align' => 'center'],
                    ['value' => \$actions, 'align' => 'center'],
                ];
            })->toArray()"
            emptyMessage="No se encontraron agentes."
        />
        <x-ui.pagination
            :currentPage="$agents->currentPage()"
            :lastPage="$agents->lastPage()"
            :total="$agents->total()"
            :from="$agents->firstItem() ?? 0"
            :to="$agents->lastItem() ?? 0"
        />
    </div>
@endsection
