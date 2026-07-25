@extends('layouts.authenticated')

@section('title', 'Historial de sesiones — Control de Operaciones')

@section('content')
    <h2 class="admin-title" style="margin-bottom:var(--space-xs);">Historial de sesiones</h2>
    <p class="admin-subtitle">Registro de accesos y actividad de usuarios en el sistema.</p>

    <div class="filter-panel" style="margin-bottom: var(--space-lg);">
        <form method="GET" action="{{ route('sessions.index') }}" class="filter-form">
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
            <div class="filter-form-actions">
                <a href="{{ route('sessions.index') }}" class="btn btn--secondary">Limpiar</a>
                <x-ui.button variant="secondary" type="submit">Filtrar</x-ui.button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-responsive"><table class="data-table">
            <thead><tr><th>ID público</th><th>Usuario</th><th class="table-th-center">Estado</th><th>Inicio</th><th>Fin</th><th>Motivo de fin</th></tr></thead>
            <tbody>@forelse($sessions as $session)<tr>
                <td class="data-mono">{{ $session->public_id }}</td><td>{{ $session->user->username_normalized ?? 'N/A' }}</td>
                <td class="table-td-center"><x-ui.badge :variant="match($session->status->value) { 'ACTIVE' => 'active', 'EXPIRED' => 'inactive', default => 'annulled' }">{{ match($session->status->value) { 'ACTIVE' => 'Activa', 'EXPIRED' => 'Expirada', 'REVOKED' => 'Revocada', default => $session->status->value } }}</x-ui.badge></td>
                <td>{{ $session->started_at?->format('Y-m-d H:i:s') ?? '—' }}</td><td>{{ $session->ended_at?->format('Y-m-d H:i:s') ?? '—' }}</td><td>{{ $session->end_reason?->value ?? '—' }}</td>
            </tr>@empty<tr><td colspan="6" class="table-empty"><div class="table-empty-icon" aria-hidden="true">&#x1F511;</div>No se encontraron sesiones.</td></tr>@endforelse</tbody>
        </table></div>
        <x-ui.pagination
            :currentPage="$sessions->currentPage()"
            :lastPage="$sessions->lastPage()"
            :total="$sessions->total()"
            :from="$sessions->firstItem() ?? 0"
            :to="$sessions->lastItem() ?? 0"
        />
    </div>
@endsection
