@extends('layouts.authenticated')

@section('title', $bank->exists ? 'Editar Banco' : 'Nuevo Banco — Control de Operaciones')

@section('content')
    <h1>{{ $bank->exists ? 'Editar Banco' : 'Nuevo Banco' }}</h1>

    <form method="POST" action="{{ $bank->exists ? route('admin.banks.update', $bank) : route('admin.banks.store') }}">
        @csrf
        @if($bank->exists)
            @method('PATCH')
        @endif

        <div>
            <label for="code">Código</label>
            <input type="text" name="code" id="code" value="{{ old('code', $bank->code) }}" required maxlength="20">
            @error('code')<span>{{ $message }}</span>@enderror
        </div>

        <div>
            <label for="name">Nombre</label>
            <input type="text" name="name" id="name" value="{{ old('name', $bank->name) }}" required maxlength="200">
            @error('name')<span>{{ $message }}</span>@enderror
        </div>

        <button type="submit">{{ $bank->exists ? 'Actualizar' : 'Crear' }}</button>
        <a href="{{ route('admin.banks.index') }}">Cancelar</a>
    </form>
@endsection
