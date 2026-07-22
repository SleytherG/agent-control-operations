@extends('layouts.authenticated')

@section('title', $operator->exists ? 'Editar Operador' : 'Nuevo Operador — Control de Operaciones')

@section('content')
    <h1>{{ $operator->exists ? 'Editar Operador' : 'Nuevo Operador' }}</h1>

    <form method="POST" action="{{ $operator->exists ? route('admin.users.update', $operator) : route('admin.users.store') }}">
        @csrf
        @if($operator->exists)
            @method('PATCH')
        @endif

        <div>
            <label for="username">Usuario</label>
            <input type="text" name="username" id="username" value="{{ old('username', $operator->exists ? $operator->username_normalized : '') }}" required maxlength="100">
            @error('username')<span>{{ $message }}</span>@enderror
        </div>

        <div>
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email', $operator->exists ? $operator->email_normalized : '') }}" required maxlength="254">
            @error('email')<span>{{ $message }}</span>@enderror
        </div>

        @unless($operator->exists)
            <div>
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" required minlength="8">
                @error('password')<span>{{ $message }}</span>@enderror
            </div>
        @endunless

        <button type="submit">{{ $operator->exists ? 'Actualizar' : 'Crear' }}</button>
        <a href="{{ route('admin.users.index') }}">Cancelar</a>
    </form>
@endsection
