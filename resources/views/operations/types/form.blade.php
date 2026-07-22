@extends('layouts.authenticated')

@section('title', $type->exists ? 'Editar Tipo de Operación' : 'Nuevo Tipo de Operación' . ' — Control de Operaciones')

@section('content')
    <h1>{{ $type->exists ? 'Editar Tipo de Operación' : 'Nuevo Tipo de Operación' }}</h1>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ $type->exists ? route('admin.operation-types.update', $type) : route('admin.operation-types.store') }}">
        @csrf
        @if($type->exists)
            @method('PATCH')
        @endif

        <div>
            <label for="name">Nombre</label>
            <input type="text" name="name" id="name" value="{{ old('name', $type->name) }}" required maxlength="160">
        </div>

        <div>
            <label for="description">Descripción</label>
            <textarea name="description" id="description" maxlength="500">{{ old('description', $type->description) }}</textarea>
        </div>

        <div>
            <label for="bank_id">Banco (vacío = General)</label>
            <select name="bank_id" id="bank_id">
                <option value="">General</option>
                @foreach($banks as $bank)
                    <option value="{{ $bank->id }}" {{ old('bank_id', $type->bank_id) == $bank->id ? 'selected' : '' }}>
                        {{ $bank->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="cash_direction">Dirección de Caja</label>
            <select name="cash_direction" id="cash_direction" required>
                <option value="">Seleccione</option>
                <option value="ENTRADA" {{ old('cash_direction', $type->cash_direction) === 'ENTRADA' ? 'selected' : '' }}>Entrada</option>
                <option value="SALIDA" {{ old('cash_direction', $type->cash_direction) === 'SALIDA' ? 'selected' : '' }}>Salida</option>
                <option value="NEUTRA" {{ old('cash_direction', $type->cash_direction) === 'NEUTRA' ? 'selected' : '' }}>Neutra</option>
                <option value="POR_CONFIRMAR" {{ old('cash_direction', $type->cash_direction) === 'POR_CONFIRMAR' ? 'selected' : '' }}>Por Confirmar</option>
            </select>
        </div>

        <div>
            <a href="{{ route('admin.operation-types.index') }}">Cancelar</a>
            <button type="submit">{{ $type->exists ? 'Actualizar' : 'Crear' }}</button>
        </div>
    </form>
@endsection
