@extends('layouts.authenticated')

@section('title', 'Cambiar Contraseña — Control de Operaciones')

@section('content')
    <h1>Cambiar Contraseña</h1>
    <p>Debes cambiar tu contraseña antes de continuar.</p>

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

    <form method="POST" action="{{ route('password.change.update') }}">
        @csrf
        @method('PATCH')

        <div>
            <label for="current_password">Contraseña Actual</label>
            <input type="password" name="current_password" id="current_password" required>
            @error('current_password')<span>{{ $message }}</span>@enderror
        </div>

        <div>
            <label for="password">Nueva Contraseña</label>
            <input type="password" name="password" id="password" required minlength="8">
            @error('password')<span>{{ $message }}</span>@enderror
        </div>

        <div>
            <label for="password_confirmation">Confirmar Nueva Contraseña</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8">
        </div>

        <button type="submit">Cambiar Contraseña</button>
    </form>
@endsection
