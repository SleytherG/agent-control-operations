@extends('layouts.authenticated')

@section('title', $operator->exists ? 'Editar Operador' : 'Nuevo Operador — Control de Operaciones')

@section('content')
    <h2 class="admin-title" style="margin-bottom:var(--space-xs);">{{ $operator->exists ? 'Editar Operador' : 'Nuevo Operador' }}</h2>

    <div class="card" style="max-width: 600px;">
        <form method="POST" action="{{ $operator->exists ? route('admin.users.update', $operator) : route('admin.users.store') }}">
            @csrf
            @if($operator->exists)
                @method('PATCH')
            @endif

            <x-ui.input
                label="Usuario"
                name="username"
                value="{{ old('username', $operator->exists ? $operator->username_normalized : '') }}"
                :error="$errors->first('username')"
                required="true"
                placeholder="Usuario"
            />

            <x-ui.input
                label="Email"
                name="email"
                type="email"
                value="{{ old('email', $operator->exists ? $operator->email_normalized : '') }}"
                :error="$errors->first('email')"
                required="true"
                placeholder="email@ejemplo.com"
            />

            @unless($operator->exists)
                <x-ui.input
                    label="Contraseña"
                    name="password"
                    type="password"
                    :error="$errors->first('password')"
                    required="true"
                    placeholder="Minimo 8 caracteres"
                />
            @endunless

            <div style="display: flex; gap: var(--space-sm); margin-top: var(--space-md);">
                <x-ui.button variant="primary" type="submit">
                    {{ $operator->exists ? 'Actualizar' : 'Crear' }}
                </x-ui.button>
                <a href="{{ route('admin.users.index') }}" class="btn btn--secondary">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
