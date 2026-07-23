@extends('layouts.authenticated')

@section('title', 'Operadores — Control de Operaciones')

@section('content')
    <h2 class="admin-title" style="margin-bottom:var(--space-xs);">Operadores</h2>
    <p class="admin-subtitle">Usuarios autorizados para registrar operaciones en el sistema.</p>

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
        <a href="{{ route('admin.users.create') }}" class="btn btn--primary">Nuevo Operador</a>

        <form method="GET" action="{{ route('admin.users.index') }}" style="display: flex; gap: var(--space-sm); align-items: flex-end;">
            <x-ui.select
                label="Estado"
                name="status"
                :options="['ACTIVE' => 'Activo', 'INACTIVE' => 'Inactivo']"
                :selected="request('status')"
                placeholder="Todos los estados"
            />
            <x-ui.button variant="secondary" type="submit">Filtrar</x-ui.button>
        </form>
    </div>

    <div class="card">
        <x-ui.data-table
            :headers="[
                ['label' => 'Usuario'],
                ['label' => 'Email'],
                ['label' => 'Estado', 'align' => 'center'],
                ['label' => 'Acciones', 'align' => 'center'],
            ]"
            :rows="$operators->map(function($operator) {
                \$actions = \"<a href='\" . route('admin.users.edit', \$operator) . \"' class='btn btn--primary'>Editar</a>
                    <a href='\" . route('admin.users.assignments.index', \$operator) . \"' class='btn btn--secondary'>Asignaciones</a>\";
                if (\$operator->status->value === 'ACTIVE') {
                    \$actions .= \"<form action='\" . route('admin.users.deactivate-operator', \$operator) . \"' method='POST' style='display:inline;' onsubmit=\\\"return confirm('Desactivar este operador?');\\\">\";
                    \$actions .= \"<input type='hidden' name='_token' value='\" . csrf_token() . \"'>\";
                    \$actions .= \"<input type='hidden' name='_method' value='DELETE'>\";
                    \$actions .= \"<button type='submit' class='btn btn--danger'>Desactivar</button></form>\";
                }
                return [
                    ['value' => \$operator->username_normalized],
                    ['value' => \$operator->email_normalized],
                    ['value' => \$operator->status->value === 'ACTIVE' ? \"<x-ui.badge variant='active'>Activo</x-ui.badge>\" : \"<x-ui.badge variant='inactive'>Inactivo</x-ui.badge>\", 'align' => 'center'],
                    ['value' => \$actions, 'align' => 'center'],
                ];
            })->toArray()"
            emptyMessage="No se encontraron operadores."
        />
        <x-ui.pagination
            :currentPage="$operators->currentPage()"
            :lastPage="$operators->lastPage()"
            :total="$operators->total()"
            :from="$operators->firstItem() ?? 0"
            :to="$operators->lastItem() ?? 0"
        />
    </div>
@endsection
