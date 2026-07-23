@extends('layouts.authenticated')

@section('title', $store->exists ? 'Editar Tienda' : 'Nueva Tienda — Control de Operaciones')

@section('content')
    <h2 class="admin-title" style="margin-bottom:var(--space-xs);">{{ $store->exists ? 'Editar Tienda' : 'Nueva Tienda' }}</h2>

    <div class="card" style="max-width: 600px;">
        <form method="POST" action="{{ $store->exists ? route('admin.stores.update', $store) : route('admin.stores.store') }}">
            @csrf
            @if($store->exists)
                @method('PATCH')
            @endif

            <x-ui.select
                label="Distrito"
                name="district_id"
                :options="$districts->mapWithKeys(function($d) { return [$d->id => $d->name . ' (' . $d->province?->name . ' - ' . $d->province?->region?->name . ')']; })->toArray()"
                :selected="old('district_id', $store->district_id)"
                :disabled="isset($readonly) && $readonly"
                required="true"
                :error="$errors->first('district_id')"
                placeholder="Seleccione un distrito"
            />

            <x-ui.input
                label="Codigo"
                name="code"
                value="{{ old('code', $store->code) }}"
                :disabled="isset($readonly) && $readonly"
                :error="$errors->first('code')"
                required="true"
                placeholder="Codigo"
            />

            <x-ui.input
                label="Nombre"
                name="name"
                value="{{ old('name', $store->name) }}"
                :disabled="isset($readonly) && $readonly"
                :error="$errors->first('name')"
                required="true"
                placeholder="Nombre"
            />

            <x-ui.input
                label="Direccion"
                name="address"
                value="{{ old('address', $store->address) }}"
                :disabled="isset($readonly) && $readonly"
                :error="$errors->first('address')"
                placeholder="Direccion (opcional)"
            />

            @unless(isset($readonly) && $readonly)
                <div style="display: flex; gap: var(--space-sm); margin-top: var(--space-md);">
                    <x-ui.button variant="primary" type="submit">
                        {{ $store->exists ? 'Actualizar' : 'Crear' }}
                    </x-ui.button>
                    <a href="{{ route('admin.stores.index') }}" class="btn btn--secondary">Cancelar</a>
                </div>
            @endunless
        </form>
    </div>
@endsection
