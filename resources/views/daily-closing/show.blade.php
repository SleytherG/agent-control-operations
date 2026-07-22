@extends('layouts.authenticated')

@section('title', 'Detalle de Cierre #' . $closure->id . ' — Control de Operaciones')

@section('content')
<h1>Detalle de Cierre #{{ $closure->id }}</h1>

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

@if($closure->has_pending_confirm)
    @include('daily-closing.components.pending-confirm-warning')
@endif

<div>
    <h2>Resumen del Cierre</h2>
    <table>
        <tr>
            <th>Agente</th>
            <td>{{ $closure->bankAgent?->code ?? '—' }} — {{ $closure->bankAgent?->bank?->name ?? '—' }} — {{ $closure->bankAgent?->store?->name ?? '—' }}</td>
        </tr>
        <tr>
            <th>Fecha del Cierre</th>
            <td>{{ $closure->business_date?->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <th>Estado</th>
            <td>{{ $closure->status }}</td>
        </tr>
        <tr>
            <th>Generado el</th>
            <td>{{ $closure->created_at?->format('Y-m-d H:i') }}</td>
        </tr>
        @if($closure->isConfirmado() || $closure->isReabierto())
            <tr>
                <th>Confirmado por</th>
                <td>{{ $closure->confirmedBy?->username_normalized ?? '—' }}</td>
            </tr>
            <tr>
                <th>Confirmado el</th>
                <td>{{ $closure->confirmed_at?->format('Y-m-d H:i') ?? '—' }}</td>
            </tr>
        @endif
        @if($closure->isReabierto())
            <tr>
                <th>Reabierto por</th>
                <td>{{ $closure->reopenedBy?->username_normalized ?? '—' }}</td>
            </tr>
            <tr>
                <th>Reabierto el</th>
                <td>{{ $closure->reopened_at?->format('Y-m-d H:i') ?? '—' }}</td>
            </tr>
            <tr>
                <th>Motivo de Reapertura</th>
                <td>{{ $closure->reopen_reason ?? '—' }}</td>
            </tr>
        @endif
    </table>
</div>

<div>
    <h2>Métricas</h2>
    <table>
        <tr>
            <th>Operaciones</th>
            <td>{{ $closure->operation_count }}</td>
        </tr>
        <tr>
            <th>Monto Bruto Operado</th>
            <td>{{ number_format($closure->gross_amount, 2) }}</td>
        </tr>
        <tr>
            <th>Entradas</th>
            <td>{{ number_format($closure->cash_in, 2) }}</td>
        </tr>
        <tr>
            <th>Salidas</th>
            <td>{{ number_format($closure->cash_out, 2) }}</td>
        </tr>
        <tr>
            <th>Neto</th>
            <td>
                {{ number_format($closure->net_movement, 2) }}
                @if($closure->has_pending_confirm)
                    <span>(Pendiente de confirmación)</span>
                @endif
            </td>
        </tr>
    </table>
</div>

<div>
    <h2>Desglose por Tipo de Operación</h2>
    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Dirección</th>
                <th>Cantidad</th>
                <th>Monto Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($breakdownByType as $row)
                <tr>
                    <td>{{ $row->name }}</td>
                    <td>{{ $row->cash_direction }}</td>
                    <td>{{ $row->operation_count }}</td>
                    <td>{{ number_format($row->total_amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Sin operaciones.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div>
    <h2>Desglose por Operador</h2>
    <table>
        <thead>
            <tr>
                <th>Operador</th>
                <th>Cantidad</th>
                <th>Monto Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($breakdownByOperator as $row)
                <tr>
                    <td>{{ $row->username_normalized }}</td>
                    <td>{{ $row->operation_count }}</td>
                    <td>{{ number_format($row->total_amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3">Sin operaciones.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div>
    <h2>Operaciones del Cierre</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tipo</th>
                <th>Monto</th>
                <th>Operador</th>
                <th>Fecha Efectiva</th>
            </tr>
        </thead>
        <tbody>
            @forelse($closureOperations as $op)
                <tr>
                    <td>{{ $op->id }}</td>
                    <td>{{ $op->operationType?->name }}</td>
                    <td>{{ $op->currency ?? 'PEN' }} {{ number_format($op->amount, 2) }}</td>
                    <td>{{ $op->user?->username_normalized ?? '—' }}</td>
                    <td>{{ $op->effective_at?->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Sin operaciones consolidadas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($annulledOperations->isNotEmpty())
    <div>
        <h2>Operaciones Anuladas del Periodo</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Monto</th>
                    <th>Operador</th>
                    <th>Anulado por</th>
                    <th>Motivo</th>
                    <th>Fecha Anulación</th>
                </tr>
            </thead>
            <tbody>
                @foreach($annulledOperations as $op)
                    <tr>
                        <td>{{ $op->id }}</td>
                        <td>{{ $op->operationType?->name }}</td>
                        <td>{{ $op->currency ?? 'PEN' }} {{ number_format($op->amount, 2) }}</td>
                        <td>{{ $op->user?->username_normalized ?? '—' }}</td>
                        <td>{{ $op->annulledBy?->username_normalized ?? '—' }}</td>
                        <td>{{ $op->annulment_reason ?? '—' }}</td>
                        <td>{{ $op->annulled_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<div>
    <p>
        <a href="{{ route('daily-closures.index') }}">Volver al Listado</a>

        @can('confirm', $closure)
            @if($closure->isActivo())
                <form method="POST" action="{{ route('daily-closures.confirm', $closure) }}" style="display:inline;">
                    @csrf
                    <button type="submit" onclick="return confirm('¿Confirmar este cierre? Las operaciones quedarán bloqueadas.')">Confirmar Cierre</button>
                </form>
            @endif
        @endcan

        @can('reopen', $closure)
            @if($closure->isConfirmado())
                <button type="button" onclick="document.getElementById('reopen-form').style.display='block'">Reabrir Cierre</button>
            @endif
        @endcan
    </p>
</div>

@can('reopen', $closure)
    @if($closure->isConfirmado())
        <div id="reopen-form" style="display:none;">
            <hr>
            <h2>Reabrir Cierre</h2>
            <form method="POST" action="{{ route('daily-closures.reopen', $closure) }}">
                @csrf
                <div>
                    <label for="reason">Motivo de Reapertura</label>
                    <textarea name="reason" id="reason" required maxlength="500" placeholder="Explique el motivo de la reapertura"></textarea>
                </div>
                <button type="submit">Confirmar Reapertura</button>
                <button type="button" onclick="document.getElementById('reopen-form').style.display='none'">Cancelar</button>
            </form>
        </div>
    @endif
@endcan
@endsection
