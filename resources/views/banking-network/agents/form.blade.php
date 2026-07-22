@extends('layouts.authenticated')

@section('title', $agent->exists ? 'Editar Agente' : 'Nuevo Agente — Control de Operaciones')

@section('content')
    <h1>{{ $agent->exists ? 'Editar Agente' : 'Nuevo Agente' }}</h1>

    <form method="POST" action="{{ $agent->exists ? route('admin.bank-agents.update', $agent) : route('admin.bank-agents.store') }}">
        @csrf
        @if($agent->exists)
            @method('PATCH')
        @endif

        <div>
            <label for="store_id">Tienda</label>
            <select name="store_id" id="store_id" required>
                <option value="">Seleccione una tienda</option>
                @foreach($stores as $store)
                    <option value="{{ $store->id }}" {{ old('store_id', $agent->store_id) == $store->id ? 'selected' : '' }}>
                        {{ $store->name }}
                    </option>
                @endforeach
            </select>
            @error('store_id')<span>{{ $message }}</span>@enderror
        </div>

        <div>
            <label for="bank_id">Banco</label>
            <select name="bank_id" id="bank_id" required>
                <option value="">Seleccione un banco</option>
                @foreach($banks as $bank)
                    <option value="{{ $bank->id }}" {{ old('bank_id', $agent->bank_id) == $bank->id ? 'selected' : '' }}>
                        {{ $bank->name }}
                    </option>
                @endforeach
            </select>
            @error('bank_id')<span>{{ $message }}</span>@enderror
        </div>

        <div>
            <label for="code">Código</label>
            <input type="text" name="code" id="code" value="{{ old('code', $agent->code) }}" required maxlength="80">
            @error('code')<span>{{ $message }}</span>@enderror
        </div>

        <div>
            <label for="terminal_code">Código de Terminal</label>
            <input type="text" name="terminal_code" id="terminal_code" value="{{ old('terminal_code', $agent->terminal_code) }}" maxlength="40">
            @error('terminal_code')<span>{{ $message }}</span>@enderror
        </div>

        <button type="submit">{{ $agent->exists ? 'Actualizar' : 'Crear' }}</button>
        <a href="{{ route('admin.bank-agents.index') }}">Cancelar</a>
    </form>
@endsection
