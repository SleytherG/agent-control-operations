@extends('layouts.authenticated')

@section('title', $bank->exists ? 'Editar Banco' : 'Nuevo Banco — Control de Operaciones')

@section('content')
    <div class="form-page form-page--compact">
        <div class="form-page-header">
            <h2 class="admin-title">{{ $bank->exists ? 'Editar Banco' : 'Nuevo Banco' }}</h2>
            <p class="form-page-subtitle">Cree o actualice bancos bajo el mismo lenguaje visual del resto de formularios administrativos.</p>
        </div>

        <div class="card form-shell">
            <div class="card-body">
                <form method="POST" action="{{ $bank->exists ? route('admin.banks.update', $bank) : route('admin.banks.store') }}" class="form-layout form-layout--single">
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

            <div class="form-actions">
                <a href="{{ route('admin.banks.index') }}" class="btn btn--secondary">Cancelar</a>
                <x-ui.button variant="primary" type="submit">
                    {{ $bank->exists ? 'Actualizar' : 'Crear' }}
                </x-ui.button>
            </div>
        </form>
            </div>
        </div>
    </div>
@endsection
