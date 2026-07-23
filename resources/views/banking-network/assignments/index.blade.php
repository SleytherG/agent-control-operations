@extends('layouts.authenticated')

@section('title', 'Asignaciones de ' . $user->username_normalized . ' — Control de Operaciones')

@section('content')
    <h2 class="admin-title" style="margin-bottom:var(--space-xs);">Asignaciones de {{ $user->username_normalized }}</h2>
    <p class="admin-subtitle">Agentes bancarios asignados al operador.</p>

    @if(session('status'))
        <div class="alert alert-success" role="alert" style="margin: var(--space-md) 0;">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" role="alert" style="margin: var(--space-md) 0;">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div style="margin-bottom: var(--space-md);">
        <a href="{{ route('admin.users.index') }}" class="btn btn--secondary">Volver a Operadores</a>
    </div>

    <div class="card" style="margin-bottom: var(--space-lg); max-width: 600px;">
        <h3 style="margin-bottom: var(--space-sm);">Asignar Agente</h3>
        <form method="POST" action="{{ route('admin.users.assignments.store', $user) }}">
            @csrf

            @php
                $agentOptions = \App\Modules\BankingNetwork\Models\BankAgent::with(['store', 'bank'])
                    ->where('organization_id', auth()->user()->organization_id)
                    ->where('is_active', true)
                    ->orderBy('code')
                    ->get()
                    ->mapWithKeys(function($a) {
                        return [$a->id => $a->code . ' — ' . $a->store?->name . ' / ' . $a->bank?->name];
                    })
                    ->toArray();
            @endphp

            <x-ui.select
                label="Agente"
                name="bank_agent_id"
                :options="$agentOptions"
                required="true"
                :error="$errors->first('bank_agent_id')"
                placeholder="Seleccione un agente"
            />

            <div style="margin-top: var(--space-sm);">
                <x-ui.button variant="primary" type="submit">Asignar</x-ui.button>
            </div>
        </form>
    </div>

    <div class="card">
        <x-ui.data-table
            :headers="[
                ['label' => 'Agente'],
                ['label' => 'Tienda'],
                ['label' => 'Banco'],
                ['label' => 'Asignado'],
                ['label' => 'Desasignado'],
                ['label' => 'Estado', 'align' => 'center'],
                ['label' => 'Acciones', 'align' => 'center'],
            ]"
            :rows="$assignments->map(function($assignment) {
                \$actions = '';
                if (\$assignment->is_active) {
                    \$actions .= \"<form action='\" . route('admin.assignments.destroy', \$assignment) . \"' method='POST' style='display:inline;' onsubmit=\\\"return confirm('Desasignar este operador?');\\\">\";
                    \$actions .= \"<input type='hidden' name='_token' value='\" . csrf_token() . \"'>\";
                    \$actions .= \"<input type='hidden' name='_method' value='DELETE'>\";
                    \$actions .= \"<button type='submit' class='btn btn--danger'>Desasignar</button></form>\";
                }
                return [
                    ['value' => \$assignment->bankAgent->code],
                    ['value' => \$assignment->bankAgent->store?->name ?? '—'],
                    ['value' => \$assignment->bankAgent->bank?->name ?? '—'],
                    ['value' => \$assignment->assigned_at->format('d/m/Y H:i')],
                    ['value' => \$assignment->unassigned_at?->format('d/m/Y H:i') ?? '—'],
                    ['value' => \$assignment->is_active ? \"<x-ui.badge variant='active'>Activo</x-ui.badge>\" : \"<x-ui.badge variant='inactive'>Inactivo</x-ui.badge>\", 'align' => 'center'],
                    ['value' => \$actions, 'align' => 'center'],
                ];
            })->toArray()"
            emptyMessage="No se encontraron asignaciones."
        />
        <x-ui.pagination
            :currentPage="$assignments->currentPage()"
            :lastPage="$assignments->lastPage()"
            :total="$assignments->total()"
            :from="$assignments->firstItem() ?? 0"
            :to="$assignments->lastItem() ?? 0"
        />
    </div>
@endsection
