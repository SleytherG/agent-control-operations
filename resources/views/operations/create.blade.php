@extends('layouts.authenticated')

@section('title', 'Registrar Operación — Control de Operaciones')

@section('content')
    <h1>Registrar Operación</h1>

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

    <form method="POST" action="{{ route('operations.store') }}">
        @csrf

        <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

        <div>
            <label for="bank_agent_id">Agente Bancario</label>
            <select name="bank_agent_id" id="bank_agent_id" required>
                <option value="">Seleccione un agente</option>
                @foreach($assignments as $assignment)
                    <option value="{{ $assignment->bank_agent_id }}" {{ old('bank_agent_id') == $assignment->bank_agent_id ? 'selected' : '' }}>
                        {{ $assignment->bankAgent->bank->name }} — {{ $assignment->bankAgent->store->name }} ({{ $assignment->bankAgent->code }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="operation_type_id">Tipo de Operación</label>
            <select name="operation_type_id" id="operation_type_id" required>
                <option value="">Seleccione un tipo</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}" {{ old('operation_type_id') == $type->id ? 'selected' : '' }}>
                        {{ $type->name }} @if($type->bank_id) ({{ $type->bank->name }}) @else (General) @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="amount">Monto</label>
            <input type="number" name="amount" id="amount" step="0.01" min="0.01" value="{{ old('amount') }}" required>
        </div>

        <div>
            <label for="currency">Moneda</label>
            <input type="text" name="currency" id="currency" value="{{ old('currency', 'PEN') }}" maxlength="3">
        </div>

        <div>
            <label for="effective_at">Fecha Efectiva</label>
            <input type="datetime-local" name="effective_at" id="effective_at" value="{{ old('effective_at', now()->format('Y-m-d\TH:i')) }}" required>
        </div>

        <div>
            <label for="reference">Referencia</label>
            <input type="text" name="reference" id="reference" value="{{ old('reference') }}" maxlength="100">
        </div>

        <div>
            <label for="observation">Observación</label>
            <textarea name="observation" id="observation" maxlength="500">{{ old('observation') }}</textarea>
        </div>

        <div>
            <button type="submit">Registrar Operación</button>
        </div>
    </form>
@endsection
