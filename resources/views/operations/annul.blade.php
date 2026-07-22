@extends('layouts.authenticated')

@section('title', 'Anular Operación — Control de Operaciones')

@section('content')
    <h1>Anular Operación #{{ $operation->id }}</h1>

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

    <table>
        <tr>
            <th>Monto</th>
            <td>{{ $operation->currency }} {{ number_format($operation->amount, 2) }}</td>
        </tr>
        <tr>
            <th>Tipo</th>
            <td>{{ $operation->operationType?->name }}</td>
        </tr>
        <tr>
            <th>Fecha Efectiva</th>
            <td>{{ $operation->effective_at?->format('Y-m-d H:i') }}</td>
        </tr>
        <tr>
            <th>Registrado por</th>
            <td>{{ $operation->user?->username_normalized ?? '—' }}</td>
        </tr>
    </table>

    @if(!$operation->isActive())
        <div class="alert alert-warning">Esta operación ya se encuentra anulada.</div>
    @else
        <form method="POST" action="{{ route('operations.annul', $operation) }}">
            @csrf
            <div>
                <label for="reason">Motivo de Anulación (*)</label>
                <textarea name="reason" id="reason" required maxlength="500" placeholder="Explique el motivo de la anulación">{{ old('reason') }}</textarea>
            </div>
            <div>
                <a href="{{ route('operations.show', $operation) }}">Cancelar</a>
                <button type="submit">Confirmar Anulación</button>
            </div>
        </form>
    @endif
@endsection
