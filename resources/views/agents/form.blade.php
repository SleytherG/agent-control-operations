@extends('layouts.authenticated')

@section('title', $agent->exists ? 'Editar Agente' : 'Nuevo Agente — Control de Operaciones')

@section('content')
    <div class="form-page">
        <div class="form-page-header">
            <h2 class="admin-title">{{ $agent->exists ? 'Editar Agente' : 'Nuevo Agente' }}</h2>
            <p class="form-page-subtitle">Registre y mantenga los puntos fisicos de operacion con una estructura uniforme.</p>
        </div>

        <div class="card form-shell">
            <div class="card-body">
                <form method="POST" action="{{ $agent->exists ? route('admin.agents.update', $agent) : route('admin.agents.store') }}" class="form-layout">
            @csrf
            @if($agent->exists)
                @method('PATCH')
            @endif

            <x-ui.input
                label="Código"
                name="code"
                value="{{ old('code', $agent->code) }}"
                :error="$errors->first('code')"
                required="true"
                placeholder="Código"
            />

            <x-ui.input
                label="Nombre"
                name="name"
                value="{{ old('name', $agent->name) }}"
                :error="$errors->first('name')"
                required="true"
                placeholder="Nombre del agente"
            />

            <x-ui.input
                label="Ciudad"
                name="city"
                value="{{ old('city', $agent->city) }}"
                :error="$errors->first('city')"
                required="true"
                placeholder="Ciudad"
            />

            <x-ui.input
                label="Región"
                name="region"
                value="{{ old('region', $agent->region) }}"
                :error="$errors->first('region')"
                placeholder="Región"
            />

            <x-ui.input
                label="Provincia"
                name="province"
                value="{{ old('province', $agent->province) }}"
                :error="$errors->first('province')"
                placeholder="Provincia"
            />

            <x-ui.input
                label="Distrito"
                name="district"
                value="{{ old('district', $agent->district) }}"
                :error="$errors->first('district')"
                placeholder="Distrito"
            />

            <x-ui.input
                label="Dirección"
                name="address"
                value="{{ old('address', $agent->address) }}"
                :error="$errors->first('address')"
                placeholder="Dirección física"
            />

            <div class="form-group form-layout__full">
                <label class="form-label" for="description">Descripción</label>
                <textarea name="description" id="description" class="form-input" rows="3" maxlength="500" placeholder="Notas internas">{{ old('description', $agent->description) }}</textarea>
                @if($errors->first('description'))
                    <span class="form-error">{{ $errors->first('description') }}</span>
                @endif
            </div>

            <div class="form-actions">
                <a href="{{ route('admin.agents.index') }}" class="btn btn--secondary">Cancelar</a>
                <x-ui.button variant="primary" type="submit">
                    {{ $agent->exists ? 'Actualizar' : 'Crear' }}
                </x-ui.button>
            </div>
        </form>
            </div>
        </div>
    </div>
@endsection
