@extends('layouts.authenticated')

@section('title', $type->exists ? 'Editar Tipo de Operacion' : 'Nuevo Tipo de Operacion' . ' — Control de Operaciones')

@section('content')
    <h2 class="admin-title" style="margin-bottom:var(--space-xs);">{{ $type->exists ? 'Editar Tipo de Operacion' : 'Nuevo Tipo de Operacion' }}</h2>

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

    <div class="card" style="max-width: 600px;">
        <form method="POST" action="{{ $type->exists ? route('admin.operation-types.update', $type) : route('admin.operation-types.store') }}">
            @csrf
            @if($type->exists)
                @method('PATCH')
            @endif

            <x-ui.input
                label="Nombre"
                name="name"
                value="{{ old('name', $type->name) }}"
                required="true"
                placeholder="Nombre"
            />

            <div class="form-group">
                <label class="form-label" for="description">Descripcion</label>
                <textarea name="description" id="description" class="form-input" rows="3" maxlength="500" placeholder="Descripcion (opcional)">{{ old('description', $type->description) }}</textarea>
            </div>

            <x-ui.select
                label="Banco (vacio = General)"
                name="bank_id"
                :options="$banks->pluck('name', 'id')->toArray()"
                :selected="old('bank_id', $type->bank_id)"
                placeholder="General"
            />

            <x-ui.select
                label="Direccion de Caja"
                name="cash_direction"
                :options="['ENTRADA' => 'Entrada', 'SALIDA' => 'Salida', 'NEUTRA' => 'Neutra', 'POR_CONFIRMAR' => 'Por Confirmar']"
                :selected="old('cash_direction', $type->cash_direction)"
                required="true"
                placeholder="Seleccione"
            />

            <div style="display: flex; gap: var(--space-sm); margin-top: var(--space-md);">
                <a href="{{ route('admin.operation-types.index') }}" class="btn btn--secondary">Cancelar</a>
                <x-ui.button variant="primary" type="submit">
                    {{ $type->exists ? 'Actualizar' : 'Crear' }}
                </x-ui.button>
            </div>
        </form>
    </div>
@endsection
