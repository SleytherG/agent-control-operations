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
        <div class="table-responsive"><table class="data-table">
            <thead><tr><th>Código</th><th>Nombre</th><th class="table-th-center">Estado</th><th class="table-th-center">Acciones</th></tr></thead>
            <tbody>@forelse($banks as $bank)<tr>
                <td>{{ $bank->code }}</td><td>{{ $bank->name }}</td>
                <td class="table-td-center"><x-ui.badge :variant="$bank->is_active ? 'active' : 'inactive'">{{ $bank->is_active ? 'Activo' : 'Inactivo' }}</x-ui.badge></td>
                <td class="table-td-center">
                    <a href="#" class="btn btn--primary" onclick="event.preventDefault(); document.getElementById('edit-bank-{{ $bank->id }}').submit();">Editar</a>
                    <form id="edit-bank-{{ $bank->id }}" action="{{ route('admin.banks.update', $bank) }}" method="POST" style="display:none">@csrf @method('PATCH')</form>
                    @if($bank->is_active)<form action="{{ route('admin.banks.deactivate', $bank) }}" method="POST" style="display:inline" data-confirm="¿Desactivar este banco?">@csrf @method('DELETE')<button type="submit" class="btn btn--danger">Desactivar</button></form>@endif
                </td>
            </tr>@empty<tr><td colspan="4" class="table-empty"><div class="table-empty-icon" aria-hidden="true">&#x1F3E6;</div>No se encontraron bancos.</td></tr>@endforelse</tbody>
        </table></div>
        <x-ui.pagination
            :currentPage="$banks->currentPage()"
            :lastPage="$banks->lastPage()"
            :total="$banks->total()"
            :from="$banks->firstItem() ?? 0"
            :to="$banks->lastItem() ?? 0"
        />
    </div>
@endsection
