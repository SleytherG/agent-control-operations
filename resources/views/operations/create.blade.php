@extends('layouts.authenticated')

@section('title', 'Registrar Operación — Control de Operaciones')

@section('content')
<div style="max-width: 800px; margin: 0 auto; width: 100%;">
    <div style="margin-bottom: var(--space-lg);">
        <h1 style="font-size: var(--font-size-headline-md, 24px); line-height: 32px; letter-spacing: -0.01em; font-weight: var(--font-weight-bold, 600); margin-bottom: 4px;">Registro Rápido</h1>
        <p style="font-size: var(--font-size-body-md, 14px); color: var(--color-on-surface-variant, #45464d); font-weight: var(--font-weight-regular, 400);">Ingrese los detalles de la transacción.</p>
    </div>

    @if(session('status'))
        <div class="alert alert-success" role="alert" style="margin-bottom: var(--space-md);">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" role="alert" style="margin-bottom: var(--space-md);">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="card" style="overflow: hidden;">
        {{-- Context Bar — Stitch: bg-surface-container-low, rounded-t-xl --}}
        <div style="background: var(--color-surface-container-low, #f6f3f5); padding: 12px var(--space-lg); border-bottom: 1px solid var(--color-outline-variant, #c6c6cd); display: flex; flex-wrap: wrap; gap: 16px 24px; align-items: center;">
            @if($agent)
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: var(--font-size-body-sm, 12px); color: var(--color-on-surface-variant, #45464d); font-weight: var(--font-weight-regular, 400);">Agente:</span>
                    <span style="font-size: var(--font-size-data-mono, 14px); font-weight: var(--font-weight-medium, 500); font-family: var(--font-family-mono, monospace);">{{ $agent->code }} — {{ $agent->name }}</span>
                </div>
            @elseif($assignments->count() > 1)
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: var(--font-size-body-sm, 12px); color: var(--color-on-surface-variant, #45464d); font-weight: var(--font-weight-regular, 400);">Agente:</span>
                    <span style="font-size: var(--font-size-data-mono, 14px); font-weight: var(--font-weight-medium, 500); font-family: var(--font-family-mono, monospace);">Múltiples disponibles</span>
                </div>
            @endif
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: var(--font-size-body-sm, 12px); color: var(--color-on-surface-variant, #45464d); font-weight: var(--font-weight-regular, 400);">Sesión:</span>
                <span style="font-size: var(--font-size-data-mono, 14px); font-weight: var(--font-weight-medium, 500); font-family: var(--font-family-mono, monospace);">{{ auth()->user()->username_normalized }}</span>
            </div>
        </div>

        {{-- Form — Stitch: p-lg grid --}}
        <form method="POST" action="{{ route('operations.store') }}" style="padding: var(--space-lg); display: grid; grid-template-columns: 1fr; gap: var(--space-md);">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">
            <input type="hidden" name="effective_at" value="{{ old('effective_at', now()->format('Y-m-d H:i:s')) }}">

            @if($agent)
                <input type="hidden" name="agent_id" value="{{ $agent->id }}">
            @elseif($assignments->count() > 1)
                <div>
                    <label style="display: block; font-size: var(--font-size-label, 12px); font-weight: var(--font-weight-bold, 600); color: var(--color-on-surface-variant, #45464d); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;" for="agent_id">Agente</label>
                    <div style="position: relative;">
                        <select name="agent_id" id="agent_id" required
                            style="width: 100%; appearance: none; background: var(--color-surface-container-lowest, #fff); border: 1px solid var(--color-outline-variant, #c6c6cd); border-radius: 8px; padding: 12px 40px 12px 16px; font-size: var(--font-size-body-md, 14px); color: #1b1b1d; font-weight: var(--font-weight-regular, 400);">
                            <option disabled selected value="">Seleccione agente...</option>
                            @foreach($assignments as $a)
                                <option value="{{ $a->agent->id }}" {{ old('agent_id') == $a->agent->id ? 'selected' : '' }}>
                                    {{ $a->agent->code }} — {{ $a->agent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('agent_id')<span style="color: var(--color-error, #ba1a1a); font-size: var(--font-size-body-sm, 12px); margin-top: 4px;">{{ $message }}</span>@enderror
                </div>
            @endif

            {{-- Hero Amount Input — Stitch: 48px font, right-aligned, PEN badge --}}
            <div>
                <label style="display: block; font-size: var(--font-size-label, 12px); font-weight: var(--font-weight-bold, 600); color: var(--color-on-surface-variant, #45464d); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;" for="amount">Monto de Operación</label>
                <div style="position: relative; display: flex; align-items: center; border: 2px solid {{ $errors->first('amount') ? 'var(--color-error, #ba1a1a)' : 'var(--color-outline-variant, #c6c6cd)' }}; border-radius: 8px; overflow: hidden; background: var(--color-surface-container-lowest, #fff);">
                    <div style="padding: 16px 20px; background: var(--color-surface-container-low, #f6f3f5); border-right: 1px solid var(--color-outline-variant, #c6c6cd); font-size: var(--font-size-headline-md, 24px); font-weight: var(--font-weight-bold, 600); color: var(--color-on-surface-variant, #45464d); white-space: nowrap;">PEN</div>
                    <span style="position: absolute; left: 85px; font-size: var(--font-size-headline-lg, 30px); font-weight: var(--font-weight-bold, 600); color: #1b1b1d; pointer-events: none;">S/</span>
                    <input type="number" name="amount" id="amount" required step="0.01" min="0.01"
                        value="{{ old('amount') }}"
                        placeholder="0.00"
                        style="width: 100%; height: 96px; padding-left: 65px; padding-right: 24px; background: transparent; border: none; text-align: right; font-size: 48px; font-weight: var(--font-weight-bold, 600); color: #1b1b1d; outline: none; line-height: 1; font-family: var(--font-family-mono, monospace);">
                </div>
                @error('amount')<span style="color: var(--color-error, #ba1a1a); font-size: var(--font-size-body-sm, 12px); margin-top: 4px;">{{ $message }}</span>@enderror
            </div>

            {{-- Type Selector — Stitch: full width --}}
            <div>
                <label style="display: block; font-size: var(--font-size-label, 12px); font-weight: var(--font-weight-bold, 600); color: var(--color-on-surface-variant, #45464d); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;" for="operation_type_id">Tipo de Operación</label>
                <div style="position: relative;">
                    <select name="operation_type_id" id="operation_type_id" required
                        style="width: 100%; appearance: none; background: var(--color-surface-container-lowest, #fff); border: 1px solid var(--color-outline-variant, #c6c6cd); border-radius: 8px; padding: 12px 40px 12px 16px; font-size: var(--font-size-body-md, 14px); color: #1b1b1d; font-weight: var(--font-weight-regular, 400);">
                        <option disabled selected value="">Seleccione tipo...</option>
                        @foreach($types as $t)
                            <option value="{{ $t->id }}" {{ old('operation_type_id') == $t->id ? 'selected' : '' }}>
                                {{ $t->name }}
                            </option>
                        @endforeach
                    </select>
                    <span style="position: absolute; right: 12px; top: 12px; pointer-events: none; color: var(--color-on-surface-variant, #45464d); font-size: 16px;">&#x25BE;</span>
                </div>
                @error('operation_type_id')<span style="color: var(--color-error, #ba1a1a); font-size: var(--font-size-body-sm, 12px); margin-top: 4px;">{{ $message }}</span>@enderror
            </div>

            {{-- Divider --}}
            <div style="height: 1px; background: var(--color-outline-variant, #c6c6cd); margin: var(--space-sm) 0;"></div>

            {{-- Optional Fields — Stitch: 2 columns, label-bold --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md);">
                <div>
                    <label style="display: block; font-size: var(--font-size-label, 12px); font-weight: var(--font-weight-bold, 600); color: var(--color-on-surface-variant, #45464d); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;" for="customer_name">
                        Cliente <span style="color: var(--color-outline, #76777d); font-weight: var(--font-weight-regular, 400); text-transform: none; letter-spacing: normal;">(Opcional)</span>
                    </label>
                    <input type="text" name="customer_name" id="customer_name" maxlength="200"
                        value="{{ old('customer_name') }}"
                        placeholder="Nombre o referencia"
                        style="width: 100%; background: var(--color-surface-container-lowest, #fff); border: 1px solid var(--color-outline-variant, #c6c6cd); border-radius: 8px; padding: 12px 16px; font-size: var(--font-size-body-md, 14px); color: #1b1b1d; font-weight: var(--font-weight-regular, 400); outline: none; font-family: var(--font-family-mono, monospace);">
                </div>
                <div>
                    <label style="display: block; font-size: var(--font-size-label, 12px); font-weight: var(--font-weight-bold, 600); color: var(--color-on-surface-variant, #45464d); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;" for="notes">
                        Observaciones <span style="color: var(--color-outline, #76777d); font-weight: var(--font-weight-regular, 400); text-transform: none; letter-spacing: normal;">(Opcional)</span>
                    </label>
                    <textarea name="notes" id="notes" rows="1" maxlength="500"
                        placeholder="Detalles adicionales..."
                        style="width: 100%; background: var(--color-surface-container-lowest, #fff); border: 1px solid var(--color-outline-variant, #c6c6cd); border-radius: 8px; padding: 12px 16px; font-size: var(--font-size-body-md, 14px); color: #1b1b1d; font-weight: var(--font-weight-regular, 400); outline: none; resize: none;">{{ old('notes') }}</textarea>
                </div>
            </div>

            {{-- Submit — Stitch: right-aligned, dark bg, uppercase label-bold --}}
            <div style="margin-top: var(--space-md); padding-top: var(--space-lg); border-top: 1px solid var(--color-outline-variant, #c6c6cd); display: flex; justify-content: flex-end;">
                <button type="submit"
                    style="width: 100%; max-width: 300px; padding: 16px 32px; background: var(--color-primary, #1b1b1d); color: #fff; border: none; border-radius: 8px; font-size: var(--font-size-label, 12px); font-weight: var(--font-weight-bold, 600); text-transform: uppercase; letter-spacing: 0.05em; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    Registrar Operación
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
