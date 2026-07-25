@extends('layouts.authenticated')

@section('title', $store->exists ? 'Editar Tienda' : 'Nueva Tienda — Control de Operaciones')

@section('content')
    <div class="form-page">
        <div class="form-page-header">
            <h2 class="admin-title">{{ $store->exists ? 'Editar Tienda' : 'Nueva Tienda' }}</h2>
            <p class="form-page-subtitle">Mantenga la estructura territorial y los datos base de la tienda con un formulario uniforme.</p>
        </div>

        <div class="card form-shell">
            <div class="card-body">
                <form method="POST" action="{{ $store->exists ? route('admin.stores.update', $store) : route('admin.stores.store') }}" class="form-layout">
            @csrf
            @if($store->exists)
                @method('PATCH')
            @endif

            <div class="form-layout__full">
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
            </div>

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
                <div class="form-actions">
                    <a href="{{ route('admin.stores.index') }}" class="btn btn--secondary">Cancelar</a>
                    <x-ui.button variant="primary" type="submit">
                        {{ $store->exists ? 'Actualizar' : 'Crear' }}
                    </x-ui.button>
                </div>
            @endunless
        </form>
            </div>
        </div>
    </div>
@endsection
