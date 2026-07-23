@extends('layouts.authenticated')

@section('title', 'Mis Agentes — Control de Operaciones')

@section('content')
<div class="my-agents">
    <div style="margin-bottom:var(--space-lg);">
        <h2 class="admin-title" style="margin-bottom:var(--space-xs);">Mis Agentes Asignados</h2>
        <p class="admin-subtitle">Agentes bancarios que tienes asignados para registrar operaciones.</p>
    </div>

    @if(session('status'))
        <div class="alert alert-success" role="alert" style="margin-bottom:var(--space-md);">{{ session('status') }}</div>
    @endif

    <div class="card">
        @if($assignments->isEmpty())
            <x-ui.empty-state
                icon="&#x1F3E6;"
                title="Sin agentes asignados"
                description="No tienes agentes bancarios asignados activos. Contacta al administrador para que te asigne un agente."
            />
        @else
            <x-ui.data-table
                :headers="[
                    ['label' => 'Código'],
                    ['label' => 'Terminal'],
                    ['label' => 'Tienda'],
                    ['label' => 'Banco'],
                    ['label' => 'Asignado'],
                ]"
                :rows="$assignments->map(function($assignment) {
                    return [
                        ['value' => $assignment->bankAgent->code, 'class' => 'data-mono'],
                        ['value' => $assignment->bankAgent->terminal_code ?? '—', 'class' => 'data-mono'],
                        ['value' => $assignment->bankAgent->store?->name ?? '—'],
                        ['value' => $assignment->bankAgent->bank?->name ?? '—'],
                        ['value' => $assignment->assigned_at->format('d/m/Y H:i')],
                    ];
                })->toArray()"
            />
            <x-ui.pagination
                :currentPage="$assignments->currentPage()"
                :lastPage="$assignments->lastPage()"
                :total="$assignments->total()"
                :from="$assignments->firstItem() ?? 0"
                :to="$assignments->lastItem() ?? 0"
            />
        @endif
    </div>
</div>
@endsection
