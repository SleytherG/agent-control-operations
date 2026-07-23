@extends('layouts.authenticated')

@section('title', $region->name . ' — Provincias — Control de Operaciones')

@section('content')
    <h2 class="admin-title" style="margin-bottom:var(--space-xs);">{{ $region->name }}</h2>

    @if(session('status'))
        <div class="alert alert-success" role="alert" style="margin: var(--space-md) 0;">{{ session('status') }}</div>
    @endif

    <div style="margin-bottom: var(--space-md);">
        <a href="{{ route('admin.regions.index') }}" class="btn btn--secondary">Volver a Regiones</a>
    </div>

    <div class="card">
        <x-ui.data-table
            :headers="[
                ['label' => 'Nombre'],
                ['label' => 'Estado', 'align' => 'center'],
                ['label' => 'Acciones', 'align' => 'center'],
            ]"
            :rows="$provinces->map(function($province) {
                \$actions = \"<a href='\" . route('admin.provinces.districts.index', \$province) . \"' class='btn btn--secondary'>Ver Distritos</a>\";
                return [
                    ['value' => \$province->name],
                    ['value' => \$province->is_active ? \"<x-ui.badge variant='active'>Activo</x-ui.badge>\" : \"<x-ui.badge variant='inactive'>Inactivo</x-ui.badge>\", 'align' => 'center'],
                    ['value' => \$actions, 'align' => 'center'],
                ];
            })->toArray()"
            emptyMessage="No se encontraron provincias."
        />
        <x-ui.pagination
            :currentPage="$provinces->currentPage()"
            :lastPage="$provinces->lastPage()"
            :total="$provinces->total()"
            :from="$provinces->firstItem() ?? 0"
            :to="$provinces->lastItem() ?? 0"
        />
    </div>
@endsection
