@extends('layouts.authenticated')

@section('title', 'Cambiar Contraseña — Control de Operaciones')

@section('content')
    <div class="form-page form-page--compact">
    <div class="form-page-header">
        <h2 class="admin-title">Cambiar Contraseña</h2>
        <p class="form-page-subtitle">Debes actualizar tu contraseña antes de continuar con el resto de modulos.</p>
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
        <form method="POST" action="{{ route('password.change.update') }}" class="form-layout form-layout--single">
            @csrf
            @method('PATCH')

            @unless($restrictedReset)
                <x-ui.input
                    label="Contraseña Actual"
                    name="current_password"
                    type="password"
                    :error="$errors->first('current_password')"
                    required="true"
                    placeholder="••••••••"
                    autocomplete="current-password"
                />
            @endunless

            <x-ui.input
                label="Nueva Contraseña"
                name="password"
                type="password"
                :error="$errors->first('password')"
                required="true"
                placeholder="Minimo 8 caracteres"
                autocomplete="new-password"
            />

            <x-ui.input
                label="Confirmar Nueva Contraseña"
                name="password_confirmation"
                type="password"
                required="true"
                placeholder="Repite la nueva contraseña"
                autocomplete="new-password"
            />

            <div class="form-actions">
                <x-ui.button variant="primary" type="submit">Cambiar Contraseña</x-ui.button>
            </div>
        </form>
        <form method="POST" action="{{ route('logout') }}" class="form-actions">
            @csrf
            <button type="submit" class="btn btn--secondary">Cerrar sesión</button>
        </form>
        </div>
    </div>
    </div>
@endsection
