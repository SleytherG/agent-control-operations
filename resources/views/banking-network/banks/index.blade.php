@extends('layouts.authenticated')

@section('title', 'Bancos — Control de Operaciones')

@section('content')
    <h2 class="admin-title" style="margin-bottom:var(--space-xs);">Bancos</h2>
    <p class="admin-subtitle">Entidades financieras registradas en el sistema.</p>

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

    <div style="margin-bottom: var(--space-md);">
        <a href="{{ route('admin.banks.create') }}" class="btn btn--primary">Nuevo Banco</a>
    </div>

    <div class="card">
        <x-ui.data-table
            :headers="[
                ['label' => 'Codigo'],
                ['label' => 'Nombre'],
                ['label' => 'Estado', 'align' => 'center'],
                ['label' => 'Acciones', 'align' => 'center'],
            ]"
            :rows="$banks->map(function($bank) {
                \$actions = \"<a href='#' onclick=\\\"event.preventDefault(); document.getElementById('edit-bank-{$bank->id}').submit();\\\" class='btn btn--primary'>Editar</a>
                    <form id='edit-bank-{$bank->id}' action='\" . route('admin.banks.update', $bank) . \"' method='POST' style='display:none;'>
                        <input type='hidden' name='_token' value='\" . csrf_token() . \"'>
                        <input type='hidden' name='_method' value='PATCH'>
                    </form>\";
                if (\$bank->is_active) {
                    \$actions .= \"<form action='\" . route('admin.banks.deactivate', $bank) . \"' method='POST' style='display:inline;' onsubmit=\\\"return confirm('Desactivar este banco?');\\\">\";
                    \$actions .= \"<input type='hidden' name='_token' value='\" . csrf_token() . \"'>\";
                    \$actions .= \"<input type='hidden' name='_method' value='DELETE'>\";
                    \$actions .= \"<button type='submit' class='btn btn--danger'>Desactivar</button></form>\";
                }
                return [
                    ['value' => \$bank->code],
                    ['value' => \$bank->name],
                    ['value' => \$bank->is_active ? \"<x-ui.badge variant='active'>Activo</x-ui.badge>\" : \"<x-ui.badge variant='inactive'>Inactivo</x-ui.badge>\", 'align' => 'center'],
                    ['value' => \$actions, 'align' => 'center'],
                ];
            })->toArray()"
            emptyMessage="No se encontraron bancos."
        />
        <x-ui.pagination
            :currentPage="$banks->currentPage()"
            :lastPage="$banks->lastPage()"
            :total="$banks->total()"
            :from="$banks->firstItem() ?? 0"
            :to="$banks->lastItem() ?? 0"
        />
    </div>
@endsection
