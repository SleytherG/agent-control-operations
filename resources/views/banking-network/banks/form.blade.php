@extends('layouts.authenticated')

@section('title', $bank->exists ? 'Editar Banco' : 'Nuevo Banco — Control de Operaciones')

@section('content')
    <h2 class="admin-title" style="margin-bottom:var(--space-xs);">{{ $bank->exists ? 'Editar Banco' : 'Nuevo Banco' }}</h2>

    <div class="card" style="max-width: 600px;">
        <form method="POST" action="{{ $bank->exists ? route('admin.banks.update', $bank) : route('admin.banks.store') }}">
            @csrf
            @if($bank->exists)
                @method('PATCH')
            @endif

            <x-ui.input
                label="Codigo"
                name="code"
                value="{{ old('code', $bank->code) }}"
                :error="$errors->first('code')"
                required="true"
                placeholder="Codigo"
            />

            <x-ui.input
                label="Nombre"
                name="name"
                value="{{ old('name', $bank->name) }}"
                :error="$errors->first('name')"
                required="true"
                placeholder="Nombre"
            />

            <div style="display: flex; gap: var(--space-sm); margin-top: var(--space-md);">
                <x-ui.button variant="primary" type="submit">
                    {{ $bank->exists ? 'Actualizar' : 'Crear' }}
                </x-ui.button>
                <a href="{{ route('admin.banks.index') }}" class="btn btn--secondary">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
