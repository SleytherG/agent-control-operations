@extends('layouts.authenticated')

@section('title', 'Historial de sesiones — Control de Operaciones')

@section('content')
    <h2 class="admin-title" style="margin-bottom:var(--space-xs);">Historial de sesiones</h2>
    <p class="admin-subtitle">Registro de accesos y actividad de usuarios en el sistema.</p>

    <div class="card" style="margin-bottom: var(--space-lg);">
        <form method="GET" action="{{ route('sessions.index') }}" style="display: flex; gap: var(--space-sm); align-items: flex-end; flex-wrap: wrap;">
            <x-ui.select
                label="Estado"
                name="status"
                :options="['ACTIVE' => 'Activa', 'EXPIRED' => 'Expirada', 'REVOKED' => 'Revocada']"
                :selected="request('status')"
                placeholder="Todos"
            />
            <x-ui.input
                label="Desde"
                name="from"
                type="date"
                value="{{ request('from') }}"
            />
            <x-ui.input
                label="Hasta"
                name="to"
                type="date"
                value="{{ request('to') }}"
            />
            <x-ui.button variant="secondary" type="submit">Filtrar</x-ui.button>
        </form>
    </div>

    <div class="card">
        <x-ui.data-table
            :headers="[
                ['label' => 'ID Publico'],
                ['label' => 'Usuario'],
                ['label' => 'Estado', 'align' => 'center'],
                ['label' => 'Inicio'],
                ['label' => 'Fin'],
                ['label' => 'Motivo de fin'],
            ]"
            :rows="$sessions->map(function($session) {
                return [
                    ['value' => \$session->public_id],
                    ['value' => \$session->user->username_normalized ?? 'N/A'],
                    ['value' => \"<x-ui.badge variant='active'>\" . \$session->status->value . \"</x-ui.badge>\", 'align' => 'center'],
                    ['value' => \$session->started_at?->format('Y-m-d H:i:s') ?? '—'],
                    ['value' => \$session->ended_at?->format('Y-m-d H:i:s') ?? '—'],
                    ['value' => \$session->end_reason?->value ?? '—'],
                ];
            })->toArray()"
            emptyMessage="No se encontraron sesiones."
        />
        <x-ui.pagination
            :currentPage="$sessions->currentPage()"
            :lastPage="$sessions->lastPage()"
            :total="$sessions->total()"
            :from="$sessions->firstItem() ?? 0"
            :to="$sessions->lastItem() ?? 0"
        />
    </div>
@endsection
