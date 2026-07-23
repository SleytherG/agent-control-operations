@extends('layouts.authenticated')

@section('title', 'Detalle de Cierre #' . $closure->id . ' — AgenteFlow')

@php
    $statusBadge = match(true) {
        $closure->isConfirmado() => 'success',
        $closure->isReabierto() => 'warning',
        default => 'blue',
    };
    $statusText = match(true) {
        $closure->isConfirmado() => 'Cierre Confirmado',
        $closure->isReabierto() => 'Cierre Reabierto',
        default => 'Cierre Pendiente',
    };
@endphp

@section('content')
<div class="daily-closing">
    @if(session('status'))
        <div class="alert alert-success" role="alert" style="margin-bottom:var(--space-md);">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" role="alert" style="margin-bottom:var(--space-md);">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="closing-header">
        <div>
            <div class="closing-status-line" style="display:flex;align-items:center;gap:var(--space-sm);margin-bottom:var(--space-xs);">
                <span class="closing-status-dot closing-status-dot--{{ $statusBadge }}" aria-hidden="true" style="width:10px;height:10px;border-radius:50%;display:inline-block;"></span>
                <span class="closing-status-text" style="font-weight:var(--font-weight-medium);">{{ $statusText }}</span>
            </div>
            <h2 class="closing-title" style="margin:0;">Cierre Diario — {{ $closure->bankAgent->code ?? '—' }}</h2>
        </div>
        <div class="closing-actions" style="display:flex;gap:var(--space-sm);">
            @can('confirm', $closure)
                @if($closure->isActivo() || $closure->isReabierto())
                <form method="POST" action="{{ route('daily-closures.confirm', $closure) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn--success btn--sm">Confirmar Cierre</button>
                </form>
                @endif
            @endcan
            @can('reopen', $closure)
                @if($closure->isConfirmado())
                <button class="btn btn--warning btn--sm" onclick="document.getElementById('reopen-modal').classList.add('modal--open')">Reabrir Cierre</button>
                @endif
            @endcan
        </div>
    </div>

    <div class="closing-context-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:var(--space-md);margin:var(--space-lg) 0;">
        <div class="closing-context-card" style="padding:var(--space-md);background:var(--color-surface-container);border-radius:var(--radius-md);">
            <span class="closing-context-label" style="font-size:var(--font-size-label);color:var(--color-on-surface-variant);display:block;">Fecha de Operacion</span>
            <span class="closing-context-value" style="font-weight:var(--font-weight-medium);">{{ $closure->business_date?->format('d M Y') ?? '—' }}</span>
        </div>
        <div class="closing-context-card" style="padding:var(--space-md);background:var(--color-surface-container);border-radius:var(--radius-md);">
            <span class="closing-context-label" style="font-size:var(--font-size-label);color:var(--color-on-surface-variant);display:block;">Tienda</span>
            <span class="closing-context-value" style="font-weight:var(--font-weight-medium);">{{ $closure->bankAgent->store->name ?? '—' }}</span>
        </div>
        <div class="closing-context-card" style="padding:var(--space-md);background:var(--color-surface-container);border-radius:var(--radius-md);">
            <span class="closing-context-label" style="font-size:var(--font-size-label);color:var(--color-on-surface-variant);display:block;">Banco Asignado</span>
            <span class="closing-context-value" style="font-weight:var(--font-weight-medium);">{{ $closure->bankAgent->bank->name ?? '—' }}</span>
        </div>
        <div class="closing-context-card" style="padding:var(--space-md);background:var(--color-surface-container);border-radius:var(--radius-md);">
            <span class="closing-context-label" style="font-size:var(--font-size-label);color:var(--color-on-surface-variant);display:block;">Agente Responsable</span>
            <span class="closing-context-value" style="font-weight:var(--font-weight-medium);">{{ $closure->bankAgent->code ?? '—' }}</span>
        </div>
    </div>

    <div class="closing-kpi-grid" style="display:grid;grid-template-columns:repeat(5,1fr);gap:var(--space-md);margin-bottom:var(--space-lg);">
        <x-ui.metric-card label="Total Ops" :value="$closure->total_operations ?? 0" icon="&#x1F4CB;" />
        <x-ui.metric-card label="Monto Bruto" :value="'S/ ' . number_format((float) ($closure->gross_amount ?? 0), 2)" icon="&#x1F4B0;" />
        <x-ui.metric-card label="Total Entradas" :value="'S/ ' . number_format((float) ($closure->total_cash_in ?? 0), 2)" icon="&#x2198;" variant="accent-green" />
        <x-ui.metric-card label="Total Salidas" :value="'S/ ' . number_format((float) ($closure->total_cash_out ?? 0), 2)" icon="&#x2197;" variant="accent-red" />
        <x-ui.metric-card label="Movimiento Neto" :value="'S/ ' . number_format((float) ($closure->net_movement ?? 0), 2)" icon="&#x1F4BC;" variant="dark" />
    </div>

    @if($closure->has_pending_confirm ?? false)
        <div class="closing-warning-banner" role="alert" style="padding:var(--space-md);background:var(--color-warning-container);border-radius:var(--radius-md);margin-bottom:var(--space-lg);">
            <span aria-hidden="true" style="margin-right:var(--space-sm);">&#x26A0;</span>
            <strong>Operaciones por confirmar:</strong> Existen operaciones con direcciones de caja no confirmadas. Revise antes de proceder con el cierre.
        </div>
    @endif

    @if($breakdownByType && count($breakdownByType) > 0)
    <div class="card" style="margin-bottom:var(--space-lg);">
        <div class="card-header"><h3 class="card-title">Desglose por Tipo de Operacion</h3></div>
        <x-ui.data-table
            :headers="[
                ['label' => 'Tipo'],
                ['label' => 'Volumen', 'align' => 'right'],
                ['label' => 'Monto', 'align' => 'right'],
            ]"
            :rows="collect($breakdownByType)->map(function($row) {
                return [
                    ['value' => $row->name],
                    ['value' => $row->operation_count, 'align' => 'right'],
                    ['value' => 'S/ ' . number_format((float) $row->total_amount, 2), 'align' => 'right'],
                ];
            })->toArray()"
        />
    </div>
    @endif

    @if($breakdownByOperator && count($breakdownByOperator) > 0)
    <div class="card" style="margin-bottom:var(--space-lg);">
        <div class="card-header"><h3 class="card-title">Actividad por Operador</h3></div>
        <x-ui.data-table
            :headers="[
                ['label' => 'Operador'],
                ['label' => 'Ops', 'align' => 'right'],
                ['label' => 'Monto', 'align' => 'right'],
            ]"
            :rows="collect($breakdownByOperator)->map(function($row) {
                return [
                    ['value' => $row->username_normalized],
                    ['value' => $row->operation_count, 'align' => 'right'],
                    ['value' => 'S/ ' . number_format((float) $row->total_amount, 2), 'align' => 'right'],
                ];
            })->toArray()"
        />
    </div>
    @endif

    @if($closureOperations && count($closureOperations) > 0)
    <div class="card" style="margin-bottom:var(--space-lg);">
        <div class="card-header"><h3 class="card-title">Operaciones del Cierre</h3></div>
        <x-ui.data-table
            :headers="[
                ['label' => 'ID'],
                ['label' => 'Tipo'],
                ['label' => 'Monto', 'align' => 'right'],
                ['label' => 'Operador'],
            ]"
            :rows="collect($closureOperations)->map(function($op) {
                return [
                    ['value' => '#' . $op->id, 'class' => 'data-mono'],
                    ['value' => $op->operationType->name ?? '—'],
                    ['value' => 'S/ ' . number_format((float) $op->amount, 2), 'align' => 'right'],
                    ['value' => $op->user->username_normalized ?? '—'],
                ];
            })->toArray()"
        />
    </div>
    @endif

    @if($annulledOperations && count($annulledOperations) > 0)
    <div class="card" style="margin-bottom:var(--space-lg);">
        <div class="card-header"><h3 class="card-title">Operaciones Anuladas</h3></div>
        <x-ui.data-table
            :headers="[
                ['label' => 'ID'],
                ['label' => 'Tipo'],
                ['label' => 'Monto', 'align' => 'right'],
                ['label' => 'Motivo'],
            ]"
            :rows="collect($annulledOperations)->map(function($op) {
                return [
                    ['value' => '#' . $op->id, 'class' => 'data-mono'],
                    ['value' => $op->operationType->name ?? '—'],
                    ['value' => 'S/ ' . number_format((float) $op->amount, 2), 'align' => 'right'],
                    ['value' => $op->annulment_reason ?? '—'],
                ];
            })->toArray()"
        />
    </div>
    @endif
</div>

@can('reopen', $closure)
<div class="modal" id="reopen-modal" role="dialog" aria-label="Reabrir cierre" style="display:none;">
    <div class="modal-overlay" onclick="document.getElementById('reopen-modal').classList.remove('modal--open')"></div>
    <div class="modal-content" style="max-width:480px;">
        <div class="modal-header">
            <h3>Reabrir Cierre</h3>
            <button class="modal-close" onclick="document.getElementById('reopen-modal').classList.remove('modal--open')">&times;</button>
        </div>
        <form method="POST" action="{{ route('daily-closures.reopen', $closure) }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Motivo de reapertura</label>
                    <textarea name="reason" class="form-input" rows="3" required placeholder="Describa el motivo..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--secondary btn--sm" onclick="document.getElementById('reopen-modal').classList.remove('modal--open')">Cancelar</button>
                <button type="submit" class="btn btn--warning btn--sm">Reabrir</button>
            </div>
        </form>
    </div>
</div>

<style>
.modal--open { display: flex !important; }
</style>
@endcan
@endsection
