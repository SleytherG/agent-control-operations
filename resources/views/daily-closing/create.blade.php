@extends('layouts.authenticated')

@section('title', 'Generar Cierre Diario — Control de Operaciones')

@section('content')
<h1>Generar Cierre Diario</h1>

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

<form method="POST" action="{{ route('daily-closures.store') }}">
    @csrf

    <div>
        <label for="bank_agent_id">Agente Bancario</label>
        <select name="bank_agent_id" id="bank_agent_id" required>
            <option value="">Seleccione un agente</option>
            @foreach($agents as $agent)
                <option value="{{ $agent->id }}" {{ old('bank_agent_id') == $agent->id ? 'selected' : '' }}>
                    {{ $agent->code }} — {{ $agent->bank->name ?? 'Sin banco' }} — {{ $agent->store->name ?? 'Sin tienda' }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="business_date">Fecha del Cierre</label>
        <input type="date" name="business_date" id="business_date" value="{{ old('business_date', now()->format('Y-m-d')) }}" required>
    </div>

    <div>
        <label for="regenerate">
            <input type="checkbox" name="regenerate" id="regenerate" value="1" {{ old('regenerate') ? 'checked' : '' }}>
            Regenerar si ya existe un cierre activo
        </label>
    </div>

    <div>
        <button type="submit">Generar Cierre</button>
    </div>
</form>

<p>
    <a href="{{ route('daily-closures.index') }}">Volver al Listado</a>
</p>
@endsection
