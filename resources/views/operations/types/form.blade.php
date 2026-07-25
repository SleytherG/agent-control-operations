@extends('layouts.authenticated')

@section('title', $type->exists ? 'Editar Tipo de Operacion' : 'Nuevo Tipo de Operacion' . ' — Control de Operaciones')

@section('content')
    <div class="form-page">
        <div class="form-page-header">
            <h2 class="admin-title">{{ $type->exists ? 'Editar Tipo de Operacion' : 'Nuevo Tipo de Operacion' }}</h2>
            <p class="form-page-subtitle">Defina el comportamiento monetario y visual de cada tipo de transaccion con una maquetacion uniforme.</p>
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

    <div class="card form-shell">
        <div class="card-body">
            <form method="POST" action="{{ $type->exists ? route('admin.operation-types.update', $type) : route('admin.operation-types.store') }}" class="form-layout">
            @csrf
            @if($type->exists)
                @method('PATCH')
            @endif

            <div class="form-layout__full">
                <x-ui.input
                    label="Nombre"
                    name="name"
                    value="{{ old('name', $type->name) }}"
                    required="true"
                    placeholder="Nombre"
                    :error="$errors->first('name')"
                />
            </div>

            <div class="form-group form-layout__full">
                <label class="form-label" for="description">Descripcion</label>
                <textarea name="description" id="description" class="form-input" rows="3" maxlength="500" placeholder="Descripcion (opcional)">{{ old('description', $type->description) }}</textarea>
            </div>

            <x-ui.select
                label="Efecto sobre Efectivo"
                name="cash_multiplier"
                :options="['1' => 'Entrada (+1)', '-1' => 'Salida (-1)', '0' => 'Sin efecto (0)']"
                :selected="old('cash_multiplier', $type->cash_multiplier ?? '0')"
                required="true"
                :error="$errors->first('cash_multiplier')"
                placeholder="Seleccione"
            />

            <x-ui.select
                label="Efecto sobre Saldo Digital"
                name="digital_multiplier"
                :options="['1' => 'Entrada (+1)', '-1' => 'Salida (-1)', '0' => 'Sin efecto (0)']"
                :selected="old('digital_multiplier', $type->digital_multiplier ?? '0')"
                required="true"
                :error="$errors->first('digital_multiplier')"
                placeholder="Seleccione"
            />

            <x-ui.input
                label="Orden"
                name="sort_order"
                value="{{ old('sort_order', $type->sort_order ?? 0) }}"
                type="number"
                placeholder="0"
                :error="$errors->first('sort_order')"
            />

            <div class="form-actions">
                <a href="{{ route('admin.operation-types.index') }}" class="btn btn--secondary">Cancelar</a>
                <x-ui.button variant="primary" type="submit">
                    {{ $type->exists ? 'Actualizar' : 'Crear' }}
                </x-ui.button>
            </div>
        </form>
        </div>
    </div>
    </div>
@endsection
