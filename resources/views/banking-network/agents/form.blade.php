@extends('layouts.authenticated')

@section('title', $agent->exists ? 'Editar Agente' : 'Nuevo Agente — Control de Operaciones')

@section('content')
    <h2 class="admin-title" style="margin-bottom:var(--space-xs);">{{ $agent->exists ? 'Editar Agente' : 'Nuevo Agente' }}</h2>

    <div class="card" style="max-width: 600px;">
        <form method="POST" action="{{ $agent->exists ? route('admin.bank-agents.update', $agent) : route('admin.bank-agents.store') }}">
            @csrf
            @if($agent->exists)
                @method('PATCH')
            @endif

            <x-ui.select
                label="Tienda"
                name="store_id"
                :options="$stores->pluck('name', 'id')->toArray()"
                :selected="old('store_id', $agent->store_id)"
                required="true"
                :error="$errors->first('store_id')"
                placeholder="Seleccione una tienda"
            />

            <x-ui.select
                label="Banco"
                name="bank_id"
                :options="$banks->pluck('name', 'id')->toArray()"
                :selected="old('bank_id', $agent->bank_id)"
                required="true"
                :error="$errors->first('bank_id')"
                placeholder="Seleccione un banco"
            />

            <x-ui.input
                label="Codigo"
                name="code"
                value="{{ old('code', $agent->code) }}"
                :error="$errors->first('code')"
                required="true"
                placeholder="Codigo"
            />

            <x-ui.input
                label="Codigo de Terminal"
                name="terminal_code"
                value="{{ old('terminal_code', $agent->terminal_code) }}"
                :error="$errors->first('terminal_code')"
                placeholder="Codigo de terminal"
            />

            <div style="display: flex; gap: var(--space-sm); margin-top: var(--space-md);">
                <x-ui.button variant="primary" type="submit">
                    {{ $agent->exists ? 'Actualizar' : 'Crear' }}
                </x-ui.button>
                <a href="{{ route('admin.bank-agents.index') }}" class="btn btn--secondary">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
