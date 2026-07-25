@extends('layouts.authenticated')

@section('title', 'Generar Cierre Diario — Control de Operaciones')

@section('content')
@php
    $agentOptions = [];
    foreach ($agents as $agent) {
        $agentOptions[$agent->id] = $agent->code . ' — ' . ($agent->bank->name ?? 'Sin banco') . ' — ' . ($agent->store->name ?? 'Sin tienda');
    }
@endphp

<div class="form-page form-page--compact">
<div class="form-page-header">
    <h1 class="admin-title">Generar Cierre Diario</h1>
    <p class="form-page-subtitle">Seleccione el agente y la fecha de corte para generar o regenerar el cierre operativo.</p>
</div>

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

<div class="card form-shell">
<div class="card-body">
<form method="POST" action="{{ route('daily-closures.store') }}" class="form-layout form-layout--single">
    @csrf

    <x-ui.select
        label="Agente Bancario"
        name="bank_agent_id"
        :options="$agentOptions"
        :selected="old('bank_agent_id')"
        placeholder="Seleccione un agente"
        :error="$errors->first('bank_agent_id')"
        required="true"
    />

    <x-ui.input
        label="Fecha del Cierre"
        name="business_date"
        id="business_date"
        type="date"
        value="{{ old('business_date', now()->format('Y-m-d')) }}"
        :error="$errors->first('business_date')"
        required="true"
    />

    <div class="form-layout__full">
        <label class="form-label" for="regenerate">Opciones</label>
        <label class="form-check" for="regenerate">
            <input class="form-check-input" type="checkbox" name="regenerate" id="regenerate" value="1" {{ old('regenerate') ? 'checked' : '' }}>
            <span class="form-check-label">
                <strong>Regenerar si ya existe un cierre activo</strong>
                <span>Use esta opcion solo cuando necesite recalcular un cierre vigente para el mismo agente y fecha.</span>
            </span>
        </label>
    </div>

    <div class="form-actions">
        <a href="{{ route('daily-closures.index') }}" class="btn btn--secondary">Volver al Listado</a>
        <x-ui.button variant="primary" type="submit">Generar Cierre</x-ui.button>
    </div>
</form>
</div>
</div>
</div>
@endsection
