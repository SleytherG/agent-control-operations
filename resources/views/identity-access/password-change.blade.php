@extends('layouts.authenticated')

@section('title', 'Cambiar Contraseña — Control de Operaciones')

@section('content')
    <h2 class="admin-title" style="margin-bottom:var(--space-xs);">Cambiar Contraseña</h2>
    <p class="admin-subtitle">Debes cambiar tu contraseña antes de continuar.</p>

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

    <div class="card" style="max-width: 500px;">
        <form method="POST" action="{{ route('password.change.update') }}">
            @csrf
            @method('PATCH')

            <x-ui.input
                label="Contraseña Actual"
                name="current_password"
                type="password"
                :error="$errors->first('current_password')"
                required="true"
                placeholder="••••••••"
            />

            <x-ui.input
                label="Nueva Contraseña"
                name="password"
                type="password"
                :error="$errors->first('password')"
                required="true"
                placeholder="Minimo 8 caracteres"
            />

            <x-ui.input
                label="Confirmar Nueva Contraseña"
                name="password_confirmation"
                type="password"
                required="true"
                placeholder="Repite la nueva contraseña"
            />

            <div style="margin-top: var(--space-md);">
                <x-ui.button variant="primary" type="submit">Cambiar Contraseña</x-ui.button>
            </div>
        </form>
    </div>
@endsection
